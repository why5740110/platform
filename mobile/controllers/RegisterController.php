<?php
/**
 * 预约挂号
 * @file RegisterController.php
 * @author lixiaolong <lixiaolong@yuanxin-inc.com>
 * @version 1.0
 * @date 2020-10-10
 */
namespace mobile\controllers;

use common\helpers\Url;
use common\models\DoctorModel;
use common\models\GuahaoScheduleModel;
use common\models\GuahaoCooInterrogationModel;
use common\libs\HashUrl;
use common\models\GuahaoOrderModel;
use common\sdks\HapiSdk;
use common\sdks\ServiceSdk;
use common\sdks\shence\SensorsSdk;
use common\sdks\snisiya\SnisiyaSdk;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
use common\sdks\ucenter\PihsSDK;
use common\sdks\ucenter\DoctorServerSDK;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use Yii;
use yii\db\Exception;
use common\libs\CommonFunc;

class RegisterController extends CommonController
{
    //1：H5，2：APP，3：小程序，4：PC
    public $device_source = ['wap'=>1,'patient'=>2,'mini'=>3];

    public function init()
    {
        parent::init();

        $user_id = $this->user_id;
        //没有登录去登陆
        if(empty($user_id))
        {
            $source_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            if($source_url)
            {
                $url = \Yii::$app->params['domains']['ucenter']."uc/login?goBack=".$_SERVER['HTTP_REFERER'];
            }else{
                $url = \Yii::$app->params['domains']['ucenter']."uc/login?goBack=".rtrim(\Yii::$app->params['domains']['mobile'], '/').'/hospital.html';
            }

            header("location:$url");
            exit;
        }
    }

    /**
     *Notes:医生个人主页点击挂号，选择就诊人
     *User:lixiaolong
     *Date:2020/10/10
     *Time:14:05
     * @return string
     */
    public function actionChoosePatient()
    {
        $this->seoTitle = "网上预约挂号_在线咨询医生_就医挂号服务平台-王氏医生";
        $this->seoKeywords = "网上挂号,挂号网,预约挂号,在线医生咨询,网上预约挂号,网上挂号平台";
        $this->seoDescription = "王氏医生公立网上预约挂号平台、提供网上挂号、预约挂号、在线咨询、电话咨询、等就医服务。多家公立医院入驻，真实经历病人点评医院、医生助您找到最适合的就医方案快速挂号。";
        $patient_id = \Yii::$app->request->get('patient_id','');//就诊人id
        $doctor_id = \Yii::$app->request->get('doctor_id');
        $scheduling_id = \Yii::$app->request->get('scheduling_id');//排班id
        $repeat_visit = \Yii::$app->request->get('repeat_visit','');
        $tp_section_id = \Yii::$app->request->get('tp_section_id','');//时间段id
        $tp_platform = \Yii::$app->request->get('tp_platform','');//平台类型
        $cookies = \Yii::$app->request->cookies;
        $user_id = $this->user_id;
        //没有登录去登陆
        if(empty($user_id))
        {
            $url = \Yii::$app->params['domains']['ucenter']."uc/login?goBack=http://".$_SERVER['SERVER_NAME'].''.Url::to(["/hospital/doctor_$doctor_id.html"]);
            header("location:$url");
            exit;
        }
        if(empty($doctor_id) || empty($scheduling_id))
        {
            throw new NotFoundHttpException();
        }

        $data['jzr_choose_doctor_id'] = $doctor_id;
        /*$session = \Yii::$app->session;
        $data['jzr_choose_doctor_id'] = $session['jzr_choose_doctor_id'] = $doctor_id;
        $session['jzr_choose_section_id'] = $tp_section_id;
        if(empty($tp_platform) and !empty($session['jzr_choose_tp_platform']))
        {
            $tp_platform = $session['jzr_choose_tp_platform'];
        }else{
             $session['jzr_choose_tp_platform'] = $tp_platform;
        }*/

        $doctor_id = HashUrl::getIdDecode($doctor_id);

        $doctor_info = DoctorModel::getInfo($doctor_id);
        if(!$doctor_info)
        {
            throw new NotFoundHttpException();
        }

        if (empty($tp_platform) and !empty($doctor_info)) {
            $tp_platform = $doctor_info['tp_platform'];
        }

        //$session['jzr_choose_scheduling_id'] = $scheduling_id;
        //获取问诊人列表信息
        $patient_list = $this->actionGetPatientList();
        $patient_info = [];
        if(empty($patient_id))
        {
            //获取最近添加的问诊人信息
            $patient_info = PihsSDK::getInstance()->actionRecentPatient(['user_id'=>$user_id]);
            //判断最近添加的就诊人是否实名认证,如果没有实名认证则unset
            if(!empty($patient_info))
            {
                if(isset($patient_info['is_auth_card']) and $patient_info['is_auth_card'] == 0)
                {
                    $patient_info = [];
                }

            }
            if(!empty($patient_list[0]) and empty($patient_info))
            {
                //如果最近没有新添的就诊人，那么获取最新实名认证过的就诊人
                foreach ($patient_list as $pk=>$pv){
                    if($pv['is_auth_card'] == 1){
                        $patient_info = $pv;
                        continue;
                    }
                }

            }
        }else{
            //获取问诊人详细信息
            $patient_info = $this->actionGetPatientInfo($patient_id);
            if(empty($patient_info))
            {
                echo "<script>alert('问诊人信息有误，请重试！');window.location.href='/hospital.html'</script>";
            }
        }
        if(empty($patient_info))
        {
            //获取医生排班信息 如果就诊人列表没有实名过或者就诊人信息没有添加过不传递就诊信息获取排班
            $guahao_paiban = SnisiyaSdk::getInstance()->guahao_paiban_info([
                'doctor_id'=>$doctor_id,
                'tp_scheduling_id' => $scheduling_id,
                'tp_section_id'=>$tp_section_id,
                'tp_platform'=>$tp_platform
            ]);
        }else{
            //获取医生排班信息 如果就诊人列表实名过或者就诊人信息添加过 传递就诊人信息获取排班
            $guahao_paiban = SnisiyaSdk::getInstance()->guahao_paiban_info([
                'doctor_id'=>$doctor_id,
                'tp_scheduling_id' => $scheduling_id,
                'tp_section_id'=>$tp_section_id,
                'patient_id'=>$patient_info['id'],
                'patient_name'=>$patient_info['realname'],
                'card'=>$patient_info['id_card'],
                'birth_time'=>$patient_info['birth_time'],
                'tp_platform'=>$tp_platform,
                'gender'=>$patient_info['sex'],
                'age'=>$patient_info['age'],
                'mobile'=>$patient_info['tel'],
                'province'=> $patient_info['province'],
                'city' => $patient_info['city'],
            ]);
        }
        if(empty($guahao_paiban['schedule_info']))
        {
            //throw new NotFoundHttpException();
            //无排班跳转个人主页
            $goUrl = rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['doctor/home', 'doctor_id' => $doctor_id]);
            return $this->redirect($goUrl);
        }
        $data['doctor_info'] = $doctor_info;
        $data['scheduling_id'] = $scheduling_id;
        $data['patient_info'] = !empty($patient_info)?$patient_info:[];
        $data['patient_list'] = $patient_list;
        $data['patient_count'] = count($patient_list);
        $data['repeat_visit'] = $repeat_visit;
        $data['tp_section_id'] = $tp_section_id;
        $data['tp_guahao_verify'] = $guahao_paiban['tp_guahao_verify'];
        $data['tp_guahao_description'] = $guahao_paiban['tp_guahao_description'];

        $data['guahao_paiban'] = $guahao_paiban['schedule_info'];
        $data['sections'] = $guahao_paiban['sections'];
        $data['md5_useId'] = md5($this->user_id);
        $data['tp_platform'] = $tp_platform;
        $data['doctor_id'] = $doctor_id;

        //埋点数据处理
        $eventParam = [
            'page_title' => '预约页面',
            'page' => '预约页面',
            'hospital_id' => ArrayHelper::getValue($doctor_info,'hospital_id'),
            'hospital_name' => ArrayHelper::getValue($doctor_info,'hospital_name'),
            'doctor_id' => $doctor_id,
            'doctor_name' => ArrayHelper::getValue($doctor_info,'doctor_realname'),
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);
        return $this->render('choose_patient',$data);
    }

    /**
     *Notes:添加就诊人
     *User:lixiaolong
     *Date:2020/10/12
     *Time:9:23
     */
    public function actionRegisterInfoAdd()
    {
        $request = Yii::$app->request;
        $this->seoTitle = "网上预约挂号_在线咨询医生_就医挂号服务平台-王氏医生";
        $this->seoKeywords = "网上挂号,挂号网,预约挂号,在线医生咨询,网上预约挂号,网上挂号平台";
        $this->seoDescription = "王氏医生公立网上预约挂号平台、提供网上挂号、预约挂号、在线咨询、电话咨询、等就医服务。多家公立医院入驻，真实经历病人点评医院、医生助您找到最适合的就医方案快速挂号。";
        $request = Yii::$app->request;
        $id = $request->get('id');
        //获取地区
        $region = $this->getRegionData();
        //unset($region['city_list'],$region['province'],$region['city']);
        $data['region'] = json_encode(array_values($region['province_list']));
        $data['md5_useId'] = md5($this->user_id);
        $data['jzr_choose_doctor_id'] = $request->get('jzr_choose_doctor_id');
        $data['jzr_choose_scheduling_id'] = $request->get('jzr_choose_scheduling_id');
        $data['jzr_choose_section_id'] = $request->get('jzr_choose_section_id');
        $data['jzr_choose_tp_platform'] = $request->get('jzr_choose_tp_platform');

        //埋点数据处理
        $eventParam = [
            'page_title' => (empty($id)) ? '添加问诊人' : '编辑问诊人',
            'page' => (empty($id)) ? '添加问诊人' : '编辑问诊人',
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);

        if(empty($id))
        {
            return $this->render('register_add',$data);
        }else{
            $data['info_data'] = $this->actionGetPatientInfo($id);
            return $this->render('register_add',$data);
        }
    }

    /**
     *Notes:确认预约页面
     *User:lixiaolong
     *Date:2020/10/13
     *Time:14:19
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionRegisterConfirm()
    {
        $this->seoTitle = "网上预约挂号_在线咨询医生_就医挂号服务平台-王氏医生";
        $this->seoKeywords = "网上挂号,挂号网,预约挂号,在线医生咨询,网上预约挂号,网上挂号平台";
        $this->seoDescription = "王氏医生公立网上预约挂号平台、提供网上挂号、预约挂号、在线咨询、电话咨询、等就医服务。多家公立医院入驻，真实经历病人点评医院、医生助您找到最适合的就医方案快速挂号。";
        $session = \Yii::$app->session;
        $id = \Yii::$app->request->get('id');
        $type = \Yii::$app->request->get('type','confirm');
        $data['tp_order_id'] = \Yii::$app->request->get('tp_order_id','');
        $session['visit'] = $data['visit'] = \Yii::$app->request->get('visit','1');//初诊复诊 1：初诊 2：复诊
        $data['doctor_id'] = $session['jzr_choose_doctor_id'];
        $data['scheduling_id'] = $session['jzr_choose_scheduling_id'];
        //获取医生排班详细信息
        $guahao_paiban = SnisiyaSdk::getInstance()->guahao_paiban_info(['doctor_id'=>$data['doctor_id'],'tp_scheduling_id'=>$data['scheduling_id']]);
        if(empty($guahao_paiban['schedule_info']))
        {
            throw new NotFoundHttpException();
        }
        $data['guahao_paiban'] = $guahao_paiban['list'][0];
        $data['allowed_cancel_time'] = $guahao_paiban['allowed_cancel_time'];
        $doctor_id = HashUrl::getIdDecode($data['doctor_id']);

        $doctor_info = DoctorModel::getInfo($doctor_id);
        if(!$doctor_info)
        {
            throw new NotFoundHttpException();
        }
        $data['doctor_info'] = $doctor_info;
        $data['patient_info'] = $this->actionGetPatientInfo($id);
        if(empty($data['patient_info']))
        {
            throw new NotFoundHttpException();
        }
        if ($type =='faild'){
            $data['type'] = 'faild';
            return $this->render('register_confirm',$data);
        }else{
            $data['type'] = 'confirm';
            return $this->render('register_confirm',$data);
        }

    }

    /**
     *Notes:查看详情
     *User:lixiaolong
     *Date:2020/10/13
     *Time:17:41
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionRegisterDetail()
    {
        $this->seoTitle = "网上预约挂号_在线咨询医生_就医挂号服务平台-王氏医生";
        $this->seoKeywords = "网上挂号,挂号网,预约挂号,在线医生咨询,网上预约挂号,网上挂号平台";
        $this->seoDescription = "王氏医生公立网上预约挂号平台、提供网上挂号、预约挂号、在线咨询、电话咨询、等就医服务。多家公立医院入驻，真实经历病人点评医院、医生助您找到最适合的就医方案快速挂号。";
        $ua = $this->getUserAgent();
        $id = \Yii::$app->request->get('id');//订单id
        $source_from = \Yii::$app->request->get('source_from','hospital');
        if(!$id)
        {
            throw new NotFoundHttpException();
        }

        //获取订单信息
        $data = HapiSdk::getInstance()->getOrderDetail(['id'=>$id]);
        if(empty($data))
        {
            throw new NotFoundHttpException();
        }
        if ($data['state'] == 6) {
            throw new NotFoundHttpException();
        }
        //不是本人订单不展示
        if (ArrayHelper::getValue($data, 'uid') != $this->user_id) {
            throw new NotFoundHttpException();
        }
        $data['ua'] = $ua;
        $data['cancel_status'] = (!empty($data['allowed_cancel']) and $data['allowed_cancel'] == 0)?2:1;//取消挂号 2:不能取消 1：可以取消
        $data['pay_url'] = $pay_url = '';
        $data['source_from'] = $source_from;

        $data['times'] = ($data['create_time'] + CommonFunc::PAY_EXP_TIME) - time();

        if($data['state'] == 5 and $data['pay_status'] == 1){
            if($data['times']<0){
                $data['state'] = 1;
            }
        }


        //验证医生是否存在或者禁用
        $infos = DoctorModel::getInfo($data['doctor_id']);
        $data['is_disable'] = 0;//医生存在或禁用  0 不存在或禁用  1 存在
        if (!empty($infos) && $infos['status'] == 1) $data['is_disable'] = 1;

        //埋点数据处理
        $eventParam = [
            'page_title' => '预约订单详情',
            'page' => '预约订单详情',
            'hospital_name' => $data['hospital_name'],
            'doctor_id' => $data['doctor_id'],
            'doctor_name' => $data['doctor_name'],
            'register_id' => $id
        ];
        \common\widgets\MiaoStatisticsWidget::widget([
            'register_event' => $this->formatEvent($eventParam),
        ]);

        if($data['state'] != 1)//成功
        {
            if($data['state'] ==  5 and $data['pay_status'] == 1)
            {
                //待支付获取支付url
                $times = 5;
                do {
                    if($times < 1)
                    {
                        $pay_url = '';
                    }
                    $pay_info = CommonFunc::guahaoGoPay($id);
                    if($pay_info['code'] == 1)
                    {
                        $pay_url = $pay_info['data'];
                    }
                } while ($times--);

            }
            $data['pay_url'] = $pay_url;
            $this->seoTitle = "挂号成功-{$data['hospital_name']}-王氏医生";
            if($source_from == 'hospital')
            {
                return $this->render('register_confirm_success',$data);
            }
            if ($source_from == 'mycenter')
            {
                return $this->render('register_confirm_success_mycenter',$data);
            }
        }else if($data['state'] == 1)//已取消
        {
            return $this->render('register_confirm_faild',$data);
        }
        /*else{
            $url = "http://".$_SERVER['SERVER_NAME'].''.Url::to(["/hospital.html"]);
            header("location:$url");
            exit;
        }*/

    }

    /**
     *Notes:已取消挂号页面
     *User:lixiaolong
     *Date:2020/10/13
     *Time:20:06
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionRegisterConfirmCancel()
    {
        $id = \Yii::$app->request->get('id');//订单主键id
        if(!$id)
        {
            throw new NotFoundHttpException();
        }
        //获取订单信息
        $data = HapiSdk::getInstance()->getOrderDetail(['id'=>$id]);

        return $this->render('register_confirm_faild',$data);

    }

    /**
     *Notes:提交确认预约
     *User:lixiaolong
     *Date:2020/10/13
     *Time:14:20
     */
    public function actionSubmitRegisterConfirm()
    {
        //$session = \Yii::$app->session;
        $id = \Yii::$app->request->get('id');//问诊人id
        $data['famark_type'] = \Yii::$app->request->get('visit');//初 复诊 1：初诊 2：复诊
        $data['tp_password'] = \Yii::$app->request->get('tp_password','');//密码
        $data['tp_section_id'] = \Yii::$app->request->get('tp_section_id','');//挂号时间段id
        $cookies = \Yii::$app->request->cookies;
        $data['uid'] = $cookies->getValue('user_id');
        $data['doctor_id'] = \Yii::$app->request->get('jzr_choose_doctor_id','');//挂号医生
        $data['tp_scheduling_id'] = \Yii::$app->request->get('jzr_choose_scheduling_id','');//排班id
        $data['symptom'] = \Yii::$app->request->get('register_bz');//预约提交的疾病症状备注
        $data['tp_doctor_id'] = \Yii::$app->request->get('tp_doctor_id');
        $data['tp_platform'] = \Yii::$app->request->get('tp_platform');//平台来源

        //获取问诊人详细信息
        $patient_info = $this->actionGetPatientInfo($id);
        if(empty($patient_info))
        {
            $msg_data = ['code'=>400,'msg'=>'问诊人信息有误！'];
            return json_encode($msg_data);
        }else{
            $data['patient_id'] = $patient_info['id'];
            $data['patient_name'] = $patient_info['realname'];
            $data['gender'] = $patient_info['sex'];
            $data['age'] = $patient_info['age'];
            $data['card'] = $patient_info['id_card'];
            $data['mobile'] = $patient_info['tel'];
            $data['birth_time'] = $patient_info['birth_time'];
            //$data['province'] = $patient_info['province'];
            $data['province'] = '';
            //$data['city'] = $patient_info['city'];
            $data['city'] = '';
            //$data['tp_platform'] = $session['jzr_choose_tp_platform'];

            //判断就诊人年龄 如果小于5岁， 就需要监护人， 如果监护人（账号者信息也是小于5岁， 就提示填写监护人）
            if(intval($data['tp_platform']) == 10 ){
                $guardian = $this->actionGetAccountDetail($data['uid']);
                $data['user_realname'] = $guardian['realname'];
                $data['user_id_card'] = $guardian['id_card'];
                $data['user_tel'] = $guardian['tel'];
                // 暂且保留
//                $patientAge =  $this->actionGetCardAge($data['card']);
//                $userAge    =  $this->actionGetCardAge($guardian['id_card']);
//                if((intval($patientAge)<6 && intval($userAge)<6)){
//                    $msg_data = ['code'=>400,'msg'=>'请填写监护人信息！'];
//                    return json_encode($msg_data);
//                }
            }
            //$extended = GuahaoScheduleModel::getExtended($data['tp_doctor_id'],$session['jzr_choose_tp_platform'],$data['tp_scheduling_id']);
            $extended = GuahaoScheduleModel::getExtended($data['tp_doctor_id'],$data['tp_platform'],$data['tp_scheduling_id']);
            $data['extended'] = $extended;

            $guardian = $this->actionGetAccountDetail($data['uid']);
            $data['user_realname'] = $guardian['realname'];
            $data['user_id_card'] = $guardian['id_card'];
            $data['user_tel'] = $guardian['tel'];

            //平台来源
            $ua = $this->getUserAgent();
            $data['device_source'] = ArrayHelper::getValue($this->device_source,$ua,'1');
            $data['ip'] = CommonFunc::getRealIpAddressForNginx();

            //添加refer
            $refer = CommonFunc::getReferCookie();
            $data['srefer'] = ArrayHelper::getValue($refer, 'srefer', '');
            $data['skey'] = ArrayHelper::getValue($refer, 'skey', '');
            if (empty($data['srefer']) || empty($data['skey'])) {//如果有一个为空 则两个都值为空
                $data['srefer'] = '';
                $data['skey'] = '';
            }
            $data['coo_platform'] = '';
            $data['coo_patient_id'] = '';
            //检测是否从第三方平台过来数据 ， 阿里健康['taobao', 'alipay', 'gaode', 'aliyy', 'uc']，科大讯飞['kedaxunfei']如其他平台继续添加到这个数组中
            $skeyPlatform = ['taobao' => 2, 'alipay' => 2, 'gaode' => 2, 'aliyy' => 2, 'uc' => 2, 'kedaxunfei' => 3];
            if (!empty($data['srefer']) && !empty($data['skey']) && in_array($data['skey'], array_keys($skeyPlatform))) {
                //检测用户id和就诊人id在tb_guahao_coo_interrogation表中是否有数据
                $data['coo_platform'] = isset($skeyPlatform[$data['skey']]) ? $skeyPlatform[$data['skey']] : '';
                $cooInterrogation = GuahaoCooInterrogationModel::getInfoByUserIdParentId($data);
                if (!empty($cooInterrogation)) {
                    $data['coo_platform'] = isset($cooInterrogation['coo_platform']) ? $cooInterrogation['coo_platform'] : "";
                    $data['coo_patient_id'] = isset($cooInterrogation['coo_patient_id']) ? $cooInterrogation['coo_patient_id'] : "";
                }
            }

            $result = SnisiyaSdk::getInstance()->guahaoConfirm($data);
            //$result = ['tp_order_id'=>1];
            if(!empty($result))
            {
                if($result['code'] == 200)
                {
                    //预约成功 发短信
                    $order_sn = ArrayHelper::getValue($result,'data.id');
                    CommonFunc::guahaoSendSms('guahao_success',$order_sn);
                    //神策埋点暂停
//                    if ($result['data']['id'] && in_array(\Yii::$app->controller->getUserAgent(),['patient'])) {
//                        $this->shenceData($data, $patient_info, $result['data']['id']);
//                    }

                    $msg_data = ['code'=>200,'msg'=>'预约挂号成功！','data'=>$result['data']];
                    return json_encode($msg_data);
                }else{
                    $msg = !empty($result['msg']) ? $result['msg'] : "预约挂号失败，请稍后再试！";
                    $msg_data = ['code'=>400,'msg'=>$msg];
                    return json_encode($msg_data);
                }

            }else{
                $msg_data = ['code'=>400,'msg'=>'预约挂号失败，请稍后再试！'];
                return json_encode($msg_data);
            }
        }
    }

    /**
     * 神策数据埋点
     * @param $data
     * @param $patient_info
     * @param $register_id
     * @return bool
     * @throws \Exception
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-05-18
     */
    public function shenceData($data, $patient_info, $register_id){
        try {
            $shence_data = HapiSdk::getInstance()->getOrderDetail(['id'=>$register_id]);
            $visit_type = $shence_data['visit_type'] ?? '0';
            $age = 0;
            if ($patient_info['age']){
                if(preg_match('/\d+/',$patient_info['age'],$arr)){
                    $age = (int)$arr[0];
                }
            }
            $superParams = [
                'platform_type' => 'App',
                'source_channel' => 'ghapp',
                'operating_system' => 'Android/IOS',
                'application_version' => ''
            ];
            $shence_data = [
                'current_page' => 'msapp_register_confirm',
                'current_page_name' => '挂号确认页',
                'register_id' => $register_id,
                'name' => $patient_info['realname'] ?? '',
                'age' => $age,
                'phone_number' => $patient_info['tel'] ?? '',
                'sex' => $patient_info['sex'] ?? '',
                'time_interval' => $shence_data['visit_time'] ?? '',
                'see_a_doctor_time' => $shence_data['visit_starttime'] ?? '',
                'diagnosis' => $data['famark_type'] == 1 ? '初诊' : '复诊',
                'hospital_name' => $shence_data['hospital_name'] ?? '',
                'department_name' => $shence_data['department_name'] ?? '',
                'doctor_name' => $shence_data['doctor_name'] ?? '',
                'visit_type' => CommonFunc::$visit_type[$visit_type] ?? '',
                'symptom' => $shence_data['symptom'] ?? '',
                'amount' => $shence_data['visit_cost']/100,
            ];
            $shence = new SensorsSdk();
            $shence->trackFile('ReserveEnterClick',$shence_data, $this->user_id,$superParams);
            return true;
        } catch (\Exception $e){
            $msg_data = ['code'=>400,'msg'=>'神策数据埋点失败'];
            return json_encode($msg_data);
        }
    }

    /**
     *Notes:取消预约
     *User:lixiaolong
     *Date:2020/10/13
     *Time:15:36
     */
    public function actionRegiterCancel()
    {
        $id = \Yii::$app->request->get('id');//订单id
        $tp_password = \Yii::$app->request->get('tp_password','');//密码
        $cancel_reason = \Yii::$app->request->get('cancel_reason','');//取消原因
        //取消订单
        $result = SnisiyaSdk::getInstance()->guahaoCancel(['id'=>$id,'tp_password'=>$tp_password,'canceldesc'=>$cancel_reason]);
        if(!empty($result) and $result['code'] == 200)
        {
            //取消通知
            CommonFunc::guahaoSendSms('guahao_cancel',$id);

            $msg_data = ['code'=>200,'msg'=>'预约挂号取消成功！'];
            return json_encode($msg_data);

        }else{
            if($result['code'] == 400)
            {
                $msg_data = ['code'=>400,'msg'=>$result['msg']];
                return json_encode($msg_data);
            }else{
                $msg_data = ['code'=>400,'msg'=>'取消失败，请重试！'];
                return json_encode($msg_data);
            }
        }
    }

    /**
     *Notes:ajax修改问诊人
     *User:lixiaolong
     *Date:2020/10/13
     *Time:11:26
     * @return false|string
     * @throws \Exception
     */
    public function actionAjaxPatientInfoUp()
    {
        $request = Yii::$app->request;
        $cookies = \Yii::$app->request->cookies;
        $info['user_id'] = $cookies->getValue('user_id');
        $info['uc_login_key'] = $cookies->getValue('uc_logined', '');
        $postData = $request->post();
        //$info['is_auth_card'] = 0;
        if(!empty($postData['id']))//修改操作
        {
            $info['id'] = $postData['id'];
            /*
            //获取就诊人信息，判断姓名+身份证号是否修改
            $res_data = $this->actionGetPatientInfo($info['id']);
            if($res_data['is_auth_card'] == 0){
                //如果ajax传递的姓名和身份证号修改了，需要重新认证
                $auth_res = SnisiyaSdk::getInstance()->verifyPerson(['name'=>$postData['realname'],'id_card' => $postData['id_card']]);
                if(!empty($auth_res) and $auth_res['code'] == 200)
                {
                    $info['is_auth_card'] = 1;
                }else{
                    $data['msg'] = "身份证号码与提交的真实姓名不一致，请重新输入！";
                    $data['code'] = 400;
                    return json_encode($data);
                }
            }else{
                if($res_data['realname'] != $postData['realname'] || $res_data['id_card'] != $postData['id_card'])
                {
                    //如果ajax传递的姓名和身份证号修改了，需要重新认证
                    $auth_res = SnisiyaSdk::getInstance()->verifyPerson(['name'=>$postData['realname'],'id_card' => $postData['id_card']]);
                    if(!empty($auth_res) and $auth_res['code'] == 200)
                    {
                        $info['is_auth_card'] = 1;
                    }else{
                        $data['msg'] = "身份证号码与提交的真实姓名不一致，请重新输入！";
                        $data['code'] = 400;
                        return json_encode($data);
                    }
                }else{
                    $info['is_auth_card'] = 1;
                }
            }*/

        }
        /*if(empty($postData['id']))//添加
        {
            //实名认证
            $auth_res = SnisiyaSdk::getInstance()->verifyPerson(['name'=>$postData['realname'],'id_card' => $postData['id_card']]);
            if(!empty($auth_res) and $auth_res['code'] == 200)
            {
                $info['is_auth_card'] = 1;
            }else{
                $data['msg'] = "身份证号码与提交的真实姓名不一致，请重新输入！";
                $data['code'] = 400;
                return json_encode($data);
            }
        }*/
        $info['is_real_auth'] = 1;
        $info['realname'] = $postData['realname'];
        $info['id_card'] = $postData['id_card'];
        $info['sex'] = $postData['sex'];
        $info['birth_time'] = $postData['birth_time'];
        $info['tel'] = $postData['mobile'];
        /*$info['province'] = (string)$postData['other_address_province'];
        $info['city'] = (string)$postData['other_address_city'];
        $info['address'] = (string)$postData['address'];*/
        $info['is_filter_auth'] = 0;
        /*$info['guarder_name'] = $postData['guarder_name'] ?? '';
        $info['guarder_card'] = $postData['guarder_card'] ?? '';
        $info['guarder_tel'] = $postData['guarder_tel'] ?? '';*/
        $result = $this->actionRegisterInfoSave($info);
        if($result['code'] == 200)
        {
            //$session = \Yii::$app->session;
            $data['code'] = 200;
            $data['patient_id'] = $result['data']['id'];
            //$data['jzr_choose_doctor_id'] = $session['jzr_choose_doctor_id'];
            $data['jzr_choose_doctor_id'] = $postData['jzr_choose_doctor_id'];
            //$data['jzr_choose_scheduling_id'] = $session['jzr_choose_scheduling_id'];
            $data['jzr_choose_scheduling_id'] = $postData['jzr_choose_scheduling_id'];
            //$data['jzr_choose_section_id'] = $session['jzr_choose_section_id'];
            $data['jzr_choose_section_id'] = $postData['jzr_choose_section_id'];
            $data['jzr_choose_tp_platform'] = $postData['jzr_choose_tp_platform'];
            return json_encode($data);
        }else{
            $data['msg'] = $result['msg'];
            $data['code'] = 400;
            return json_encode($data);
        }
    }
    /**
     *Notes:获取就诊人列表信息
     *User:lixiaolong
     *Date:2020/10/10
     *Time:17:25
     */
    public function actionGetPatientList()
    {
        $cookies = \Yii::$app->request->cookies;
        $user_id = $cookies->getValue('user_id');
        $patientData = PihsSDK::getInstance()->actionPatientList(['user_id' => $user_id]);
        return $patientData;
    }

    /**
     *Notes:获取问诊人详细信息
     *User:lixiaolong
     *Date:2020/10/12
     *Time:14:20
     * @param $id
     */
    public function actionGetPatientInfo($id)
    {
        $cookies = \Yii::$app->request->cookies;
        $user_id = $cookies->getValue('user_id');
        $result = PihsSDK::getInstance()->actionGetPatienInfo(['id'=>$id,'user_id'=>$user_id]);
        /*$uc_logic_key = $cookies->getValue('uc_logined', '');$data = [
            'patient_id' => $id,
            'uc_logic_key' => $uc_logic_key,
        ];
        $result = DoctorServerSDK::getInstance()->getPatientDetail($data);*/
        return $result;
    }

    /**
     *Notes:就诊人信息添加/修改
     *User:lixiaolong
     *Date:2020/10/12
     *Time:11:13
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function actionRegisterInfoSave($data)
    {
        //$patientData = PihsSDK::getInstance()->actionRegisterSave($data);
        $patientData = $result = DoctorServerSDK::getInstance()->savePatient($data);
        return $patientData;
    }
    /**
     * 获取地区信息
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-28
     * @version 1.0
     * @return  [type]     [description]
     */
    public function getRegionData()
    {
        $request       = Yii::$app->request;
        $region        = $request->get('region', 0);
        // $province_list = Department::district();
        $province_list = SnisiyaSdk::getInstance()->getDistrict();
        $city_list     = [];
        $province      = [];
        $city          = [];
        if ($region) {
            // $regioninfo = Department::pinyin2id($region);
            $regioninfo = SnisiyaSdk::getInstance()->getRegionInfo(['region'=>$region]);
            if ($regioninfo && $regioninfo['c_id'] == 0) {
                $province  = $province_list[$regioninfo['p_id']];
                $city_list = $province['city_arr'] ?? [];
            }
            if ($regioninfo && $regioninfo['c_id'] > 0) {
                $province  = $province_list[$regioninfo['p_id']];
                $city_list = $province['city_arr'] ?? [];
                if ($city_list) {
                    foreach ($city_list as $key => $value) {
                        if ($value['id'] == $regioninfo['c_id']) {
                            $city = $value;
                            break;
                        }
                    }
                }
            }
        }

        if ($province_list) {
            //省份下的城市如果没有就把省份信息填充到城市下
            foreach ($province_list as &$val) {
                if (in_array($val['name'], ['香港', '澳门']) && empty($val['city_arr'])) {
                    $val['city_arr'][] = [
                        'name' => $val['name'],
                        'parentid' => $val['id'],
                        'code' => $val['code'],
                        'order' => 1,
                        'parentcode' => $val['code'],
                        'suffix' => $val['suffix'],
                        'pinyin' => $val['pinyin']
                    ];
                }
            }
        }

        return [
            'province_list' => $province_list,
            'city_list'     => $city_list,
            'province'      => $province,
            'city'          => $city,
        ];

    }

    /**
     *Notes:订单支付成功，展示支付成功信息
     *User:lixiaolong
     *Date:2020/12/3
     *Time:9:53
     */
    public function actionShowOrdermsg()
    {
        $this->seoTitle = "支付成功_网上预约挂号_在线咨询医生_就医挂号服务平台-王氏医生";
        $this->seoKeywords = "网上挂号,挂号网,预约挂号,在线医生咨询,网上预约挂号,网上挂号平台";
        $this->seoDescription = "王氏医生公立网上预约挂号平台、提供网上挂号、预约挂号、在线咨询、电话咨询、等就医服务。多家公立医院入驻，真实经历病人点评医院、医生助您找到最适合的就医方案快速挂号。";
        $id = \Yii::$app->request->get('id');//订单id
        if(!$id)
        {
            throw new NotFoundHttpException();
        }

        //获取订单信息
        $data = HapiSdk::getInstance()->getOrderDetail(['id'=>$id]);
        if(empty($data))
        {
            throw new NotFoundHttpException();
        }
        $ua = $this->getUserAgent();
        $data['ua'] = $ua;

        //解析支付回调参数
        $aesData = \Yii::$app->request->get('data');
        $aesData = CommonFunc::validatorBackData($aesData);

        $backRes = CommonFunc::payBack($aesData);
        $msg = '';
        if(ArrayHelper::getValue($backRes,'code')!=1){
            $msg = ArrayHelper::getValue($backRes,'msg');
        }
        $data['msg'] = $msg;
        return $this->render('pay_success',$data);
    }
    /**
     *  获取监护人（账号信息）
     * @param $uid
     * @return array|mixed
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-10-13
     */
    public function actionGetAccountDetail($uid=0)
    {
        $jzrList = $this->actionGetPatientList();
        $detail  = [];
        foreach($jzrList as &$v){
            if($v['user_id'] == $uid){
                $detail = $v;
            }
        }
        return $detail;
    }

    /**
     *  判断身份证号年龄
     * @param $card
     * @return false|int|string
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-10-13
     */
    public function actionGetCardAge($card)
    {
        if(strlen($card)<10){
            return 0;
        }
         return date('Y') - substr($card, 6, 4) + (date('md') >= substr($card, 10, 4) ? 1 : 0);
    }

}
?>