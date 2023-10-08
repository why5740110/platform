<?php

namespace common\sdks\guahao;

use common\models\Department;
use common\models\GuahaoHospitalModel;
use common\models\HospitalDepartmentRelation;
use common\models\TmpGuahaoHospitalDepartmentRelation;
use common\sdks\GuahaoInterface;
use common\sdks\snisiya\SnisiyaSdk;
use yii\helpers\ArrayHelper;

class Henan extends \yii\base\Controller implements GuahaoInterface
{
    const TP_PLATFORM = 1;

    public static $city_code = ['410100', '410200', '410300', '410400', '410500', '410600', '410700', '410800', '410900', '411000', '411100', '411200', '411300', '411400', '411500', '411600', '411700', '411800'];

    public function actionGetTpHospital($tp_platform = 'henan')
    {
        $params = [
            'tp_platform' => self::TP_PLATFORM,
        ];
        $cityCode = self::$city_code;
        foreach ($cityCode as $v) {
            $params['citycode'] = $v;
            $res                = SnisiyaSdk::getInstance()->getGuahaoHospital($params);
            if (isset($res['list'])) {
                foreach ($res['list'] as $item) {
                    if (isset($item['tp_hospital_code'])) {
                        $hos = GuahaoHospitalModel::find()->where(['tp_hospital_code' => $item['tp_hospital_code']])->one();
                        if ($hos) {
                            echo $item['hospital_name'] . "-已存在\n\r";
                        } else {
                            $henan                    = new GuahaoHospitalModel();
                            $henan->city_code         = 0;
                            $henan->hospital_name     = $item['hospital_name'];
                            $henan->tp_hospital_code  = $item['tp_hospital_code'];
                            $henan->hospital_id       = $this->getMiaoId($item['tp_hospital_code']);
                            $henan->create_time       = time();
                            $henan->status            = 0;
                            $henan->tp_platform       = self::TP_PLATFORM;
                            $henan->tp_guahao_section = isset($item['isSeg']) ? (in_array($item['isSeg'], [null, NULL]) ? 0 : $item['isSeg']) : 0;
                            $henan->tp_guahao_verify  = '1';
                            $henan->save();
                            echo $item['hospital_name'] . "-入库\n\r";
                        }
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
        $param = [
            'tp_platform' => $params['tp_platform'],
            'tp_department_id' => $params['tp_department_id'],
        ];
        $docList = SnisiyaSdk::getInstance()->getGuahaoDoctor($param);
        $docList = ArrayHelper::getValue($docList, 'list');
        return $docList ?? [];
    }

    public function getMiaoId($tp_hos_id)
    {
        $arr = [2020925001 => 9295, 2020925002 => 25061, 2020925003 => 9303, 2020925004 => 9300, 2020925005 => 9302, 2020925006 => 9301, 2020925007 => 9298, 2020925008 => 9404, 2020925009 => 9408, 2020925010 => 9312, 2020925011 => 9383, 2020925012 => 32286, 2020925013 => 22421, 2020925014 => 22713, 2020925015 => 9296, 2020925016 => 9396, 2020925017 => 9311, 2020925018 => 31530, 2020925019 => 9397, 2020925020 => 23800, 2020925021 => 28577, 2020925022 => 32294, 2020925023 => 32295, 2020925024 => 30794, 2020925025 => 31691, 2020925026 => 9401, 2020925027 => 32296, 2020925028 => 32297, 2020925029 => 32298, 2020925030 => 28574, 2020925031 => 32298, 2020925032 => 32300, 2020925033 => 32301, 2020925034 => 28575, 2020925035 => 32302, 2020925036 => 32303, 2020925037 => 32304, 2020925038 => 32305, 2020925039 => 9375, 2020925040 => 32306, 2020925041 => 32307, 2020925042 => 28582, 2020925043 => 9345, 2020925044 => 9309, 2020925045 => 9392, 2020925046 => 32308, 2020925047 => 32309, 2020925048 => 32310, 2020925049 => 9308, 2020925050 => 9457, 2020925051 => 24729, 2020925052 => 30414, 2020925053 => 9465, 2020925054 => 9460, 2020925055 => 25198, 2020925056 => 9518, 2020925057 => 9509, 2020925058 => 9515, 2020925059 => 9511, 2020925060 => 9519, 2020925061 => 32311, 2020925062 => 9517, 2020925063 => 28606, 2020925064 => 9581, 2020925065 => 32312, 2020925066 => 25784, 2020925067 => 22176, 2020925068 => 32313, 2020925069 => 28642, 2020925070 => 28650, 2020925071 => 9769, 2020925072 => 9770, 2020925073 => 25805, 2020925074 => 9778, 2020925075 => 32314, 2020925076 => 9798, 2020925077 => 25377, 2020925078 => 18857, 2020925079 => 9772, 2020925080 => 9786, 2020925081 => 28788, 2020925082 => 9699, 2020925083 => 22408, 2020925084 => 9719, 2020925085 => 9718, 2020925086 => 9717, 2020925087 => 9730, 2020925088 => 9722, 2020925089 => 32315, 2020925090 => 9631, 2020925091 => 9632, 2020925092 => 32316, 2020925093 => 9638, 2020925094 => 9636, 2020925095 => 32317, 2020925096 => 9813, 2020925097 => 9387, 2020925098 => 9814, 2020925099 => 9815, 2020925100 => 9825, 2020925101 => 28733, 2020925102 => 22809, 2020925103 => 9884, 2020925104 => 9874, 2020925105 => 28752, 2020925106 => 9887, 2020925107 => 32221, 2020925108 => 9888, 2020925109 => 9889, 2020925110 => 9918, 2020925111 => 9917, 2020925112 => 9919, 2020925113 => 9947, 2020925114 => 9940, 2020925115 => 9957, 2020925116 => 9964, 2020925117 => 9966, 2020925118 => 28617, 2020925119 => 32318, 2020925120 => 9979, 2020925121 => 23543, 2020925122 => 9967, 2020925123 => 32319, 2020925124 => 32320, 2020925125 => 9987, 2020925126 => 28619, 2020925127 => 9963, 2020925128 => 10037, 2020925129 => 10038, 2020925130 => 32321, 2020925131 => 10048, 2020925132 => 10078, 2020925133 => 28712, 2020925134 => 10079, 2020925135 => 10112, 2020925136 => 10125, 2020925137 => 10105, 2020925138 => 10119, 2020925139 => 10099, 2020925140 => 32322, 2020925141 => 10127, 2020925142 => 10159, 2020925143 => 28765, 2020925144 => 10176, 2020925145 => 23214, 2020925146 => 32323, 2020925147 => 30695, 2020925148 => 28746, 2020925149 => 10213, 2020925150 => 22982, 2020925151 => 10216];
        if (isset($arr[$tp_hos_id])) {
            return $arr[$tp_hos_id];
        }
        return 0;
    }

    /**
     * 删除河南管理医院科室关系
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-30
     * @version 1.0
     * @param   string     $value [description]
     */
    public function delDepartment()
    {
        $where = [
            'status'      => 1,
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
     * 更新河南对应医院新的对应科室关系
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
        ];
        $query = TmpGuahaoHospitalDepartmentRelation::find()->where($where);
        do {
            $offset     = max(0, ($page - 1)) * $limit;
            $keshi_list = $query->offset($offset)->limit($limit)->asArray()->all();
            if (!$keshi_list) {
                echo ('结束：' . date('Y-m-d H:i:s', time())) . '科室没有了！' . PHP_EOL;
                break;
            }
            foreach ($keshi_list as $key => &$value) {
                $value['tp_frist_department_name']  = str_replace(" ", '', trim($value['tp_frist_department_name']));
                $value['tp_second_department_name'] = str_replace(" ", '', trim($value['tp_second_department_name']));
                $relationInfo                       = HospitalDepartmentRelation::find()->where(['hospital_id' => $value['hospital_id'], 'tp_platform' => $value['tp_platform'], 'frist_department_name' => $value['tp_frist_department_name'], 'second_department_name' => $value['tp_second_department_name']])->asArray()->one();
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

    public function actionUpdateHospitalId()
    {
        $arr = [2020925001 => 9295, 2020925002 => 25061, 2020925003 => 9303, 2020925004 => 9300, 2020925005 => 9302, 2020925006 => 9301, 2020925007 => 9298, 2020925008 => 9404, 2020925009 => 9408, 2020925010 => 9312, 2020925011 => 9383, 2020925012 => 32286, 2020925013 => 22421, 2020925014 => 22713, 2020925015 => 9296, 2020925016 => 9396, 2020925017 => 9311, 2020925018 => 31530, 2020925019 => 9397, 2020925020 => 23800, 2020925021 => 28577, 2020925022 => 32294, 2020925023 => 32295, 2020925024 => 30794, 2020925025 => 31691, 2020925026 => 9401, 2020925027 => 32296, 2020925028 => 32297, 2020925029 => 32298, 2020925030 => 28574, 2020925031 => 32298, 2020925032 => 32300, 2020925033 => 32301, 2020925034 => 28575, 2020925035 => 32302, 2020925036 => 32303, 2020925037 => 32304, 2020925038 => 32305, 2020925039 => 9375, 2020925040 => 32306, 2020925041 => 32307, 2020925042 => 28582, 2020925043 => 9345, 2020925044 => 9309, 2020925045 => 9392, 2020925046 => 32308, 2020925047 => 32309, 2020925048 => 32310, 2020925049 => 9308, 2020925050 => 9457, 2020925051 => 24729, 2020925052 => 30414, 2020925053 => 9465, 2020925054 => 9460, 2020925055 => 25198, 2020925056 => 9518, 2020925057 => 9509, 2020925058 => 9515, 2020925059 => 9511, 2020925060 => 9519, 2020925061 => 32311, 2020925062 => 9517, 2020925063 => 28606, 2020925064 => 9581, 2020925065 => 32312, 2020925066 => 25784, 2020925067 => 22176, 2020925068 => 32313, 2020925069 => 28642, 2020925070 => 28650, 2020925071 => 9769, 2020925072 => 9770, 2020925073 => 25805, 2020925074 => 9778, 2020925075 => 32314, 2020925076 => 9798, 2020925077 => 25377, 2020925078 => 18857, 2020925079 => 9772, 2020925080 => 9786, 2020925081 => 28788, 2020925082 => 9699, 2020925083 => 22408, 2020925084 => 9719, 2020925085 => 9718, 2020925086 => 9717, 2020925087 => 9730, 2020925088 => 9722, 2020925089 => 32315, 2020925090 => 9631, 2020925091 => 9632, 2020925092 => 32316, 2020925093 => 9638, 2020925094 => 9636, 2020925095 => 32317, 2020925096 => 9813, 2020925097 => 9387, 2020925098 => 9814, 2020925099 => 9815, 2020925100 => 9825, 2020925101 => 28733, 2020925102 => 22809, 2020925103 => 9884, 2020925104 => 9874, 2020925105 => 28752, 2020925106 => 9887, 2020925107 => 32221, 2020925108 => 9888, 2020925109 => 9889, 2020925110 => 9918, 2020925111 => 9917, 2020925112 => 9919, 2020925113 => 9947, 2020925114 => 9940, 2020925115 => 9957, 2020925116 => 9964, 2020925117 => 9966, 2020925118 => 28617, 2020925119 => 32318, 2020925120 => 9979, 2020925121 => 23543, 2020925122 => 9967, 2020925123 => 32319, 2020925124 => 32320, 2020925125 => 9987, 2020925126 => 28619, 2020925127 => 9963, 2020925128 => 10037, 2020925129 => 10038, 2020925130 => 32321, 2020925131 => 10048, 2020925132 => 10078, 2020925133 => 28712, 2020925134 => 10079, 2020925135 => 10112, 2020925136 => 10125, 2020925137 => 10105, 2020925138 => 10119, 2020925139 => 10099, 2020925140 => 32322, 2020925141 => 10127, 2020925142 => 10159, 2020925143 => 28765, 2020925144 => 10176, 2020925145 => 23214, 2020925146 => 32323, 2020925147 => 30695, 2020925148 => 28746, 2020925149 => 10213, 2020925150 => 22982];
        foreach ($arr as $key => $val) {
            $hos              = GuahaoHospitalModel::find()->where(['tp_hospital_code' => $key])->one();
            $hos->hospital_id = $val;
            $hos->save();
            echo $key . '---' . $val . "-更新\n\r";
        }
    }

    /**
     * 获取河南科室列表
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
        $where['tp_platform'] = self::TP_PLATFORM;
        $where['status']      = 1;
        // $query                = GuahaoHospitalModel::find()->select('tp_hospital_code,hospital_id,hospital_name')->where($where)->andWhere(['>', 'hospital_id', 0]);

        $query         = GuahaoHospitalModel::find()->where($where);
        $hospital_list = $query->asArray()->all();
        if (!$hospital_list) {
            echo ('结束：' . date('Y-m-d H:i:s', time())) . '医院不存在或者未关联！' . PHP_EOL;die();
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

                $relationParams['third_fkname']     = $hkeshiItem['department_first_name'];
                $relationParams['third_skname']     = $hkeshiItem['department_name'];
                $relationParams['tp_department_id'] = $hkeshiItem['tp_department_id'];
                $relationParams['department_name']  = $hkeshiItem['department_name'];
                $relationParams['hospital_id']      = $value['hospital_id'];
                $relationParams['tp_platform']      = self::TP_PLATFORM;
                $relationParams['tp_hospital_code'] = $value['tp_hospital_code'];
                $relationParams['hospital_name']    = $value['hospital_name'];
                $relationParams['create_time']      = time();
                // (liuyingwei 科室自动导入 待调用 函数方法 2021-09-14 )
                $result = HospitalDepartmentRelation::autoImportDepartment($relationParams);
                if($result['code'] == 200){
                    echo $hkeshiItem['department_name'] . $result['msg'] . date('Y-m-d H:i:s', time()) . PHP_EOL;
                }else{
                    echo $hkeshiItem['department_name'] . $result['msg'] . date('Y-m-d H:i:s', time()) . PHP_EOL;
                }
                //医院科室缓存
                //HospitalDepartmentRelation::hospitalDepartment($value['hospital_id'], true);
                unset($relationParams);
            }
            unset($res);
            unset($keshi_list);
            echo $value['tp_hospital_code'] . '  end' . date('Y-m-d H:i:s', time()) . PHP_EOL;
        }
        echo "任务" . date('Y-m-d H:i:s') . "完成！\n";

    }

    public function getHos($id)
    {

    }

}
