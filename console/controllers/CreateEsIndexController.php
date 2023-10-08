<?php

namespace console\controllers;

use common\models\BaseDoctorHospitals;
use common\models\BuildToEsModel;
use common\models\DiseaseEsModel;
use common\models\DoctorModel;
use common\models\HospitalEsModel;
use common\models\GuahaoHospitalModel;
use common\models\HospitalDepartmentRelation;
use common\models\HospitalDoctorIndexEsMap;
use console\controllers\CommonController;

class CreateEsIndexController extends CommonController
{

    public function init()
    {
        parent::init();
        set_time_limit(0);
        ini_set('memory_limit', '512M');
    }

    /**
     * 只生成某一个平台的医院es索引
     * $tp_platform 平台类型 1 河南 2 南京 5 健康160 9 健康之路
     * $hospital_id 医院id
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * date 2022-11-02
     */
    public function actionHospitalPlatform($tp_platform = 0, $hospital_id = 0)
    {
        if (!($tp_platform > 0 && in_array($tp_platform, [1, 2, 5, 9]))) {
            echo '输入的平台ID不在范围内,请输入1,2,5,9' . PHP_EOL;
        }

        $query = GuahaoHospitalModel::find();
        if ($tp_platform) {
            $query->where(['tp_platform' => intval($tp_platform)]);
        }
        if ($hospital_id) {
            $query->where(['hospital_id' => intval($hospital_id)]);
        }

        $hosList = $query->asArray()->all();
        if (empty($hosList)) {
            echo '没有医院数据' . PHP_EOL;
        } else {
            HospitalDoctorIndexEsMap::updateMapping();
            $model = new BuildToEsModel();
            foreach ($hosList as $val) {
                $res = $model->db2esByIdHospital($val['hospital_id']);
                if ($res['code'] == 1) {
                    echo $val['hospital_id'] . 'success' . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                } else {
                    echo $val['hospital_id'] . $res['msg'] . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                }
            }
        }

        echo ('结束：' . date('Y-m-d H:i:s', time())) . PHP_EOL;
    }

    /**
     * 只生成某一个平台的医生es索引
     * $tp_platform 平台类型 1 河南 2 南京 5 健康160 9 健康之路
     * $hospital_id 医院id
     * $doctor_id 医生id
     * $sync 同步更新医生缓存 0 默认不更新 1 更新
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * date 2022-11-02
     */
    public function actionDoctorPlatform($tp_platform = 0, $hospital_id = 0, $doctor_id = 0, $sync = 0)
    {
        if (!($tp_platform > 0 && in_array($tp_platform, [1, 2, 5, 9]))) {
            echo '输入的平台ID不在范围内,请输入1,2,5,9' . PHP_EOL;
        }

        $query = DoctorModel::find();
        if ($tp_platform) {
            $query->where(['tp_platform' => intval($tp_platform)]);
        }
        if ($hospital_id) {
            $query->where(['hospital_id' => intval($hospital_id)]);
        }
        if ($doctor_id) {
            $query->where(['doctor_id' => intval($doctor_id)]);
        }

        $docList = $query->asArray()->all();
        if (empty($docList)) {
            echo '没有医生数据' . PHP_EOL;
        } else {
            $model = new BuildToEsModel();
            foreach ($docList as $val) {
                $res = $model->db2esByIdDoctor($val['doctor_id']);
                if ($res['code'] == 1) {
                    echo $val['doctor_id'] . 'success' . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                } else {
                    echo $val['doctor_id'] . $res['msg'] . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                }
                if ($sync) {
                    DoctorModel::getInfo($val['doctor_id'], true);
                }
            }
        }

        echo ('结束：' . date('Y-m-d H:i:s', time())) . PHP_EOL;
    }

    /**
     * 生成医院es索引
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-24
     * @version 1.0
     * @param   integer    $start_id [description]
     * @param   integer    $end_id   [description]
     * @return  [type]               [description]
     */
    public function actionHospital($start_id = 0, $end_id = 0, $sync = 1)
    {
        // if (empty($start_id) || empty($end_id)) {
        //     exit('start_id end_id is empty!');
        // }
        if (!$end_id) {
            //$end_id = BaseDoctorHospitals::find()->where([])->max('id');  走接口获取不到最大id了 必须传
        }
        if($end_id) {
            HospitalDoctorIndexEsMap::updateMapping();

            //获取禁用的医院列表
            $disHospitalList = (new GuahaoHospitalModel())->getDisableHospital();
            $disHosIds = array_filter(array_column($disHospitalList, 'hospital_id'));
            $disHosNames = array_filter(array_column($disHospitalList, 'hospital_name'));

            $model = new BuildToEsModel();
            for ($i = $start_id; $i <= $end_id; $i++) {
                $res = $model->db2esByIdHospital($i, $disHosIds, $disHosNames);
                if ($res['code'] == 1) {
                    echo $i . 'success' . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                } else {
                    echo $i . $res['msg'] . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                }
                // if ($sync) {
                //     BaseDoctorHospitals::HospitalDetail($i, true);
                //     HospitalDepartmentRelation::hospitalDepartment($i,true);
                // }
            }
        }else{
            echo '请传结束id'.PHP_EOL;
        }
        echo ('结束：' . date('Y-m-d H:i:s', time())) . PHP_EOL;
    }

    /**
     * 生成医生es索引
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-24
     * @version 1.0
     * @param   integer    $start_id [description]
     * @param   integer    $end_id   [description]
     * @param   integer    $onlyCache   只更新医生redis缓存
     * @param   integer    $is_console  1 默认脚本执行  0 不是
     * @return  [type]               [description]
     */
    public function actionDoctor($start_id = 0, $end_id = 0, $sync = 0,$onlyCache=0, $is_console=0)
    {
        // if (empty($start_id) || empty($end_id)) {
        //     exit('start_id end_id is empty!');
        // }
        if (!$end_id) {
            $end_id = DoctorModel::find()->where([])->max('doctor_id');
        }
        $model = new BuildToEsModel();
        for ($i = $start_id; $i <= $end_id; $i++) {
            if($onlyCache==1){
                 DoctorModel::getInfo($i, true,0);
                echo $i . 'success' . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
            }else{
                $res = $model->db2esByIdDoctor($i, $is_console);
                if ($res['code'] == 1) {
                    echo $i . 'success' . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                } else {
                    echo $i . $res['msg'] . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                }
                if ($sync) {
                    DoctorModel::getInfo($i, true);
                }
            }

        }
        echo ('结束：' . date('Y-m-d H:i:s', time())) . PHP_EOL;
    }

    /**
     * 生成疾病索引
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-07-25
     * @version 1.0
     * @param   integer    $start_id [description]
     * @param   integer    $end_id   [description]
     * @return  [type]               [description]
     */
    public function actionDisease($start_id = 0, $end_id = 0)
    {
        if (empty($start_id) || empty($end_id)) {
            exit('start_id end_id is empty!');
        }
        DiseaseEsModel::updateMapping();
        $model = new BuildToEsModel();
        for ($i = $start_id; $i <= $end_id; $i++) {
            $res = $model->db2esByIdDisease($i);
            if ($res['code'] == 1) {
                echo $i . 'success' . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
            } else {
                echo $i . $res['msg'] . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
            }
        }
        echo ('结束：' . date('Y-m-d H:i:s', time())) . PHP_EOL;
    }

    /**
     * 根据医院更新对应的医院以及医院下医生es信息
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-04-20
     * @version v1.0
     * @param   integer    $hospital_id [description]
     * @return  [type]                  [description]
     */
    public function actionHospitalId($hospital_id = 0,$update_doctor = 1)
    {
        if (!$hospital_id) {
            echo "医院id不存在！" . PHP_EOL;die();
        }
        HospitalEsModel::updateHospitalDoctorEsDataByHospital($hospital_id,$update_doctor);
    }

}
