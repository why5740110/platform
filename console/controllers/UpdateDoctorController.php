<?php

namespace console\controllers;

use common\models\DoctorModel;
use common\sdks\ucenter\PihsSDK;
use common\models\BuildToEsModel;
use common\models\BaseDoctorHospitals;
use yii\helpers\ArrayHelper;
use common\libs\CommonFunc;

class UpdateDoctorController extends CommonController
{
    public function actionRun($start = 0, $end = 0)
    {
        if (!$end) {
            $end = DoctorModel::find()->where([])->max('doctor_id');
        }
        for ($i = $start; $i <= $end; $i++) {
            $res = DoctorModel::getInfo($i, true);
            if ($res) {
                echo $i . "\n";
            } else {
                echo "不存在\n";
            }
        }
    }

    // public function actionIsPlus($page=1, $limit = 10,$doctor_id = 0)
    // {
    //     do {
    //         $offset = max(($page - 1), 0) * $limit;
    //         if ($doctor_id) {
    //             $res = DoctorModel::find()->select('miao_doctor_id,doctor_id,hospital_id')->where(['doctor_id'=>$doctor_id])->offset($offset)->limit($limit)->asArray()->all();
    //         }else{
    //             $res = DoctorModel::find()->select('miao_doctor_id,doctor_id,hospital_id')->where(['<>','miao_doctor_id',0])->offset($offset)->limit($limit)->asArray()->all();
    //         }
            
    //         if (!$res) {
    //             echo '没有数据了' . "\n";
    //         }
    //         $logInfo='';
    //         foreach ($res as $item) {
    //             $params = [
    //                 'doctor_ids' => $item['miao_doctor_id'],
    //             ];
    //             $jiahao_info = PihsSDK::getInstance()->plus_list($params);
    //             $res = DoctorModel::find()->where(['doctor_id' => $item['doctor_id']])->one();
    //             if($jiahao_info){
    //                 $res->setAttribute('is_plus', 1);
    //             }else{
    //                 $res->setAttribute('is_plus', 0);
    //             }
    //             if ($res->getOldAttribute('is_plus') != $res->getAttribute('is_plus')) {
    //                 $logInfo = '] is_plus 由' . $res->getOldAttribute('is_plus') . '改为' . $res->getAttribute('is_plus');
    //             }
    //             if($res->save()){
    //                 $this->UpdateInfo($item['doctor_id'],$item['hospital_id']);
    //                 echo '更新医生 [' . $item['doctor_id'] . $logInfo . "成功[".$res['is_plus']."]\n\r";
    //             }
                
    //         }
    //         $page++;
    //         usleep(50);
    //     } while (count($res) > 0);

    // }

    public function UpdateInfo($doctor_id, $hospital_id)
    {
        DoctorModel::getInfo($doctor_id, 1);
        $model = new BuildToEsModel();
        $model->db2esByIdDoctor($doctor_id);
        $model->db2esByIdHospital($hospital_id);
        BaseDoctorHospitals::HospitalDetail($hospital_id,true);
    }

    /**
     * 更新医生所在医院信息
     * @param int $start
     * @param int $end
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2022/1/11
     */
    public function actionHospitalType($start = 0, $end = 0)
    {
        if (!$end) {
            $end = DoctorModel::find()->where([])->max('doctor_id');
        }
        for ($i = $start; $i <= $end; $i++) {
            $doctor_model = DoctorModel::find()->where(['doctor_id' => $i])->one();
            if ($doctor_model) {
                $hos_info = BaseDoctorHospitals::getInfo($doctor_model->hospital_id);
                $hospital_type = ArrayHelper::getValue($hos_info, 'kind') == '公立' ? 1 : 2;
                if ($doctor_model->hospital_type != $hospital_type) {
                    $doctor_model->hospital_type = $hospital_type;
                    $doc_res = $doctor_model->save();
                    if (!$doc_res) {
                        echo "更新失败：$i\n";
                        continue;
                    }
                    echo '成功：';
                }
                echo $i . "\n";
            } else {
                echo "不存在\n";
            }
        }
    }

    /**
     * 更新健康160的医生头像问题
     * 命令： php ./yii update-doctor/update-doctor-avatar 5 >> /tmp/update-doctor-avatar.log 2>&1
     * 线上命令： /usr/local/php7.4.8/bin/php /data/wwwroot/nisiya.top/yii update-doctor/update-doctor-avatar 5 >> /tmp/update-doctor-avatar.log 2>&1
     * https://imagesbasicinfo.91160.com/upload/doctor/3/doctor_2920_15755128829675.jpeg
     * @param int $tp_platform
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * date 2022/09/19
     */
    public function actionUpdateDoctorAvatar($tp_platform=5)
    {
        $page        = 1;
        $limit       = 1000;
        $likeUrl1 = "https://images.91160.com/upload/";
        $likeUrl2 = "https://imagesbasicinfo.91160.com/upload/";

        $total = DoctorModel::find()
            ->where(['tp_platform' => $tp_platform])
            ->andWhere(['like', 'source_avatar', $likeUrl1 . $likeUrl2])
            ->count();

        if ($total <= 0) {
            echo "没有符合条件的医生数据" . PHP_EOL;
            die;
        }

        do {
            $offset     = max(0, ($page - 1)) * $limit;
            $docList = DoctorModel::find()
                ->where(['tp_platform' => $tp_platform])
                ->andWhere(['like', 'source_avatar', $likeUrl1 . $likeUrl2])
                ->offset($offset)->limit($limit)->asArray()->all();

            foreach ($docList as $key => $val) {
                try {
                    $newAvatar = str_replace($likeUrl1, "", $val['source_avatar']);
                    //上传头像 先同步上传
                    $avatar = CommonFunc::uploadDoctorAvatarByUrl($newAvatar);
                    $info = DoctorModel::findOne($val['doctor_id']);
                    $info->source_avatar = $newAvatar;
                    $info->avatar = $avatar;
                    $res = $info->save();
                    if ($res) {
                        DoctorModel::getInfo($val['doctor_id'], 1);
                        $model = new BuildToEsModel();
                        $model->db2esByIdDoctor($val['doctor_id']);
                        echo "【{$key}】- 医生ID：{$val['doctor_id']},姓名：{$val['realname']},原头像地址:{$val['source_avatar']},新头像地址:{$newAvatar}, avatar地址:{$avatar}, 头像调整已完成" . PHP_EOL;
                    } else {
                        echo "【{$key}】- 医生ID：{$val['doctor_id']},姓名：{$val['realname']}, 头像调整已失败" . PHP_EOL;
                    }
                } catch (\Exception $e) {
                    $msg = $e->getMessage();
                    echo "【{$key}】- 医生ID：{$val['doctor_id']},姓名：{$val['realname']}, 头像调整已失败:{$msg}" . PHP_EOL;
                }
            }

            $num = count($docList);
            $page++;
        } while ($num > 0);

    }
}