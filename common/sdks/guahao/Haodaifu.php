<?php
/**
 * Created by PhpStorm.
 * User: lskla
 * Date: 2020/11/26
 * Time: 17:10
 */

namespace common\sdks\guahao;

use common\libs\CommonFunc;
use common\models\BaseDoctorHospitals;
use common\models\GuahaoHospitalModel;
use common\models\TmpBaseDepThirdPartyRelationModel;
use common\models\TmpDepartmentThirdPartyModel;
use common\models\TmpDoctorThirdPartyModel;
use common\sdks\GuahaoInterface;
use common\sdks\snisiya\SnisiyaSdk;
use yii\base\Controller;

class Haodaifu extends Controller implements GuahaoInterface
{
    const TP_PLATFORM = 3;

    /**
     * 拉取好大夫医生
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/11/3
     */
    public function actionGetTpDoctor($docid = 0)
    {
        $page     = 1;
        $pagesize = 20;
        do {
            $params = [
                'tp_platform' => self::TP_PLATFORM,
                'page'        => $page,
                'pagesize'    => $pagesize,
            ];

            $docids = $docid == 0 ? SnisiyaSdk::getInstance()->getDoctorIds($params)['list'] : [$docid];

            if (!$docids) {
                echo '没有数据了' . "\n";
                break;
            }

            $doctorTitles = array_flip(CommonFunc::getTitle());
            foreach ($docids as $items) {

                $item = SnisiyaSdk::getInstance()->getDoctorByid(['tp_platform' => self::TP_PLATFORM, 'tp_doctor_id' => $items]);

                if (isset($item) && !empty($item)) {
                    if (isset($item['tp_doctor_id'])) {
                        
                        $item['realname'] = $item['realname'] ?? '';
                        $has_demo =  CommonFunc::isDemoDoctor($item['realname']);
                        if ($has_demo) {
                            echo '[医生]' . $item['realname'] . "-测试医生过滤\n\r";
                            continue;
                        }
                        $docInfo = TmpDoctorThirdPartyModel::find()->select('*')->where(['tp_platform' => self::TP_PLATFORM, 'tp_doctor_id' => $item['tp_doctor_id']])->asArray()->one();
                        if ($docInfo) {
                            echo '[医生]' . $item['realname'] . "-已存在\n\r";
                        } else {

                            $item                    = array_filter($item);
                            $doc                     = new TmpDoctorThirdPartyModel();
                            $doc->realname           = $item['realname'] ?? '';
                            $doc->source_avatar      = $item['source_avatar'] ?? '';
                            $doc->good_at            = $item['good_at'] ?? '';
                            $doc->profile            = $item['profile'] ?? '';
                            $doc->province           = 0;
                            $doc->city               = 0;
                            $doc->district           = 0;
                            $doctorjobtitle          = $item['job_title'] ?? '未知';
                            $doc->job_title_id       = $doctorTitles[$doctorjobtitle] ?? 99;
                            $doc->job_title          = $doctorjobtitle;
                            $doc->professional_title = '';
                            //$doc->hospital_id = $this->getMiaoId($item['hospital_name']);
                            $doc->hospital_name          = $item['hospital_name'];
                            $doc->tp_platform            = self::TP_PLATFORM;
                            $doc->tp_frist_department_id = $item['third_fkid'] ?? 0;
                            $doc->tp_department_id       = $item['third_skid'] ?? 0;
                            $doc->frist_department_name  = $item['third_fkname'] ?? '';
                            $doc->second_department_name = $item['third_skname'] ?? '';
                            $doc->tp_doctor_id           = $item['tp_doctor_id'];
                            $doc->tp_primary_id          = $item['tp_primary_id'];
                            $doc->tp_hospital_code       = $item['tp_hospital_code'];
                            $doc->create_time            = time();
                            $doc->save();
                            //var_dump($doc->getErrors());
                            echo '好大夫 医生 uid：' . $item['tp_doctor_id'] . '--' . date("Y-m-d H:i:s") . " [入库] \n\r";
                        }
                        //医院
                        $hos = GuahaoHospitalModel::find()->select('*')->where([
                            'tp_hospital_code' => $item['tp_hospital_code'],
                            'tp_platform'      => self::TP_PLATFORM,
                        ])->one();
                        if ($hos) {
                            echo '[医院]' . $item['hospital_name'] . "-已存在\n\r";
                        } else {
                            $newhos                    = new GuahaoHospitalModel();
                            $exrInfo                   = CommonFunc::getHospitalGuahaoinfo(self::TP_PLATFORM, $item['tp_hospital_code']);
                            $newhos->tp_hospital_code  = $item['tp_hospital_code'];
                            $newhos->tp_platform       = self::TP_PLATFORM;
                            $newhos->hospital_name     = $item['hospital_name'];
                            $newhos->province          = $item['province'];
                            $newhos->tp_hospital_level = $item['hospital_level'];
                            if ($this->getMiaoId($item['hospital_name'])) {
                                $newhos->status      = 1;
                                $newhos->hospital_id = $this->getMiaoId($item['hospital_name']);
                            }
                            $newhos->create_time            = time();
                            $newhos->tp_allowed_cancel_day  = $exrInfo['tp_allowed_cancel_day'];
                            $newhos->tp_allowed_cancel_time = $exrInfo['tp_allowed_cancel_time'];
                            $newhos->tp_guahao_description  = $exrInfo['tp_guahao_description'];
                            $newhos->save();
                            echo '[医院]' . $item['hospital_name'] . '--' . date("Y-m-d H:i:s") . " [入库] \n\r";
                        }
                        //科室
                        $dep = TmpDepartmentThirdPartyModel::find()->where([
                            'tp_hospital_code' => $item['tp_hospital_code'],
                            'tp_platform'      => self::TP_PLATFORM,
                            'tp_department_id' => $item['tp_department_id'],
                        ])->one();
                        if ($dep) {
                            echo '[科室]' . $item['hospital_name'] . $item['third_fkname'] . $item['third_skname'] . "-已存在\n\r";
                        } else {
                            $newdep                   = new TmpDepartmentThirdPartyModel();
                            $newdep->tp_hospital_code = $item['tp_hospital_code'];
                            $newdep->tp_platform      = self::TP_PLATFORM;
                            $newdep->hospital_name    = $item['hospital_name'];

                            $newdep->third_fkid   = $item['third_fkid'];
                            $newdep->third_fkname = $item['third_fkname'];
                            $newdep->third_skid   = $item['third_skid'];
                            $newdep->third_skname = $item['third_skname'];

                            $newdep->tp_department_id = $item['tp_department_id'];
                            $newdep->department_name  = $item['department_name'];

                            $newdep->create_time = time();
                            $newdep->save();
                            echo '[科室]' . $item['hospital_name'] . $item['third_fkname'] . $item['third_skname'] . '--' . date("Y-m-d H:i:s") . " [入库] \n\r";

                        }

                        echo "\n\r";

                    }

                } else {
                    echo '没有数据' . PHP_EOL;
                    exit;
                }
            }
            $page++;
        } while (count($docids) > 0);
    }

    public function actionGetTpHospital($tp_platform = 'haodaifu')
    {

    }

    public function getMiaoId($hospital_name)
    {
        /*$hos = BaseDoctorHospitals::find()->select('id,level')->where(['name' => $hospital_name])->orWhere(['nick_name' => $hospital_name])->one();
        if ($hos) {
            return $hos['id'];
        }*/
        return 0;
    }

    public function getKeshi($fkid, $skid)
    {
        $dep = TmpBaseDepThirdPartyRelationModel::find()->where([
            'third_fkid' => $fkid,
            'third_skid' => $skid,
        ])->one();
        if ($dep) {
            return $dep;
        }
        return 0;
    }
}
