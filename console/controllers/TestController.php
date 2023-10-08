<?php
/**
 *  测试文件
 * @file TestController.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 1.0
 * @date 2020-07-18
 */

namespace console\controllers;

use common\libs\CryptoTools;
use common\models\Department;
use common\models\DoctorEsModel;
use common\models\DoctorModel;
use common\libs\CommonFunc;
use common\models\GuahaoHospitalModel;
use common\models\GuahaoPlatformModel;
use common\models\HospitalDepartmentRelation;
use common\models\TbDepartmentThirdPartyRelationModel;
use common\models\TbDoctorThirdPartyRelationModel;
use common\models\TestModel;
use common\models\BaseDoctorHospitals;
use common\models\TmpDepartmentThirdPartyModel;
use common\models\TmpDoctorThirdPartyModel;
use common\sdks\BaiduGuahaoSdk;
use common\sdks\GuahaoSdk;
use common\sdks\HospitalSdk;
use yii\helpers\ArrayHelper;
use common\sdks\snisiya\SnisiyaSdk;
use Yii;

class TestController extends CommonController
{
    private $baseDir = '/data/upload';

    /**
     * 匹配历史科室关联王氏科室    一
     * @throws \Exception
     * @author xiujianying
     * @date 2021/9/22
     */
    public function actionRunkeshi(){


        $sdk =  HospitalSdk::getInstance();
        $data = $sdk->search(['status'=>0,'keyword'=>'北京协和']);
exit;
        print_r($data);exit;

        $data = BaseDoctorHospitals::getHospitalDetail(32283);
        print_r($data);exit;


        $a= BaseDoctorHospitals::getHospitalSearch(['name'=>'协和','status'=>0],'scalar','province_name');
print_r($a);
        exit;

        /*$data = BaseDoctorHospitals::HospitalDetail(32283,true);
print_r($data);exit;*/


       $data=  CommonFunc::getMiaoKeshi();
        print_r($data);
        exit;


        $sql = "UPDATE `tb_department` SET `is_match` = '1' WHERE parent_id=0 ";
        \Yii::$app->db->createCommand($sql)->execute();

        sleep(1);

        $keshi_total = Department::find()->where(['is_match'=>0])->count();
        echo $keshi_total."\n";
        $pagesize = 100;
        $maxPage = ceil($keshi_total/$pagesize);
        for($i=0;$i<=$maxPage;$i++){
            $offset = $i*$pagesize;
            $data = Department::find()->where(['is_match'=>0])->offset($offset)->limit($pagesize)->asArray()->all();
            if($data) {
                foreach ($data as $v) {
                    $id = $v['department_id'];
                    $relaData = HospitalDepartmentRelation::find()->where(['second_department_id' => $id])->asArray()->one();
                    if ($relaData) {
                        $query = Department::find()->where(['department_id' => $id])->one();
                        $query->miao_first_department_id = ArrayHelper::getValue($relaData, 'miao_frist_department_id');
                        $query->miao_second_department_id = ArrayHelper::getValue($relaData, 'miao_second_department_id');
                        $query->is_match = 1;
                        $query->save();
                        echo $id . '-' . ArrayHelper::getValue($relaData, 'miao_frist_department_id') . '-' . ArrayHelper::getValue($relaData, 'miao_second_department_id') . "\n";
                    } else {
                        echo $id . '无匹配' . "\n";
                    }

                }
            }
        }
        echo 'end';
    }

    /**
     * 检查王氏一级科室是否在 tb_department 表中  无则新增
     * @author xiujianying
     * @date 2021/9/27
     */
    public function actionRunmiao(){
       $keshiData =  CommonFunc::getFkeshiInfos();
       foreach($keshiData as $v){
           $exist = Department::find()->where(['parent_id'=>0,'department_name'=>$v['name']])->count();
           if(!$exist){
               $model = new Department();
               $model->department_name = $v['name'];
               $model->parent_id = 0;
               $model->is_match = 1;
               $model->create_time = time();
               $model->save();

               echo $v['name'].PHP_EOL;

           }
       }
    }



    /**
     * 第三方科室表 新增医院code
     * @throws \yii\db\Exception
     * @author xiujianying
     * @date 2021/9/22
     */
    public function actionRuncode(){
        $total = TbDepartmentThirdPartyRelationModel::find()->count();
        $pagesize = 100;
        $maxPage = ceil($total/$pagesize);
        for($i=0;$i<=$maxPage;$i++){
            $offset = $i*$pagesize;
            $data = TbDepartmentThirdPartyRelationModel::find()->offset($offset)->limit($pagesize)->asArray()->all();
            if($data) {
                foreach ($data as $v) {
                    $dep_id = $v['hospital_department_id'];
                    $tp_platform = $v['tp_platform'];
                    $hospital_id = HospitalDepartmentRelation::find()->where(['id'=>$dep_id])->select(['hospital_id'])->scalar();
                    if($hospital_id) {
                        $tp_hospital_code = GuahaoHospitalModel::find()->where(['hospital_id' => $hospital_id, 'tp_platform' => $tp_platform])->select(['tp_hospital_code'])->scalar();
                        if($tp_hospital_code) {
                            //$query = TbDepartmentThirdPartyRelationModel::find()->where(['id' => $v['id']])->one();
                            //$query->tp_hospital_code = $tp_hospital_code;
                            //$query->save();
                            $sql = "UPDATE `tb_department_third_party_relation` SET `tp_hospital_code` = '$tp_hospital_code' WHERE `id` =".$v['id'];
                            \Yii::$app->db->createCommand($sql)->execute();
                            echo $sql."\n";

                        }else{
                            echo $dep_id.'--'.$hospital_id."no tp_hospital_code";echo "\n";
                        }
                    }else{
                        echo $dep_id."no hospital_id";echo "\n";
                    }
                }
            }

        }

        echo 'end';

    }



    /**
     * 获取医院数据
     */
    public function actionGetHospital()
    {
        $cityCode = [411800];
        foreach ($cityCode as $v) {
            $res = GuahaoSdk::getGuahaoInfo(100, ['citycode' => $v]);
            print_r($res);die;
            if ($res['data']['hospital']) {
                foreach ($res['data']['hospital'] as $item) {

                    $hos = TestModel::find()->where(['tp_hospital_code' => $item['hosid']])->one();
                    if ($hos) {
                        echo $item['hosname'] . "-已存在\n\r";
                    } else {
                        $henan                   = new TestModel();
                        $henan->city_code        = $item['citycode'];
                        $henan->hospital_name    = $item['hosname'];
                        $henan->tp_hospital_code = $item['hosid'];
                        $henan->create_time      = time();
                        $henan->save();
                        echo $item['hosname'] . "-\n\r";
                    }
                }
            }
        }
    }

    public function actionRun()
    {
        $res = GuahaoSdk::getGuahaoInfo(102, ['hosid' => 2020915002]);
        foreach ($res['data']['dept'] as $item) {
            $this->PreInfo($item['deptid']);
            echo $item['deptid'] . "\n\r";
            if ($item['deptid'] == 16090916185106670) {
                die;
            }
        }

    }
    public function PreInfo($deptid)
    {
        $res = GuahaoSdk::getGuahaoInfo(104, [
            'deptid'    => $deptid,
            'doctorid'  => '',
            'startdate' => '',
            'enddate'   => '',
            'nooncode'  => '',
            'state'     => '',
        ]);
        $res      = json_encode($res, JSON_UNESCAPED_UNICODE);
        $filename = $deptid . '.json';
        $filePath = $this->baseDir . '/' . $filename;
        $fp       = fopen($filePath, 'w+');
        fputs($fp, $res . "\n");
    }
    /**
     * 测试队列
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-07-28
     */
    public function actionTest()
    {

        try{
            $res = [];
            $docInfo = DoctorModel::find()->where(['doctor_id'=>265481])->select('tp_platform')->asArray()->one();
            if(!$docInfo){
                throw new \Exception('医生不存在');
            }
            $tp_platform = ArrayHelper::getValue($docInfo,'tp_platform');

            $cooArr = GuahaoPlatformModel::getCoo($tp_platform);
            if($cooArr){
                $flag = false;
                foreach($cooArr as $v){
                    if($v==1){ //百度合作
                        $flag = true;
                        $sdk = BaiduGuahaoSdk::getInstance();
                        $res = $sdk->pushDoctor(265481,1);
                    }
                }
                if(!$flag){
                    throw new \Exception('无coo合作方!');
                }
            }else{
                throw new \Exception('无coo合作方');
            }

            $msg = $this->id . '--res:' . json_encode($res);
            $a = ['code' => 1, 'res' => $msg];
        }catch (\Exception $e){
            $a = ['code'=>0,'msg'=>$e->getMessage()];
        }

        var_dump($a);

        exit;

        DoctorEsModel::deleteDoctorEsData(8617, 245602);
    }

    /**
     * 测试队列生成最终问答数据
     * @author niewei <niewei@yuanxin-inc.com>
     * @date 2018-09-11
     * @param $post_id int 临时表post_id
     * @param $doctor_id int 回复医生ID
     * @return int 队列任务jobID
     */
    public function actionBuild($post_id, $doctor_id)
    {
        $jobId = CommonFunc::pushCreatePostJob($post_id, $doctor_id);

        echo "jobId: " . $jobId . "\n";
        // Check whether the job is waiting for execution.
        echo "isWaiting: " . (\Yii::$app->queue->isWaiting($jobId) ? 'yes' : 'no');
        echo "\n";

        // Check whether a worker got the job from the queue and executes it.
        echo "isReserved: " . (\Yii::$app->queue->isReserved($jobId) ? 'yes' : 'no');
        echo "\n";

        // Check whether a worker has executed the job.
        echo "isDone: " . (\Yii::$app->queue->isDone($jobId) ? 'yes' : 'no');
        echo "\n";
    }

    public function actionUpdateHospital()
    {
        $ids = [
            30954 => 152,
        ];

        foreach ($ids as $k => $v) {
            $res = DoctorModel::find()->where(['hospital_id' => $k])->asArray()->all();
            if ($res) {
                echo $res['doctor_id'] . "\n\r";
                DoctorModel::updateAll(['hospital_id' => $v], ['hospital_id' => $k]);
            }
            $ress = DoctorModel::find()->where(['hospital_id' => $v])->asArray()->all();

            if ($ress) {
                foreach ($ress as $item) {
                    echo $item['doctor_id'] . "更新缓存\n\r";
                    DoctorModel::getInfo($item['doctor_id'], true);
                }
            }

        }
    }

    /**
     * 删除错误的医生头像
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/11/12
     */
    public function actionCheckImg($ids = '')
    {
        $num = 0;
        $page = 0;
        $execute_num = 0;
        $upload_dir = '/data/upload/user_avatar/doctor_avatar/';
        $query = DoctorModel::find()->where(['<>', 'avatar', '']);
        if ($ids) {
           $ids_arr = explode(',', $ids);
           $query->andWhere(['in', 'doctor_id', $ids_arr]);
        }
        $pageSize = 1000;
        $total   = $query->count();
        $maxPage = ceil($total / $pageSize);
        $temp_maxPage = $maxPage;

        do {
            if ($temp_maxPage < 1) {
                break;
            }
            $tpage      = $temp_maxPage-1;
            $offset     = max(0, $tpage) * $pageSize;
            $ress   = $query->offset($offset)->limit($pageSize)->all();
            if (empty($ress)) {
                echo '没有数据了---' . "\n";
                break;
            }
            
            foreach ($ress as $itemObj) {
                $execute_num++;
                echo "最大分页{$maxPage} 当前第{$temp_maxPage}页 共{$total}条数据 当前第{$execute_num}条" ."[" . date('Y-m-d H:i:s') . "] " . "！\n";
                if (is_file($upload_dir . $itemObj->avatar)) {
                    echo $itemObj->doctor_id . "正常\n\r";
                } else {
                    $itemObj->avatar = '';
                    try {
                        $res = $itemObj->save();
                        if ($res) {
                            $num++;
                        }else{
                            throw new \Exception(json_encode($itemObj->getErrors(), JSON_UNESCAPED_UNICODE));
                        }
                        
                    } catch (\Exception $e) {
                        $msg = $e->getMessage();
                        \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生头像置空失败');
                        echo "[" . date('Y-m-d H:i:s') . "] " . $itemObj->doctor_id . " 置空失败：{$msg}！\n";
                    }
                    echo $itemObj->doctor_id . "置空\n\r";
                }
            }
            $temp_maxPage--;
        } while (count($ress) > 0);
        echo "共{$total}条数据 置空{$num}条" . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

    /**
     * 取消好大夫部分医生关联
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-20
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionCancelHaodaifu($tp_doctor_id_str = '', $tp_platform = 0)
    {
        $tp_doctor_list = [];
        $execute_num = 0;
        $success_num = 0;
        $error_arr   = [];

        ##如果传入其他值以传入值为准
        if ($tp_doctor_id_str && $tp_platform) {
            $tp_doctor_list = explode(',', $tp_doctor_id_str);
        } else {
            $tp_platform = 3;
        }

        foreach ($tp_doctor_list as $tp_doctor_id) {
            $execute_num++;
            $filer = [
                'tp_doctor_id' => $tp_doctor_id,
                'tp_platform'  => $tp_platform,
            ];

            $tb_doc_model = TmpDoctorThirdPartyModel::find()->where($filer)->one();
            if (!$tb_doc_model) {
                echo 'id ' . $tp_doctor_id . "[" . date('Y-m-d H:i:s') . "] " . "不存在跳过！\n";
                continue;
            }
            $transition = Yii::$app->getDb()->beginTransaction();
            try {
                $doctor_id                 = $tb_doc_model->doctor_id;
                $tb_doc_model->status      = 0;
                $tb_doc_model->doctor_id   = 0;
                $tb_doc_model->is_relation = 0;
                $res                       = $tb_doc_model->save();
                if ($res) {
                    echo 'id ' . $tp_doctor_id . "[" . date('Y-m-d H:i:s') . "] " . "禁用成功！\n";
                    $success_num++;
                } else {
                    throw new \Exception(json_encode($tb_doc_model->getErrors(), JSON_UNESCAPED_UNICODE));
                }

                ##查询关联表
                $relation_list = TbDoctorThirdPartyRelationModel::find()->where([
                    'doctor_id'    => $doctor_id,
                    'tp_platform'  => $tp_platform,
                    'tp_doctor_id' => $tp_doctor_id,
                ])->all();
                if ($relation_list) {
                    foreach ($relation_list as $relation_model) {
                        $relation_model->delete();
                    }
                }
                $transition->commit();

                echo 'id ' . $tp_doctor_id . "[" . date('Y-m-d H:i:s') . "] " . "操作成功！\n";
            } catch (\Exception $e) {
                $transition->rollBack();
                $msg = $e->getMessage();
                \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 好大夫医生禁用失败');
                echo "[" . date('Y-m-d H:i:s') . "] " . $tp_doctor_id . " 禁用失败：{$msg}！\n";
                $error_arr[] = ['tp_doctor_id' => $tp_doctor_id, 'msg' => $e->getMessage()];
            }

        }
        echo "处理数量：$execute_num\n";
        echo "成功数量：$success_num\n";
        if (count($error_arr) > 0) {
            echo "处理失败信息ID：\n";
            print_r($error_arr);
        }
        echo '总数 ' . $execute_num . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

    /**
     * 部分医院取消科室关联
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-20
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionCancelHospital($hospital_id_str = '')
    {

        $execute_num = 0;
        $success_num = 0;
        $error_arr   = [];

        $id_list = [30742, 26889, 1283, 31366, 22771, 9458, 30964, 31140, 8466, 27904, 1960, 27659, 28373, 22475, 22365, 26205, 31869, 31475, 31682, 31499, 25716, 1762, 31781, 2752, 14759, 5557, 6, 31149, 26089, 30984, 16800, 7851, 12617, 31667, 31626, 31113, 1790, 1397, 1342, 26193, 1347, 31362, 4432, 31406, 27950, 9771, 16099, 27900, 23510, 29060, 1959, 25203, 1733, 2117, 24762, 30429, 3263, 30830, 28690, 24541, 15059, 31573, 18953, 25284, 22125, 1168, 1754, 184, 3225, 3233, 30949, 4250, 4542, 4506, 9407, 14546, 4678, 30710, 28186, 30876, 30428, 29243, 31215, 29253, 23500, 6364, 23693, 10236, 24309, 31561, 28444, 25204, 8855, 25016, 25737, 3594, 4300, 31088, 1487, 24103, 366, 25710, 26110];
        if ($hospital_id_str) {
            $id_list = explode(',', $hospital_id_str);
        }

        foreach ($id_list as $hospital_id) {
            $execute_num++;
            ##查询医院下有医生的跳过
            $doc_total = DoctorModel::find()->where(['hospital_id' => $hospital_id])->count();
            if ($doc_total) {
                echo 'id ' . $hospital_id . ' 医院下存在医生跳过！' . "[" . date('Y-m-d H:i:s') . "]\n";
                continue;
            }
            ##取消科室关联
            ##查询医院下所有科室
            $keshi_list = HospitalDepartmentRelation::find()->select('id')->where(['hospital_id' => $hospital_id])->column();
            if (!$keshi_list) {
                echo 'id ' . $hospital_id . ' 医院下没有科室跳过！' . "[" . date('Y-m-d H:i:s') . "]\n";
                continue;
            }
            if ($keshi_list) {
                foreach ($keshi_list as $key => $hospital_department_id) {
                    $transition = Yii::$app->getDb()->beginTransaction();
                    try {
                        ##查询科室下关联第三方科室表和关联表
                        $tp_department_relation_list = TbDepartmentThirdPartyRelationModel::find()->where(['hospital_department_id' => $hospital_department_id])->all();
                        if ($tp_department_relation_list) {
                            foreach ($tp_department_relation_list as $tp_relation_model) {
                                $tp_relation_model->delete();
                            }
                            echo 'id ' . $hospital_id . ' 医院' . '科室主id:' . $hospital_department_id . '删除关联表成功！' . "[" . date('Y-m-d H:i:s') . "]\n";
                        }
                        ##删除关联表更新第三方科室表
                        $tp_department_list = TmpDepartmentThirdPartyModel::find()->where(['hospital_department_id' => $hospital_department_id])->all();
                        if ($tp_department_list) {
                            foreach ($tp_department_list as $key => $tp_department_model) {
                                $tp_department_model->is_relation            = 0;
                                $tp_department_model->hospital_department_id = 0;
                                $tp_department_model->save();
                            }
                            echo 'id ' . $hospital_id . ' 医院' . '科室主id:' . $hospital_department_id . '删除关联成功！' . "[" . date('Y-m-d H:i:s') . "]\n";
                        }

                        $transition->commit();
                    } catch (\Exception $e) {
                        $transition->rollBack();
                        $msg = $e->getMessage();
                        \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 取消医院科室关联失败');
                        echo "[" . date('Y-m-d H:i:s') . "] " . $hospital_id . ' 科室主id:' . $hospital_department_id . " 取消医院科室关联：{$msg}！\n";
                        $error_arr[] = ['hospital_id' => $hospital_id, 'hospital_department_id' => $hospital_department_id, 'msg' => $e->getMessage()];
                        continue;
                    }
                }
                $success_num++;
            }

        }

        echo "处理数量：$execute_num\n";
        // echo "成功数量：$success_num\n";
        if (count($error_arr) > 0) {
            echo "处理失败信息ID：\n";
            print_r($error_arr);
        }
        echo '总数 ' . $execute_num . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";

    }

    /**
     * 临时导出无头像医生数据
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-05-19
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionExportCsv($hospital_id = 0,$limit = 1000)
    {
        $titles = ['医生ID','医生姓名','一级科室','二级科室','医院ID','医院名称','是否关联','是否有号'];
        $filer = ['avatar'=>''];
        if ($hospital_id) {
            $filer['hospital_id'] = $hospital_id;
        }
        $query = DoctorModel::find()->select('doctor_id,realname,frist_department_name,second_department_name,hospital_id')->where($filer);
        $currentId = 0;
        $total = $query->count();
        $execute_num = 0;
        $str = mb_convert_encoding(join(",",$titles), "gbk","utf-8")."\n";

        while($list = $query->andWhere(['>','doctor_id',$currentId])->limit($limit)->orderBy('doctor_id asc')->asArray()->all()){
            foreach ($list as $key=>$item) {
                $execute_num++;
                $currentId = $item['doctor_id'];
                $returnData = $item;
                //$hos_info = BaseDoctorHospitals::find()->select('name')->where(['id'=>$returnData['hospital_id']])->asArray()->one();
                $returnData['hospital_name'] = $hos_info['name'] ?? '';
                ##查看是否关联
                $relationInfo = TbDoctorThirdPartyRelationModel::find()->select('id')->where(['doctor_id' => $item['doctor_id'],'status'=>1])->asArray()->all();
                $returnData['has_relation'] = !empty($relationInfo) ? 1: 0;
                //根据排班是否有号
                $doctor_real_plus = SnisiyaSdk::getInstance()->getRealPlus(['doctor_id' => $item['doctor_id']]);
                $returnData['doctor_real_plus'] = $doctor_real_plus;
                foreach ($returnData as $k => &$value) {
                    $value = CommonFunc::strFilterCsv($value);
                }
                $str .=mb_convert_encoding(join(",",$returnData), "gbk","utf-8")."\n";
                unset($returnData);
                echo "共{$total}条数据 当前第{$execute_num}条 ".'医生id ' . $currentId . " [" . date('Y-m-d H:i:s') . "]\n";
            }
            unset($list);
        }
        $filename = '无头像医生导出_'.date('Y-m-d-H-i-s', time());

        $baseDir = '/tmp/hospital_tmpfile/'.date('Y-m').'/';
        if (!is_dir($baseDir)) {
            CommonFunc::createDir($baseDir);
        }

        $filePath = $baseDir . $filename;
        $file = CommonFunc::export_csv($str,$filePath);
        echo '文件 ' . $file . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

    /**
     * 删除好大夫医生信息
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-08-04
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionDelDoctor($start_id = 0, $end_id = 0)
    {
        $cache_head = Yii::$app->params['cache_key']['hospital_doctor_info'];
        $query       = DoctorModel::find()->where(['tp_platform' => 3]);
        $pageSize    = 1000;
        $execute_num = 0;
        $error_num   = 0;
        $page        = 1;
        if ($start_id && $end_id) {
            $query->andWhere(['>=', 'doctor_id', (int) ($start_id)]);
            $query->andWhere(['<=', 'doctor_id', (int) ($end_id)]);
        }
        $total   = $query->count();
        $maxPage = ceil($total / $pageSize);
        do {
            if ($page > $maxPage) {
                break;
            }
            $offset = max(0, ($page - 1)) * $pageSize;
            $list   = $query->offset($offset)->limit($pageSize)->orderBy('doctor_id asc')->all();
            if (empty($list)) {
                echo '没有数据了---' . "\n";
                break;
            }
            foreach ($list as $key => $doc_item) {
                $execute_num++;
                echo "最大分页{$maxPage} 当前第{$page}页 共{$total}条数据 当前第{$execute_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "！\n";
                $doctor_id = $doc_item->doctor_id;
                $transition = Yii::$app->getDb()->beginTransaction();
                try {
                    ##查找是否有子医生
                    if ($doc_item->primary_id == 0) {
                        $child_num = DoctorModel::find()->where(['primary_id' => $doctor_id])->count();
                        if ($child_num) {
                            DoctorModel::updateAll(['primary_id' => 0], ['primary_id' => $doctor_id]);
                        }
                    }
                    ##查找是否关联王氏医生
                    if ($doc_item->miao_doctor_id > 0) {
                        // ##删除挂号医生和妙医生关联
                        CommonFunc::setMiaoid2HospitalDoctorID($doc_item->miao_doctor_id,0);
                    }
                    ##删除医生数据
                    $doc_cache_key = sprintf($cache_head, $doctor_id);
                    CommonFunc::delCodisCache($doc_cache_key);
                    $doc_item->delete();
                    $transition->commit();
                } catch (\Exception $e) {
                    $error_num++;
                    $transition->rollBack();
                    $msg = $e->getMessage();
                    \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生删除失败');
                    echo "[" . date('Y-m-d H:i:s') . "] " . "医生id:{$doctor_id}" . " 医生删除失败:{$msg}！\n";
                    continue;
                }
            }
            $page++;
            $num = count($list);
            unset($list);
        } while ($num > 0);
        echo "共{$total}条数据 处理{$execute_num}条 错误{$error_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

    /**
     * 导出百度对接的医院
     * @param int $realPlus
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/10/27
     */
    public function actionHospitalList($realPlus = 0)
    {
        echo "[" . date('Y-m-d H:i:s') . "] 开始！\n";
        $cooArr = GuahaoPlatformModel::getTp(1);
        $hospitalList = GuahaoHospitalModel::find()->where(['tp_platform' => $cooArr, 'status' => 1])->asArray()->all();
        foreach ($hospitalList as $hospital) {
            //查询是否公立
            $hospInfo = BaseDoctorHospitals::HospitalDetail($hospital['hospital_id']);
            if (ArrayHelper::getValue($hospInfo, 'kind') == '公立') {
                //查询是否有号
                $hospital_real_plus = SnisiyaSdk::getInstance()->getRealPlus(['tp_platform' => $hospital['tp_platform'], 'tp_hospital_code' => $hospital['tp_hospital_code']]);
                echo $hospital['tp_platform'] . "," . $hospital['hospital_id'] . "," . $hospInfo['name'] . "," . $hospital['tp_hospital_code'] . "," . $hospital['hospital_name'];
                if ($hospital_real_plus > 0) {
                    echo ",是\n";
                } else {
                    echo ",否\n";
                }
            }
        }

        echo "[" . date('Y-m-d H:i:s') . "] 完成！\n";
    }

    public function actionTgd()
    {
        //$res = CommonFunc::gaode2BaiduGnote('116.44','39.91');
        //$res = CommonFunc::getDistanceByLngLat(['lng'=> '116.445495', 'lat' => '39.921321'], ['lng' => '116.45848', 'lat' => '39.930558']);
        $res = CommonFunc::latlng2cityGd('116.397447','39.909167');
        var_dump($res);die;
    }

}
