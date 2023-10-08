<?php
/**
 * Created by PhpStorm.
 * @file OnceCacheController.php
 * @author lipengbo <lipengbo@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-10-24
 */

namespace console\controllers;

use common\libs\CommonFunc;
use common\models\BaseDoctorHospitals;
use common\models\Department;
use common\models\DoctorModel;
use common\models\GuahaoCooListModel;
use common\models\GuahaoHospitalModel;
use common\models\GuahaoPlatformListModel;
use common\models\HospitalDepartmentRelation;
use common\models\minying\DepartmentWeightConfigModel;
use yii\console\Controller;

/**
 * 一次性生成缓存脚本
 * @author lipengbo <lipengbo@yuanxinjituan.com>
 * @date 2022-10-24
 * Class OnceCreateCacheController
 * @package console\controllers
 */
class OnceCacheController extends Controller
{
    public function init()
    {
        date_default_timezone_set('PRC');
    }

    /**
     * 生成常见科室缓存 sh hospital-doctor-info.sh
     * @throws \Exception
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-24
     */
    public function actionHospitalDetail($start = 0, $end = 0)
    {
        echo date('Y-m-d H:i:s') . " start\n";
        for ($i = $start; $i <= $end; $i++) {
            //医院详情缓存
            BaseDoctorHospitals::HospitalDetail($i, true);
            //医院科室缓存
            HospitalDepartmentRelation::hospitalDepartment($i, true);
            echo "start:{$start}, current:{$i}, end:{$end}\n";
        }
        echo date('Y-m-d H:i:s') . " done\n";
    }

    /**
     * 更新王氏id对应的挂号医生id  原miaoid_hospital_doctor_id   sh hospital-doctor-info.sh
     * @param $start
     * @param $end
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-11-05
     */
    public function actionHospitalDoctor($start = 0, $end = 0)
    {
        echo date('Y-m-d H:i:s') . " start\n";
        $maxId = $start;
        do {
            $res = DoctorModel::find()->select(['miao_doctor_id', 'doctor_id'])->where("doctor_id > '{$maxId}' and doctor_id <= '{$end}' and miao_doctor_id > 0 and status = 1")->orderBy('doctor_id')->limit(100)->asArray()->all();
            $maxId = $res ? (int)max(array_column($res, 'doctor_id')) : 0;
            foreach ($res as $v) {
                CommonFunc::setMiaoid2HospitalDoctorID($v['miao_doctor_id'], $v['doctor_id']);
            }
        } while ($res);
        echo date('Y-m-d H:i:s') . " done\n";
    }

    /**
     * 修改科室信息为王氏科室 php yii once-cache/keshi-info php /data/wwwroot/nisiya.top/yii /once-cache/keshi-info
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-24
     */
    public function actionKeshiInfo()
    {
        echo date('Y-m-d H:i:s') . " start\n";
        foreach (CommonFunc::getDepartment() as $v) {
            CommonFunc::getKeshiInfo($v['id'], true);
        }
        echo date('Y-m-d H:i:s') . " done\n";
    }

    /**
     * 生成第三方医院缓存 php /data/wwwroot/nisiya.top/yii /once-cache/hospital-info >>/tmp/hospital-info.log 2>&1 &
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-24
     */
    public function actionHospitalInfo()
    {
        echo date('Y-m-d H:i:s') . " start\n";
        $maxId = 0;
        do {
            $res = GuahaoHospitalModel::find()->select(['id', 'tp_platform', 'tp_hospital_code'])->where("id > '{$maxId}'")->orderBy('id')->limit(100)->asArray()->all();
            $maxId = $res ? (int)max(array_column($res, 'id')) : 0;
            foreach ($res as $v) {
                GuahaoHospitalModel::getTpHospitalCache($v['tp_platform'], $v['tp_hospital_code'], true);
                echo "tp_platform:{$v['tp_platform']}, tp_hospital_code:{$v['tp_hospital_code']}\n";
            }
        } while ($res);
        echo date('Y-m-d H:i:s') . " done\n";
    }

    /**
     * 生成合作平台缓存 php /data/wwwroot/nisiya.top/yii /once-cache/coo-list
     * @throws \Exception
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-24
     */
    public function actionCooList()
    {
        echo date('Y-m-d H:i:s') . " start\n";
        $data = GuahaoCooListModel::getCooPlatformListCache(true);
        var_dump($data);
        echo date('Y-m-d H:i:s') . " done\n";
    }

    /**
     * 生成第三方平台缓存 php /data/wwwroot/nisiya.top/yii /once-cache/platform-list
     * @throws \Exception
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-24
     */
    public function actionPlatformList()
    {
        echo date('Y-m-d H:i:s') . " start\n";
        $data = GuahaoPlatformListModel::getPlatformListCache(true);
        var_dump($data);
        echo date('Y-m-d H:i:s') . " done\n";
    }

    /**
     * 将科室权重配置数据保存到redis中 php /data/wwwroot/nisiya.top/yii /once-cache/department-config-list
     * @throws \Exception
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-24
     */
    public function actionDepartmentConfigList()
    {
        echo date('Y-m-d H:i:s') . " start\n";
        $res = DepartmentWeightConfigModel::getALl('second_department_id,second_department_name,weight');
        if ($res) {
            $key = \Yii::$app->params['cache_key']['department_config_list'];
            CommonFunc::setCodisCache($key, $res);
        }
        echo date('Y-m-d H:i:s') . " done\n";
    }

    /**
     * 生成常见科室缓存 php /data/wwwroot/nisiya.top/yii /once-cache/common-department
     * @throws \Exception
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-24
     */
    public function actionCommonDepartment()
    {
        echo date('Y-m-d H:i:s') . " start\n";
        var_dump(Department::department(true));
        echo date('Y-m-d H:i:s') . " done\n";
    }

}