<?php

namespace common\sdks\guahao;

use common\libs\CommonFunc;
use common\models\GuahaoHospitalModel;
use common\models\HospitalDepartmentRelation;
use common\sdks\snisiya\SnisiyaSdk;
use yii\helpers\ArrayHelper;

class Shaanxi extends \yii\base\Controller implements \common\sdks\GuahaoInterface
{

    const TP_PLATFORM = 7;
    public $pagesize  = 500;

    /**
     * 拉取陕西医院
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-01
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionGetTpHospital($tp_hospital_code = '')
    {

        $page   = 1;
        $params = [
            'tp_platform' => self::TP_PLATFORM,
            'pagesize'    => 100,
        ];
        if ($tp_hospital_code) {
            $params['tp_hospital_code'] = $tp_hospital_code;
        }
        do {
            $params['page'] = $page;
            $res            = SnisiyaSdk::getInstance()->getGuahaoHospital($params);
            if (isset($res['list']) && $res['list']) {
                foreach ($res['list'] as $key => $item) {
                    if (isset($item['tp_hospital_code'])) {
                        $hospital_info = SnisiyaSdk::getInstance()->getHospitalByid(['tp_platform' => self::TP_PLATFORM, 'tp_hospital_code' => $item['tp_hospital_code'], 'corp_id' => $item['corp_id']]);
                        $hos           = GuahaoHospitalModel::find()->where(['tp_platform' => self::TP_PLATFORM, 'tp_hospital_code' => $item['tp_hospital_code']])->one();
                        if ($hos) {
                            $hos->corp_id                = $item['corp_id'] ?? '';
                            $hos->tp_allowed_cancel_day  = $hospital_info['tp_allowed_cancel_day'] ?? 1;
                            $hos->tp_allowed_cancel_time = $hospital_info['tp_allowed_cancel_time'] ?? '12:00';
                            $hos->save();
                            if ((isset($hospital_info['tp_allowed_cancel_day']) && ($hospital_info['tp_allowed_cancel_day'] != $hos->tp_allowed_cancel_day)) || (isset($hospital_info['tp_allowed_cancel_time']) && ($hospital_info['tp_allowed_cancel_time'] != $hos->tp_allowed_cancel_time))) {
                                $hos->tp_allowed_cancel_day  = $item['tp_allowed_cancel_day'];
                                $hos->tp_allowed_cancel_time = $item['tp_allowed_cancel_time'];
                                $hos->save();
                                //更新缓存es
                                if ($hos->status == 1 && $hos->hospital_id > 0) {
                                    CommonFunc::UpHospitalCache($hos->hospital_id);
                                }
                            }
                            echo $item['hospital_name'] . "-已存在\n\r";
                        } else {
                            $nanjing                         = new GuahaoHospitalModel();
                            $nanjing->city_code              = 0;
                            $nanjing->hospital_name          = $item['hospital_name'];
                            $nanjing->tp_hospital_code       = $item['tp_hospital_code'];
                            $nanjing->create_time            = time();
                            list($status, $remarks) = $this->getTpStatus($hospital_info['tp_check']);
                            $nanjing->status  = $status;
                            $nanjing->remarks = $remarks;
                            $nanjing->corp_id                = $item['corp_id'] ?? '';
                            $nanjing->tp_platform            = self::TP_PLATFORM;
                            $nanjing->tp_guahao_section      = $item['isSeg'] ?? 0;
                            $nanjing->tp_guahao_description  = '';
                            $nanjing->tp_guahao_verify       = '';
                            $nanjing->tp_allowed_cancel_day  = $hospital_info['tp_allowed_cancel_day'] ?? 1;
                            $nanjing->tp_allowed_cancel_time = $hospital_info['tp_allowed_cancel_time'] ?? '12:00';
                            $nanjing->tp_open_day            = $item['tp_open_day'] ?? 0;
                            $nanjing->tp_open_time           = $item['tp_open_time'] ?? '';
                            $nanjing->save();
                            echo "key：" . $key . ' 陕西 医院 ' . $item['hospital_name'] . "-入库\n\r";
                        }
                    } else {
                        echo "key：" . $key . ' 陕西 医院 ' . "-tp_hospital_code为空\n\r";
                    }
                }
            }
            $page++;
        } while (isset($res['list']) && $res['list']);
        echo 'hospital end' . PHP_EOL;
    }

    /**
     * 检测医院是否使用就诊卡或者是否使用在线支付或者是否使用在线建档
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2021-068-23
     * @version v1.0
     * @param   array     $tp_check
     * @return  []
     */
    public function getTpStatus ($tp_check = [])
    {
        $status = 0;
        $remarks = "";
        $need_card  = (!empty($tp_check) && isset($tp_check['need_card']))  ? $tp_check['need_card']  : 0;//是否使用就诊卡0否1是
        $need_pay   = (!empty($tp_check) && isset($tp_check['need_pay']))   ? $tp_check['need_pay']   : 0;//是否在线支付0否1是
        $need_build = (!empty($tp_check) && isset($tp_check['need_build'])) ? $tp_check['need_build'] : 0;//是否在线建档0否1是
        $need_card_desc  = ($need_card == 1)  ? " 使用就诊卡 "  : "";
        $need_pay_desc   = ($need_pay == 1)   ? " 使用在线支付 " : "";
        $need_build_desc = ($need_build == 1) ? " 使用在线建档 " : "";

        if (($need_card  == 1) || ($need_pay   == 1) || ($need_build == 1)) {
            $status  = 2;
            $remarks = $need_card_desc . $need_pay_desc . $need_build_desc;
        }
        return [$status, $remarks];
    }

    /**
     * 根据医院获取科室
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-01
     * @version v1.0
     * @param   string     $tp_hospital_code [description]
     * @return  [type]                       [description]
     */
    public function pullDepartment($tp_hospital_code = '')
    {
        $page     = 0;
        $pagesize = $this->pagesize;
        do {
            $offset = $page * $pagesize;
            $hos_query = GuahaoHospitalModel::find()->where(['=', 'tp_platform', self::TP_PLATFORM]);
            $hos_query->andWhere(['=', 'status', 1]);
            if ($tp_hospital_code) {
                $hos_query->andWhere(['=', 'tp_hospital_code', $tp_hospital_code]);
            }
            $hospList = $hos_query->offset($offset)->limit($pagesize)->asArray()->all();
            if ($hospList) {
                foreach ($hospList as $v) {
                    $params = [
                        'tp_platform'      => self::TP_PLATFORM,
                        'tp_hospital_code' => ArrayHelper::getValue($v, 'tp_hospital_code'),
                        'pagesize'         => 1000,
                        // 'tp_department_id' => -1,##获取所有一级科室
                    ];
                    $department = SnisiyaSdk::getInstance()->getGuahaoDepartment($params);

                    if ($department) {
                        $department = ArrayHelper::getValue($department, 'list');
                        foreach ($department as $ks) {
                            //科室id或科室名称为空的时候不拉取
                            if (empty($ks['tp_department_id']) || empty($ks['department_name'])) {
                                echo '[科室]' . $v['hospital_name'] . '--' . $ks['tp_department_id'] . '--' . $ks['department_name'] . '--' . date("Y-m-d H:i:s") . '科室id或科室名称不合法'.PHP_EOL;
                                continue;
                            }

                            if (ArrayHelper::getValue($ks, 'hasChild') == 1) {
                                echo "科室{$ks['tp_department_id']}hasChild=1，有子科室\n";
                                continue;
                            }
                            $relationParams['tp_hospital_code'] = $v['tp_hospital_code'];
                            $relationParams['tp_platform']      = self::TP_PLATFORM;
                            $relationParams['hospital_name']    = $v['hospital_name'];
                            $relationParams['tp_department_id'] = $ks['tp_department_id'];
                            $relationParams['hospital_id']      = $v['hospital_id'];
                            $relationParams['department_name']  = $ks['department_name'];
                            $relationParams['create_time']      = time();
                            //放号时间
                            // $relationParams['tp_open_day']  = $tp_open_day;
                            // $relationParams['tp_open_time'] = $tp_open_time;

                            // (liuyingwei 科室自动导入 待调用 函数方法 2021-09-14 )
                            $result = HospitalDepartmentRelation::autoImportDepartment($relationParams);

                            if($result['code'] == 200){
                                echo $relationParams['department_name'] . $result['msg'] . date('Y-m-d H:i:s', time()) . PHP_EOL;
                            }else{
                                echo $relationParams['department_name'] . $result['msg'] . date('Y-m-d H:i:s', time()) . PHP_EOL;
                            }
                            unset($relationParams);


                        }
                    } else {
                        echo ("医院:{$v['hospital_name']} 医院id:{$v['tp_hospital_code']}  " . date('Y-m-d H:i:s', time())) . '暂无数据' . PHP_EOL;
                    }
                    unset($department);

                }
            } else {
                echo '没有数据了或医院未关联' . strval($tp_hospital_code).PHP_EOL;
            }

            $page++;
        } while (count($hospList) > 0);

        echo 'keshi end' . PHP_EOL;
        sleep(2);
        //$this->actionGetTpDoctor($tp_hospital_code);
    }

    /**
     * 根据科室获取医生
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2021-09-18
     * @version v1.0
     * @param   array     $params [description]
     * @return  [type]            [description]
     */
    //获取医生数据
    public function actionGetTpDoctor($params = [])
    {
        $param = [
            'tp_platform' => $params['tp_platform'],
            'tp_hospital_code' => $params['tp_hospital_code'],
            'tp_department_id' => $params['tp_department_id'],
            'page' => $params['page'],
            'pagesize' => $params['pagesize'] ?? 1000,
        ];
        $docList = SnisiyaSdk::getInstance()->getGuahaoDoctor($param);
        $docList = ArrayHelper::getValue($docList, 'list');
        if ($docList) {
            foreach ($docList as &$doc) {
                $doc_item = SnisiyaSdk::getInstance()->getDoctorByid([
                    'tp_platform'      => $params['tp_platform'],
                    'tp_hospital_code' => $params['tp_hospital_code'],
                    'tp_department_id' => $params['tp_department_id'],
                    'tp_doctor_id'     => $doc['tp_doctor_id'],
                ]);
                $doc['profile'] = isset($doc_item['profile']) ? $doc_item['profile'] : '';
            }
        }
        return $docList ?? [];
    }
}
