<?php


namespace console\controllers;

use common\models\GuahaoScheduleModel;
use common\models\GuahaoHospitalModel;
use common\models\BaseDoctorHospitals;
use common\models\Department;
use common\models\HospitalEsModel;
use common\models\HospitalDepartmentRelation;
use common\sdks\HospitalSdk;
use Matrix\Exception;
use yii\helpers\ArrayHelper;
use common\libs\CommonFunc;
use common\models\DoctorModel;
use yii\data\Pagination;
use Yii;

class HospitalController extends CommonController
{

    /**
     * 更新 某一平台医院缓存 医院下科室缓存
     * $tp_platform 平台类型 1 河南 2 南京 5 健康160 9 健康之路
     * $hospital_id 医院id
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * date 2022-11-02
     */
    public function actionRunDepartmentPlatform($tp_platform = 0, $hospital_id = 0)
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
            foreach ($hosList as $val) {
                //医院详情缓存
                BaseDoctorHospitals::HospitalDetail($val['hospital_id'],true);
                //医院科室缓存
                HospitalDepartmentRelation::hospitalDepartment($val['hospital_id'],true);
                echo "王氏医院ID:({$val['hospital_id']})更新完成医院详情缓存和医院科室缓存"."\n";
            }
        }
        echo ('结束：' . date('Y-m-d H:i:s', time())) . PHP_EOL;
    }

    /**
     * 更新 医院缓存 医院下科室缓存
     * @author xiujianying
     * @date 2020/7/24
     */
    public function actionRunDepartment($start_id = 0, $end_id = 0) {

        if (empty($start_id) || empty($end_id)) {
            die("请输入开始id和结束id!\n");
        }

        for ($i = $start_id; $i <= $end_id; $i++) {
            //医院详情缓存
            BaseDoctorHospitals::HospitalDetail($i,true);
            //医院科室缓存
            HospitalDepartmentRelation::hospitalDepartment($i,true);
            echo "王氏医院ID:({$i})更新完成医院详情缓存和医院科室缓存"."\n";
        }
        echo "end\n";
    }

    /**
     * 常见科室缓存
     * @author xiujianying
     * @date 2020/8/4
     */
    public function actionRunCommon(){
        Department::department(true);
        Department::department_platform(true);
        echo "end\n";
    }


    /**
     * 更新通用科室缓存
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-08-04
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionKeshi()
    {
        $comlist = Department::find()->where(['is_common'=>1,'status'=>1])->select('department_id,department_name,parent_id,status,is_common')->indexBy('department_id')->asArray()->all();
        if ($comlist) {
            foreach ($comlist as $key => $value) {
                CommonFunc::getKeshiInfo($value['department_id'],1);
                echo "科室id:{$value['department_id']} name:".$value['department_name'] . '更新成功' . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
            }
        }
        echo ('结束：' . date('Y-m-d H:i:s', time())) . PHP_EOL;

    }

    /**
     * 地区缓存
     * @author xiujianying
     * @date 2020/8/4
     */
    public function actionRunDistrict(){
        Department::district(true);
        echo "end\n";
    }


    /**
     * 更新所有一级下二级科室缓存
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-09-12
     * @version 1.0
     * @return  [type]     [description]
     */
    public function actionSkeshiList()
    {
        $fkeshi_list = Department::find()->where(['is_common'=>1,'status'=>1])->select('department_id,department_name')->indexBy('department_id')->asArray()->all();
        foreach ($fkeshi_list as $key => $value) {
            CommonFunc::get_all_skeshi_list($value['department_id'],1);
            echo "科室id:{$value['department_id']} name:".$value['department_name'] . '下二级科室更新成功' . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
        }
         echo ('结束：' . date('Y-m-d H:i:s', time())) . PHP_EOL;
    }

    public function actionKind($pageSize = 500)
    {
        $query       = DoctorModel::find()->select('hospital_id,hospital_type')->where([]);
        // $pageSize    = 500;
        $execute_num = 0;
        $error_num   = 0;
        $page        = 1;
        $query->andWhere(['>', 'hospital_id', 0]);
        $query->groupBy('hospital_id');
        $total   = $query->count();
        $maxPage = ceil($total / $pageSize);
        $temp_maxPage = $maxPage;
        do {

            $offset     = max(0, ($page-1)) * $pageSize;
            $list   = $query->offset($offset)->limit($pageSize)->orderBy('hospital_id asc')->asArray()->all();
            if (empty($list)) {
                echo '没有数据了---' . "\n";
                break;
            }
            foreach ($list as $key => $doctor_model) {
                $execute_num++;
                $hospital_id = $doctor_model['hospital_id'];
                echo "最大分页{$maxPage} 当前第{$page}页 共{$total}条数据 当前第{$execute_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "！\n";
                $hos_info = BaseDoctorHospitals::getInfo($hospital_id);
                $hospital_type = ArrayHelper::getValue($hos_info,'kind') == '公立' ? 1 : 2;
                $hospital_name = ArrayHelper::getValue($hos_info,'name','');
                try {
                    $doc_res = DoctorModel::updateAll(['hospital_type' => $hospital_type,'hospital_name'=>$hospital_name],['hospital_id' => $hospital_id]);
                } catch (\Exception $e) {
                    $error_num++;
                    $msg = $e->getMessage();
                    \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生更新保存失败');
                    echo "[" . date('Y-m-d H:i:s') . "] " . "医院id:{$hospital_id}" . " 医生保存失败:{$msg}！\n";
                    break;
                }
            }
            $page++;
            $num = count($list);
            unset($list);
        } while ($num > 0);
        echo "共{$total}条数据 处理{$execute_num}条 错误{$error_num}条" . "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    }

    /**
     * 更新第三方医院缓存
     * @param $tp_platform
     * @param $tp_hospital_code
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/7/28
     */
    public function actionUpdateTpHospitalCache($tp_platform, $tp_hospital_code)
    {
        echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：\n";
        $query = GuahaoHospitalModel::find();
        if ($tp_platform != 'all') {
            $query->andWhere(['tp_platform' => $tp_platform]);
        }

        if ($tp_hospital_code != 'all') {
            $query->andWhere(['tp_hospital_code' => $tp_hospital_code]);
        }

        $totalQuery = clone $query;
        $totalCount = $totalQuery->count();
        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => 100,
        ]);
        $page = 1;

        do {
            $pageObj->setPage($page - 1, false);
            $list = $query->select('tp_platform,tp_hospital_code')->orderBy(['id' => SORT_ASC])->offset($pageObj->offset)->limit($pageObj->limit)->asArray()->all();
            if (empty($list)) {
                break;
            } else {
                foreach ($list as $k => $v) {
                    GuahaoHospitalModel::getTpHospitalCache($v['tp_platform'], $v['tp_hospital_code'], true);
                }
            }
            echo "[" . date('Y-m-d H:i:s') . "] 第 {$page} 页处理完成！\n";
            $page++;
            $dataCount = count($list);
            unset($data);
        } while ($dataCount > 0);
    }

    /**
     * 批量下架第三方医院（慎用）
     * @param $tp_platform
     * @param $tp_hospital_codes
     * @param string $remarks
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/11/16
     */
    public function actionBatchOffTpHospital($tp_platform, $tp_hospital_codes, $remarks = '')
    {
        echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：\n";

        $tp_hospital_codes = explode(',', $tp_hospital_codes);
        if (empty($tp_hospital_codes)) {
            echo "[" . date('Y-m-d H:i:s') . "] 医院编码不能为空！\n";
            exit();
        }

        foreach ($tp_hospital_codes as $tp_hospital_code){
            //获取医院数据
            $tpHospitalModel = GuahaoHospitalModel::find()->where(['tp_platform' => $tp_platform, 'tp_hospital_code' => $tp_hospital_code])->one();
            if (empty($tpHospitalModel)) {
                echo "[" . date('Y-m-d H:i:s') . "] 医院信息未找到！\n";
                continue;
            }
            if ($tpHospitalModel->status != '1') {
                echo "[" . date('Y-m-d H:i:s') . "] 医院关联状态错误！\n";
                continue;
            }

            echo "禁用\"{$tpHospitalModel->hospital_name}\" \n";
            //禁用第三方医院
            $tpHospitalModel->status = 2;
            $tpHospitalModel->remarks = $remarks;
            $tpHospitalModel->save();
            if (!$tpHospitalModel->validate() || !$tpHospitalModel->save()) {
                echo "[" . date('Y-m-d H:i:s') . "] 医院禁用失败！\n";
                continue;
            }

            //禁用排班
            echo "[" . date('Y-m-d H:i:s') . "] 开始禁用排班：\n";
            $query = GuahaoScheduleModel::find()->where(['tp_platform' => $tp_platform, 'tp_scheduleplace_id' => $tp_hospital_code]);

            $totalQuery = clone $query;
            $totalCount = $totalQuery->count();
            $pageObj = new Pagination([
                'totalCount' => $totalCount,
                'pageSize' => 100,
            ]);
            $page = 1;

            do {
                $pageObj->setPage($page - 1, false);
                $list = $query->select('scheduling_id,status')->orderBy(['scheduling_id' => SORT_ASC])->offset($pageObj->offset)->limit($pageObj->limit)->asArray()->all();
                if (empty($list)) {
                    break;
                } else {
                    $scheduleIdList = [];
                    foreach ($list as $k => $v) {
                        $scheduleIdList[] = $v['scheduling_id'];
                    }
                    if (!empty($scheduleIdList)) {
                        GuahaoScheduleModel::updateScheduleCache($scheduleIdList, $tp_platform);
                    }
                }
                echo "[" . date('Y-m-d H:i:s') . "] 第 {$page} 页处理完成！\n";
                $page++;
                $dataCount = count($list);
                unset($data);
            } while ($dataCount > 0);
        }

        echo "[" . date('Y-m-d H:i:s') . "] 处理完成！\n";
    }

    /**
     * 下架某个第三方医院（慎用）
     * @param $tp_platform
     * @param $tp_hospital_code
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/7/29
     */
    public function actionOffTpHospital($tp_platform, $tp_hospital_code, $remarks = '')
    {
        echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：\n";
        //获取医院数据
        $tpHospitalModel = GuahaoHospitalModel::find()->where(['tp_platform' => $tp_platform, 'tp_hospital_code' => $tp_hospital_code])->one();
        if (empty($tpHospitalModel)) {
            echo "[" . date('Y-m-d H:i:s') . "] 医院信息未找到！\n";
            exit();
        }
        if ($tpHospitalModel->status != '1') {
            echo "[" . date('Y-m-d H:i:s') . "] 医院关联状态错误！\n";
            exit();
        }

        //询问是否禁用
        echo "确认禁用\"{$tpHospitalModel->hospital_name}\"? [Yes|No] \n";
        $answer = trim(fgets(STDIN));
        if (strncasecmp($answer, 'y', 1)) {
            echo "[" . date('Y-m-d H:i:s') . "] 结束！\n";
            exit();
        }

        //禁用第三方医院
        $tpHospitalModel->status = 2;
        $tpHospitalModel->remarks = $remarks;
        $tpHospitalModel->save();
        if (!$tpHospitalModel->validate() || !$tpHospitalModel->save()) {
            echo "[" . date('Y-m-d H:i:s') . "] 医院禁用失败！\n";
            exit();
        }

        //禁用排班
        echo "[" . date('Y-m-d H:i:s') . "] 开始禁用排班：\n";
        $query = GuahaoScheduleModel::find()->where(['tp_platform' => $tp_platform, 'tp_scheduleplace_id' => $tp_hospital_code]);

        $totalQuery = clone $query;
        $totalCount = $totalQuery->count();
        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => 100,
        ]);
        $page = 1;

        do {
            $pageObj->setPage($page - 1, false);
            $list = $query->select('scheduling_id,status')->orderBy(['scheduling_id' => SORT_ASC])->offset($pageObj->offset)->limit($pageObj->limit)->asArray()->all();
            if (empty($list)) {
                break;
            } else {
                $scheduleIdList = [];
                foreach ($list as $k => $v) {
                    $scheduleIdList[] = $v['scheduling_id'];
//                    if (!in_array($v['status'], [3, 4])) {
//                        $scheduleModel = GuahaoScheduleModel::find()->where(['scheduling_id' => $v['scheduling_id']])->one();
//                        if (!empty($scheduleModel)) {
//                            $scheduleModel->status = 4;
//                            $scheduleModel->save();
//                            $scheduleIdList[] = $v['scheduling_id'];
//                        }
//                    }
                }
                if (!empty($scheduleIdList)) {
//                    sleep(10);##暂停十秒
                    GuahaoScheduleModel::updateScheduleCache($scheduleIdList, $tp_platform);
                }
            }
            echo "[" . date('Y-m-d H:i:s') . "] 第 {$page} 页处理完成！\n";
            $page++;
            $dataCount = count($list);
            unset($data);
        } while ($dataCount > 0);

        echo "[" . date('Y-m-d H:i:s') . "] 处理完成！\n";
    }

    /**
     * 按医院、医生推送全部排班脚本
     * @param $tp_platform
     * @param int $primary_id
     * @param string $tp_hospital_code
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/11/9
     */
    public function actionPushScheduleToCoo($tp_platform, $primary_id = 0, $tp_hospital_code = '')
    {
        if (empty($primary_id) && empty($tp_hospital_code)) {
            echo "[" . date('Y-m-d H:i:s') . "] 医生主id和第三方医院id不能同时为空！\n";
            exit();
        }

        echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：\n";
        $query = GuahaoScheduleModel::find()->where(['tp_platform' => $tp_platform]);

        if (!empty($primary_id)) {
            $query->andWhere(['primary_id' => $primary_id]);
        }

        if (!empty($tp_hospital_code)) {
            $query->andWhere(['tp_scheduleplace_id' => $tp_hospital_code]);
        }

        $totalQuery = clone $query;
        $totalCount = $totalQuery->count();
        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => 100,
        ]);
        $page = 1;

        do {
            $pageObj->setPage($page - 1, false);
            $list = $query->select('scheduling_id')->orderBy(['scheduling_id' => SORT_ASC])->offset($pageObj->offset)->limit($pageObj->limit)->asArray()->all();
            if (empty($list)) {
                break;
            } else {
                $scheduleIdList = [];
                foreach ($list as $k => $v) {
                    $scheduleIdList[] = $v['scheduling_id'];
                }
                if (!empty($scheduleIdList)) {
                    GuahaoScheduleModel::updateScheduleCache($scheduleIdList, $tp_platform);
                }
            }
            echo "[" . date('Y-m-d H:i:s') . "] 第 {$page} 页处理完成！\n";
            $page++;
            $dataCount = count($list);
            unset($data);
        } while ($dataCount > 0);

        echo "[" . date('Y-m-d H:i:s') . "] 处理完成！\n";
    }

    /**
     * 检测挂号医院在基础医院那边是否正常
     * @param $tp_platform
     * @param string $tp_hospital_code
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * date 2022/04/01
     */
    public function actionCheckHospitalStatus($tp_platform='', $tp_hospital_code='')
    {
        $hospitalModel = GuahaoHospitalModel::find()->where(['status' => 1]);
        if ($tp_platform) {
            $hospitalModel->andWhere(['tp_platform' => $tp_platform]);
        }

        if ($tp_hospital_code) {
            $hospitalModel->andWhere(['tp_hospital_code' => $tp_hospital_code]);
        }
        $hosList = $hospitalModel->asArray()->all();
        $total = count($hosList);
        $successNum = $failNum = 0;

        if (!empty($hosList)) {
            foreach ($hosList as $key => $hos) {
                $hospitalCache = \common\models\BaseDoctorHospitals::HospitalDetail($hos['hospital_id'], true);
                if (!$hospitalCache) {
                    $failNum++;
                    echo "第{$key}条： {$hos['hospital_id']} =={$hos['hospital_name']}==> {$hos['tp_hospital_code']} ===> 无基础数据！\n";
                    continue;
                } else {
                    $statusDes = "正常";
                    if ($hospitalCache['status'] != 0) {
                        $statusDes = "未审核";
                    }
                    $is_hospital_project_des = "是";
                    if ($hospitalCache['is_hospital_project'] != 1) {
                        $is_hospital_project_des = "否";
                    }
                    if ($hospitalCache['status'] != 0 || $hospitalCache['is_hospital_project'] != 1) {
                        $failNum++;
                        echo "第{$key}条： {$hos['hospital_id']} =={$hos['hospital_name']}==> {$hos['tp_hospital_code']} ===> 状态码（{$statusDes}）：{$hospitalCache['status']} ==> 是否关联医院业务线（{$is_hospital_project_des}）:{$hospitalCache['is_hospital_project']}！\n";
                        continue;
                    }
                    $successNum++;
                    echo "第{$key}条： {$hos['hospital_id']} =={$hos['hospital_name']}==> {$hos['tp_hospital_code']} ===> 已关联基础医院数据！\n";
                }
            }
        } else {
            echo "没有医院信息！\n";
        }
        echo "总共：{$total} 家医院！已检测成功关联数量：{$successNum}, 失效数量：{$failNum} \n";
    }
}