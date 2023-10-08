<?php
/**
 * 阿里健康
 * @file AliController.php
 * @author wanghongying <wanghongying@yuanxinjituan.com>
 * @version 2.0
 * @date 2022-03-02
 */

namespace mobile\controllers;

use yii\web\Controller;
use common\libs\CommonFunc;
use common\models\GuahaoCooInterrogationModel;
use common\models\GuahaoCooListModel;
use common\libs\MiaoCrypt3Des;
use common\libs\Log;
use common\helpers\PatientLogin;

class AliController extends Controller
{
    public $coo_platform = 2;
    public $param = [];
    public $log = [];
    public $sreferArr = [];
    public $domain = '';
    public $ucenterDomain = '';
    public $hospitalUrl = '';
    public $tranUrl = [
        'doctor_info' => 'hospital/doctor',
        'order_detail' => 'hospital/register/register-detail.html?',
        'login_url' => 'uc/login?',
    ];

    public function init()
    {
        parent::init();
        $this->domain = \Yii::$app->params['domains']['mobile'];
        $this->ucenterDomain = \Yii::$app->params['domains']['ucenter'];
        $this->hospitalUrl = \Yii::$app->params['hospitalUrl'];
        $this->log['log_start_time'] = microtime(true);

        $data = [];
        if (\Yii::$app->request->isPost) {
            $data = \Yii::$app->request->post();
        }
        if (\Yii::$app->request->isGet) {
            $data = \Yii::$app->request->get();
        }

        $token = isset($data['token']) ? $data['token'] : '';
        $this->sreferArr = [
            'srefer' => 'kepudl',
            'skey' => 'alipay'
        ];
        $this->log['token'] = $token;
        //跳转到指定的登录;
        if (empty($token)) {
            $this->jumpLoginUrl($this->sreferArr, "token值为空");
        }
        //判断来源是否可用
        $existCooPlatform = GuahaoCooListModel::getCooNameByCooPlatform($this->coo_platform);
        if (!empty($existCooPlatform)) {
            $token = urldecode($token);
            $miao3Des = new MiaoCrypt3Des();
            $query = $miao3Des->decrypt($token);
            if(empty($query)){
                $this->param = [];
            }
            $arr = [];
            parse_str($query, $arr);
            if (empty($arr)) {
                //跳转到指定的登录;
                $this->jumpLoginUrl($this->sreferArr, "token值3DES解密失败");
            }
            $this->param = $arr;
        } else {
            //跳转到指定的登录;
            $this->jumpLoginUrl($this->sreferArr, "没有可用的来源");
        }
        $this->sreferArr['skey'] = isset($this->param['app_channel']) ? $this->param['app_channel'] : 'alipay';
    }

    /**
     * 医生信息页面
     * @author wanghongying
     * @date 2022/03/02
     * param token
     * ext_user_id String Y 登录⽤户ID（⽀付宝ID）
     * ext_user_name String Y 登录⽤户姓名
     * ext_user_card_no String Y 登录⽤户证件号
     * ext_user_card_type String Y 登录⽤户证件类型
     * ext_user_mobile String Y 登录⽤户⼿机号
     * patient_id String Y 就诊⼈
     * patient_name String Y 就诊⼈姓名⼈姓名ID
     * patient_card_no String Y 就诊⼈证件号
     * patient_card_type String Y 就诊⼈证件类型
     * patient_mobile String Y 就诊⼈⼿机号
     * patient_birthdate String Y 就诊⼈⽣⽇
     * patient_gender String Y 就诊⼈性别
     * ext_Info String N 扩展信息
     * doctor_no String Y 医⽣
     * appChannel String Y 来源taobao/alipay/gaode
     * version String Y 1.0
     * ext_user_id 为alipayId(⽀付宝 ID) 2.0
     * ext_user_id为healthId(健康ID)
     * timestamp String N 时间戳
     * target String Y 跳转⽬标地址(与服务上沟通URL格 式，⼩程序等)
     * return html
     */
    public function actionDoctorInfo()
    {
        try {
            //验证ext_user_id 和 appChannel 不能为空
            if (empty($this->param['ext_user_id']) || empty($this->param['app_channel']) || empty($this->param['target'])) {
                $this->jumpLoginUrl($this->sreferArr, "第三方用户ID(ext_user_id)或来源(app_channel)或跳转目标地址(target)为空");
            }
            $doctorUrl = $this->tranUrl['doctor_info'];
            $param = $this->param;

            $targetItem = CommonFunc::convertUrlQuery($this->param['target']);

            if (empty($targetItem['doctorId'])) {
                $this->jumpLoginUrl($this->sreferArr, "跳转目标地址(target)中的医生ID为空");
            }

            //1自动登录成功之后获取登录用户id
            $userRes = [
                'unique_id' => $param['ext_user_id'],
                'channel' => $param['app_channel'],
                'mobile' => $param['mobile'],
            ];
            $userResult = CommonFunc::registerThirdUser($userRes);
            if (empty($userResult)) {
                //跳转到指定的登录;
                $this->jumpLoginUrl($this->sreferArr, "请求第三方登录接口(/v3/member/register-third-member)失败");
            }
            $param['user_id'] = $user_id = $userResult['userInfo']['uid'];
            $param['coo_user_id'] = $param['ext_user_id'];

            //2 就诊人是否存在,存在更改. 不存在新增就诊人
            GuahaoCooInterrogationModel::formatAliInterrogation($param);

            //3跳转医生挂号页面携带srefer和skey 记录cookie中
            $expire = time() + 86400;//设置患者cookie有效期
            $sres = [
                'srefer' => 'kepudl',
                'skey' => $param['app_channel'],  //来源taobao/alipay/gaode
                'login_key_expire' => $userResult['loginkey'] . "_" . $expire,
            ];
            $urlParam = http_build_query($sres);
            $doctorUrl .= "_{$targetItem['doctorId']}.html?";
            $jumpUrl = $this->domain . $doctorUrl . $urlParam;
            $this->log['index'] = $targetItem['doctorId'];
            $this->addLogFile("阿里健康跳转医生({$targetItem['doctorId']})主页成功", 200);
            //跳转之前清除之前已有的cookie
            PatientLogin::removeLoginCookie();
            header("Location:" . $jumpUrl);
        } catch (\Exception $e) {
            $this->jumpLoginUrl($this->sreferArr, $e->getMessage());
        }
    }


    /**
     * 订单详情信息页面
     * @author wanghongying
     * @date 2022/03/02
     * param token
     * ext_user_id 是 string ⽤户ID
     * mobile 否 string 就诊⼈⼿机号码
     * order_id 是 string isv系统订单id； 对应挂号数据回传接⼝的registrationCode字段
     * app_channel 是 String 来源 taobao/alipay/gaode
     * version 是 String 1.0 ext_user_id 为alipayId(⽀付宝ID) 2.0 ext_user_id为healthId(健康ID)
     * ext_user_mobile 是 String 当前登录⽤户⼿机号
     * ext_user_name 是 String 当前登录⽤户名称
     * ext_user_idcard 是 String 当前登录⽤户身份证号
     * patient_name 否 String 就诊⼈姓名
     * patient_idcard 否 String 就诊身份证号
     * return html
     */
    public function actionOrderDetail()
    {
        try {
            //验证ext_user_id 和 appChannel 不能为空
            if (empty($this->param['ext_user_id']) || empty($this->param['app_channel']) || empty($this->param['order_id'])) {
                $this->jumpLoginUrl($this->sreferArr, "第三方用户ID(ext_user_id)或来源(app_channel)或订单ID(order_id)为空");
            }
            $detailUrl = $this->tranUrl['order_detail'];
            $param = $this->param;

            //1自动登录成功之后获取登录用户id
            $userRes = [
                'unique_id' => $param['ext_user_id'],
                'channel' => $param['app_channel'],
                'mobile' => $param['ext_user_mobile'],
            ];
            $userResult = CommonFunc::registerThirdUser($userRes);
            if (empty($userResult)) {
                //跳转到指定的登录;
                $this->jumpLoginUrl($this->sreferArr, "请求第三方登录接口(/v3/member/register-third-member)失败");
            }

            $param['user_id'] = $user_id = $userResult['userInfo']['uid'];
            //设置患者cookie有效期
            $expire = time()+86400;
            //3跳转医生挂号页面携带srefer和skey 记录cookie中
            $sres = [
                'id' => $param['order_id'],
                'srefer' => 'kepudl',
                'skey' => $param['app_channel'],  //来源taobao/alipay/gaode
                'login_key_expire' => $userResult['loginkey'] . "_" . $expire,
            ];
            $urlParam = http_build_query($sres);
            $jumpUrl = $this->domain . $detailUrl . $urlParam;
            $this->log['index'] = $param['order_id'];
            $this->addLogFile("阿里健康跳转订单({$param['order_id']})详情页成功", 200);
            //跳转之前清除之前已有的cookie
            PatientLogin::removeLoginCookie();
            header("Location:" . $jumpUrl);
        } catch (\Exception $e) {
            $this->jumpLoginUrl($this->sreferArr, $e->getMessage());
        }
    }

    /**
     *  跳转到登录页
     * @return
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-03-04
     */
    public function jumpLoginUrl($param=[], $logMsg = '')
    {
        $this->addLogFile($logMsg, 400);
        $jumpUrl = $this->ucenterDomain . $this->tranUrl['login_url'] . "goBack=" . $this->hospitalUrl . "?";

        $urlStr = "";
        if (!empty($param)) {
            $urlStr = urlencode(http_build_query($param));
        }
        $jumpUrl .= $urlStr;
        header("Location:" . $jumpUrl);
        exit;
    }

    /**
     *  添加日志文件
     * @return
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-03-04
     */
    public function addLogFile($logMsg = '', $code = 400)
    {
        $this->log['log_end_time'] = microtime(true);
        $log_spend_time = round($this->log['log_end_time'] - $this->log['log_start_time'], 2);
        //记录日志
        $this->log["URL"] = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $this->log["prefix"] = "AliController";
        $this->log["action"] = \Yii::$app->request->getPathInfo();
        //$this->log["post"] = json_encode($this->param, JSON_UNESCAPED_UNICODE);
        $this->log["post"] = $this->param;
        $this->log["logMsg"] = "【{$logMsg}】";
        $this->log["IP"] = CommonFunc::getIp();
        $this->log['platform'] = 202; //阿里健康
        $this->log['request_type'] = \Yii::$app->request->getPathInfo();
        $this->log['cur_log']['log_code'] = $code;
        $this->log['cur_log']['log_spend_time'] = $log_spend_time;
        if ($code == 400) {
            $this->log['index'] = 'error';
        }
        //异步记录日志表
        Log::pushLogDataToQueues($this->log);
    }
}