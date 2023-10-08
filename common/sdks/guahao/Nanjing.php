<?php

namespace common\sdks\guahao;

use common\libs\CommonFunc;
use common\models\Department;
use common\models\GuahaoHospitalModel;
use common\models\HospitalDepartmentRelation;
use common\models\TmpGuahaoHospitalDepartmentRelation;
use common\sdks\GuahaoInterface;
use common\sdks\snisiya\SnisiyaSdk;
use yii\base\Controller;
use yii\helpers\ArrayHelper;

class Nanjing extends Controller implements GuahaoInterface
{
    const TP_PLATFORM = 2;

    /**
     * 拉取南京医院
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/11/3
     */
    public function actionGetTpHospital($tp_platform = 'nanjing')
    {
        $params = [
            'tp_platform' => self::TP_PLATFORM,
        ];
        $res = SnisiyaSdk::getInstance()->getGuahaoHospital($params);
        if (isset($res['list'])) {
            foreach ($res['list'] as $item) {
                if (isset($item['tp_hospital_code'])) {
                    $hos = GuahaoHospitalModel::find()->where(['tp_platform' => self::TP_PLATFORM, 'tp_hospital_code' => $item['tp_hospital_code']])->one();
                    if ($hos) {
                        if ($item['tp_open_day'] != $hos->tp_open_day || $item['tp_open_time'] != $hos->tp_open_time ||  $item['isSeg'] != $hos->tp_guahao_section) {
                            $hos->tp_open_day  = $item['tp_open_day'];
                            $hos->tp_open_time = $item['tp_open_time'];
                            $hos->tp_guahao_section = $item['isSeg']; // 更新上 是否分时段id
                            $hos->save();
                            //更新缓存es
                            if ($hos->status == 1) {
                                CommonFunc::UpHospitalCache($hos->hospital_id);
                            }
                        }
                        echo $item['hospital_name'] . "-已存在\n\r";
                    } else {
                        $nanjing                    = new GuahaoHospitalModel();
                        $nanjing->city_code         = 0;
                        $nanjing->hospital_name     = $item['hospital_name'];
                        $nanjing->tp_hospital_code  = $item['tp_hospital_code'];
                        $nanjing->hospital_id       = $this->getMiaoId($item['tp_hospital_code']);
                        $nanjing->create_time       = time();
                        $nanjing->status            = 0;
                        $nanjing->tp_platform       = self::TP_PLATFORM;
                        $nanjing->tp_guahao_section = $item['isSeg'];
                        $nanjing->tp_guahao_verify  = '1';
                        $nanjing->tp_open_day       = $item['tp_open_day'];
                        $nanjing->tp_open_time      = $item['tp_open_time'];
                        $nanjing->save();
                        echo '南京 医院 ' . $item['hospital_name'] . "-入库\n\r";
                    }
                }
            }
        }

    }

    /**
     * 根据科室获取医生
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2021-09-18
     * @version v1.0
     * @param   array     $params [description]
     * @return  [type]            [description]
     */
    public function actionGetTpDoctor($params = [])
    {
        //医院 科室获取医生
        $param = [
            'tp_platform' => $params['tp_platform'],
            'tp_department_id' => $params['tp_department_id'],
        ];
        $docList = SnisiyaSdk::getInstance()->getGuahaoDoctor($param);
        $docList = ArrayHelper::getValue($docList, 'list');
        return $docList ?? [];
    }

    public function cut($str)
    {
        $pattern = "/(擅长|专长)(.*?)(。|,|;)/";
        if (!$str) {
            return '';
        }
        preg_match($pattern, $str, $matches);
        if (isset($matches[2])) {
            return $matches[2];
        }
        return '';
    }

    /**
     * 更新医院关系
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/11/3
     */
    public function actionUpdateHospitalId()
    {
        //第三方医院id=》王氏医院id
        $arr = ['32016400' => '32392', '67131953' => '16823', '425802359' => '16879', '32040700' => '27303', '426032421' => '16853', '12100' => '5660', '787104988' => '32393', '466005997' => '27297', '426061142' => '16817', 'L03317' => '27301', '32014000' => '5665', '466002665' => '16807', '466000838' => '5659', '466002809' => '5679', '466002673' => '16810', '32000400' => '5661', '69839091-9' => '32389', '32010100' => '22291', '32010100-0' => '32387', 'E93792052' => '27302', '93799638' => '27308', '32017100' => '16809', '913201040532737748' => '25761', '32010300' => '5664', '32010200' => '16805', '32010500' => '16803', '426080415' => '31200', '426080423' => '27329', '425850238' => '27313', '426032413-0' => '32388', '32010800' => '16812', '426070487' => '27298', '426070495' => '32008', '426061150' => '16865', '426051147' => '5669', '426010101' => '16819', '32010900' => '16806', 'H0012' => '5708', '32011500' => '5703', '32011000' => '5686', '32012000' => '27311', '32010700' => '5666', '425802367' => '16846', '32017000' => '16808', '320042935' => '27299', '771261508' => '5678', 'YA1211276' => '5697', '32000800' => '27291', '32011311' => '25893'];
        foreach ($arr as $key => $val) {
            $hos              = GuahaoHospitalModel::find()->where(['tp_hospital_code' => $key, 'tp_platform' => self::TP_PLATFORM])->one();
            $hos->hospital_id = $val;
            $hos->status      = 1;
            $hos->save();
            echo $key . '---' . $val . "-更新\n\r";
        }
    }

    public function getMiaoId($tp_hos_id)
    {
        $arr = ['32016400' => '32392', '67131953' => '16823', '425802359' => '16879', '32040700' => '27303', '426032421' => '16853', '12100' => '5660', '787104988' => '32393', '466005997' => '27297', '426061142' => '16817', 'L03317' => '27301', '32014000' => '5665', '466002665' => '16807', '466000838' => '5659', '466002809' => '5679', '466002673' => '16810', '32000400' => '5661', '69839091-9' => '32389', '32010100' => '22291', '32010100-0' => '32387', 'E93792052' => '27302', '93799638' => '27308', '32017100' => '16809', '913201040532737748' => '25761', '32010300' => '5664', '32010200' => '16805', '32010500' => '16803', '426080415' => '31200', '426080423' => '27329', '425850238' => '27313', '426032413-0' => '32388', '32010800' => '16812', '426070487' => '27298', '426070495' => '32008', '426061150' => '16865', '426051147' => '5669', '426010101' => '16819', '32010900' => '16806', 'H0012' => '5708', '32011500' => '5703', '32011000' => '5686', '32012000' => '27311', '32010700' => '5666', '425802367' => '16846', '32017000' => '16808', '320042935' => '27299', '771261508' => '5678', 'YA1211276' => '5697', '32000800' => '27291', '32011311' => '25893'];
        if (isset($arr[$tp_hos_id])) {
            return $arr[$tp_hos_id];
        }
        return 0;
    }

    /**
     * 删除南京相关医院科室关系
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-30
     * @version 1.0
     * @param   string     $value [description]
     */
    public function delDepartment()
    {
        $where = [
            'tp_platform' => self::TP_PLATFORM,
        ];
        $query         = GuahaoHospitalModel::find()->select('tp_hospital_code,hospital_id')->where($where)->andWhere(['>', 'hospital_id', 0]);
        $hospital_list = $query->asArray()->all();
        if (!$hospital_list) {
            echo ('结束：' . date('Y-m-d H:i:s', time())) . '医院不存在！' . PHP_EOL;die();
        }
        foreach ($hospital_list as $key => $value) {
            $hospitalModel = HospitalDepartmentRelation::find()->where(['hospital_id' => $value['hospital_id']])->one();
            if ($hospitalModel) {
                HospitalDepartmentRelation::deleteAll(['hospital_id' => $value['hospital_id']]);
                echo ('结束：' . date('Y-m-d H:i:s', time())) . '医院' . $value['hospital_id'] . '删除成功！' . PHP_EOL;
            }
            unset($hospitalModel);
        }
        echo "任务" . date('Y-m-d H:i:s') . "完成！\n";
    }

    /**
     * 更新对应医院新的对应科室关系
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-12
     * @version 1.0
     * @return  [type]     [description]
     */
    public function updateDepartmentRelation()
    {
        $page  = 1;
        $limit = 1000;
        $where = [
            'tp_platform' => self::TP_PLATFORM,
            // 'hospital_id' => 0,
        ];
        $query = TmpGuahaoHospitalDepartmentRelation::find()->where($where);
        do {
            // $tpage = $maxPage - $page;
            // $offset     = max(0, ($tpage - 1)) * $limit;##倒序
            $offset     = max(0, ($page - 1)) * $limit; ##正序
            $keshi_list = $query->offset($offset)->limit($limit)->asArray()->all();
            if (!$keshi_list) {
                echo ('结束：' . date('Y-m-d H:i:s', time())) . '科室没有了！' . PHP_EOL;
                break;
            }
            foreach ($keshi_list as $key => &$value) {
                $value['tp_frist_department_name']  = str_replace(" ", '', trim($value['tp_frist_department_name']));
                $value['tp_second_department_name'] = str_replace(" ", '', trim($value['tp_second_department_name']));
                $relationInfo                       = HospitalDepartmentRelation::find()->where(['hospital_id' => $value['hospital_id'], 'tp_platform' => self::TP_PLATFORM, 'frist_department_name' => $value['tp_frist_department_name'], 'second_department_name' => $value['tp_second_department_name']])->asArray()->one();
                if ($relationInfo) {
                    echo $value['id'] . (date('Y-m-d H:i:s', time())) . '医院' . $value['hospital_id'] . '已存在！' . PHP_EOL;
                    continue;
                }
                $relationModel = new HospitalDepartmentRelation();
                $fkeshiInfo    = Department::find()->where(['department_name' => $value['tp_frist_department_name']])->one();
                if ($fkeshiInfo) {
                    $relationModel->frist_department_id = $fkeshiInfo->department_id;
                } else {
                    $fkeshiModek                  = new Department();
                    $fkeshiModek->department_name = $value['tp_frist_department_name'];
                    $fkeshiModek->parent_id       = 0;
                    $fkeshiModek->status          = 1;
                    $fkeshiModek->is_common       = 0;
                    $fkeshiModek->create_time     = time();
                    $res                          = $fkeshiModek->save();
                    if ($res) {
                        $department_id                      = $fkeshiModek->attributes['department_id'];
                        $relationModel->frist_department_id = $department_id;
                    } else {
                        $relationModel->frist_department_id = 0;
                    }
                }
                $skeshiInfo = Department::find()->where(['department_name' => $value['tp_second_department_name']])->one();
                if ($skeshiInfo) {
                    $relationModel->second_department_id = $skeshiInfo->department_id;
                } else {
                    $skeshiModek                  = new Department();
                    $skeshiModek->department_name = $value['tp_second_department_name'];
                    $skeshiModek->parent_id       = $relationModel->frist_department_id;
                    $skeshiModek->status          = 1;
                    $skeshiModek->is_common       = 0;
                    $skeshiModek->create_time     = time();
                    $res                          = $skeshiModek->save();
                    if ($res) {
                        $department_id                       = $skeshiModek->attributes['department_id'];
                        $relationModel->second_department_id = $department_id;
                    } else {
                        $relationModel->second_department_id = 0;
                    }
                }
                $relationModel->tp_department_id          = $value['tp_department_id'];
                $relationModel->frist_department_name     = $value['tp_frist_department_name'];
                $relationModel->second_department_name    = $value['tp_second_department_name'];
                $relationModel->tp_platform               = self::TP_PLATFORM;
                $relationModel->tp_hospital_code          = $value['tp_hospital_id'];
                $relationModel->hospital_id               = $value['hospital_id'];
                $relationModel->miao_frist_department_id  = $value['miao_frist_department_id'];
                $relationModel->miao_second_department_id = $value['miao_second_department_id'];
                $relationModel->doctors_num               = (int) $value['doctors_num'];
                $relationModel->create_time               = time();
                $res                                      = $relationModel->save();
                unset($relationModel);
                echo '第' . $value['id'] . '--' . $value['tp_second_department_name'] . '  科室保存成功！' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                unset($value);
            }
            $num = count($keshi_list);
            unset($keshi_list);
            $page++;
        } while ($num > 0);

        echo "任务" . date('Y-m-d H:i:s') . "完成！\n";
    }

    /**
     * 拉取南京科室列表
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-11-02
     * @version 1.0
     * @param   string     $value [description]
     */
    public function pullDepartment($tp_hospital_code = '')
    {
        $where = [];
        if ($tp_hospital_code) {
            $where['tp_hospital_code'] = $tp_hospital_code;
        }
        $where['tp_platform']   = self::TP_PLATFORM;
        $where['status']        = 1;
        $query         = GuahaoHospitalModel::find()->select('tp_hospital_code,hospital_id,hospital_name')->where($where);
        $hospital_list = $query->asArray()->all();
        if (!$hospital_list) {
            echo ('结束：' . date('Y-m-d H:i:s', time())) . '数据为空或医院未关联！' . strval($tp_hospital_code) . PHP_EOL;die();
        }
        $snisiyaSdk = new SnisiyaSdk();
        foreach ($hospital_list as $key => $value) {
            echo $value['tp_hospital_code'] . '  start' . date('Y-m-d H:i:s', time()) . PHP_EOL;
            $res = $snisiyaSdk->getGuahaoDepartment(['tp_platform' => self::TP_PLATFORM, 'tp_hospital_code' => $value['tp_hospital_code']]);
            if (!$res) {
                echo $value['tp_hospital_code'] . '  接口获取失败！' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                continue;
            }
            $keshi_list = $res['list'] ?? [];
            if (!$keshi_list) {
                echo $value['tp_hospital_code'] . '  科室不存在！' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                continue;
            }
            foreach ($keshi_list as $hkey => $hkeshiItem) {

                $relationParams['third_skname']     = $hkeshiItem['department_name'];
                $relationParams['tp_department_id'] = $hkeshiItem['tp_department_id'];
                $relationParams['department_name']  = $hkeshiItem['department_name'];
                $relationParams['tp_platform']      = self::TP_PLATFORM;
                $relationParams['tp_hospital_code'] = $value['tp_hospital_code'];
                $relationParams['hospital_name']    = $value['hospital_name'];
                $relationParams['hospital_id']      = $value['hospital_id'];
                $relationParams['create_time']      = time();
                // (liuyingwei 科室自动导入 待调用 函数方法 2021-09-14 )
                $result = HospitalDepartmentRelation::autoImportDepartment($relationParams);
                //医院科室缓存
                // HospitalDepartmentRelation::hospitalDepartment($value['hospital_id'], true);
                if($result['code'] == 200){
                    echo $relationParams['department_name'] . $result['msg'] . date('Y-m-d H:i:s', time()) . PHP_EOL;
                }else{
                    echo $relationParams['department_name'] . $result['msg'] . date('Y-m-d H:i:s', time()) . PHP_EOL;
                }
                unset($relationParams);
            }
            unset($res);
            unset($keshi_list);
            echo $value['tp_hospital_code'] . '  end' . date('Y-m-d H:i:s', time()) . PHP_EOL;
        }
        echo "任务" . date('Y-m-d H:i:s') . "完成！\n";

    }
}
