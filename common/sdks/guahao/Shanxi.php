<?php

namespace common\sdks\guahao;

use common\models\GuahaoHospitalModel;
use common\models\HospitalDepartmentRelation;
use common\sdks\snisiya\SnisiyaSdk;
use yii\helpers\ArrayHelper;

class Shanxi extends \yii\base\Controller implements \common\sdks\GuahaoInterface
{

    const TP_PLATFORM = 8;
    public $pagesize  = 500;

    /**
     * 拉取山西医院
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2021-09-08
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionGetTpHospital($tp_hospital_code = '')
    {
        $params = [
            'tp_platform' => self::TP_PLATFORM,
        ];
        $res            = SnisiyaSdk::getInstance()->getGuahaoHospital($params);
        if (isset($res['list']) && $res['list']) {
            foreach ($res['list'] as $key => $item) {
                if (isset($item['tp_hospital_code'])) {
                    if (!empty($item['branches'])) {//分医院不为空
                        foreach ($item['branches'] as $val) {
                            $val['hospital_name'] = $item['hospital_name'] . ' ' . $val['hospital_name'];
                            $this->saveHospital($val);
                        }
                    } else {
                        $this->saveHospital($item);
                    }
                } else {
                    echo "key：" . $key . "山西医院-tp_hospital_code为空\n\r";
                }
            }
        }
        echo 'hospital end' . PHP_EOL;
        sleep(2);
    }

    /**
     * 验证医院数据写入数据库
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2021-09-08
     * @version v1.0
     * @param array  $item 返回医院数据数组信息
     * @return  [type]     [description]
     */
    public function saveHospital($item)
    {
        $hos = GuahaoHospitalModel::find()->where(['tp_platform' => self::TP_PLATFORM, 'tp_hospital_code' => $item['tp_hospital_code']])->one();
        if ($hos) {
            list($status, $remarks, $verify) = $this->getTpStatus($item['tp_check']);
            $hos->remarks = $remarks;
            $hos->tp_guahao_verify = $verify;
            $hos->hospital_name = $item['hospital_name'];
            $hos->save();
            echo "医院ID:{$item['tp_hospital_code']}--医院名称:" . $item['hospital_name'] . "-已存在\n\r";
        } else {
            $nanjing = new GuahaoHospitalModel();
            $nanjing->city_code = $item['cityCode'];
            $nanjing->hospital_name = $item['hospital_name'];
            $nanjing->tp_hospital_code = $item['tp_hospital_code'];
            $nanjing->create_time = time();
            list($status, $remarks, $verify) = $this->getTpStatus($item['tp_check']);
            $nanjing->status = $status;
            $nanjing->remarks = $remarks;
            $nanjing->tp_platform = self::TP_PLATFORM;
            $nanjing->tp_guahao_section = 0;
            $nanjing->tp_guahao_description = '';
            $nanjing->tp_guahao_verify = $verify;
            $nanjing->tp_allowed_cancel_day = 1;
            $nanjing->tp_allowed_cancel_time = '12:00';
            $nanjing->tp_open_day = $item['tp_open_day'] > 0 ? $item['tp_open_day'] : 0;
            $nanjing->tp_open_time = '';
            $nanjing->save();
            echo "医院ID:{$item['tp_hospital_code']}--医院名称:" . $item['hospital_name'] . "-入库\n\r";
        }
    }

    /**
     * 检测医院是否使用就诊卡或者是否使用在线支付
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2021-09-08
     * @version v1.0
     * @param   int $pay_method  支付方式 0、线下，1、在线支付
     * @param   int $is_need_treat_card  是否需要就诊卡 0:不需要；1：需要
     * @return  []
     */
    public function getTpStatus ($tp_check = [])
    {
        $status = 0;
        $remarks = "";
        $verify = "";
        $need_pay_desc = ($tp_check['need_pay'] == 1) ? " 使用在线支付 " : "";
        $need_card_desc = ($tp_check['need_card'] == 1) ? " 使用就诊卡 " : "";

        if (($tp_check['need_pay'] == 1) || ($tp_check['need_card'] == 1)) {
            if ($tp_check['need_card'] == 1) $verify .= "2";
            $status = 2;
            $remarks .= $need_card_desc . $need_pay_desc;
        }
        return [$status, $remarks, $verify];
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
                            $relationParams['tp_hospital_code'] = $v['tp_hospital_code'];
                            $relationParams['tp_platform']      = self::TP_PLATFORM;
                            $relationParams['hospital_name']    = $v['hospital_name'];
                            $relationParams['tp_department_id'] = $ks['tp_department_id'];
                            $relationParams['hospital_id']      = $v['hospital_id'];
                            $relationParams['department_name']  = $ks['department_name'];
                            $relationParams['create_time']      = time();
                            // (liuyingwei 科室自动导入 待调用 函数方法 2021-09-14 )
                            $result = HospitalDepartmentRelation::autoImportDepartment($relationParams);
                            if($result['code'] == 200){
                                echo '[科室]' . $v['hospital_name'] . '--' . $ks['tp_department_id'] . '--' . $ks['department_name'] . '--' . date("Y-m-d H:i:s") . $result['msg'].PHP_EOL;
                            }else{
                                echo '[科室]' . $v['hospital_name'] . '--' . $ks['tp_department_id'] . '--' . $ks['department_name'] . '--' . date("Y-m-d H:i:s") . $result['msg'].PHP_EOL;
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
    public function actionGetTpDoctor($params = [])
    {
        //医院 科室获取医生
        $param = [
            'tp_platform' => $params['tp_platform'],
            'tp_hospital_code' => $params['tp_hospital_code'],
            'tp_department_id' => $params['tp_department_id'],
        ];
        $docList = SnisiyaSdk::getInstance()->getGuahaoDoctor($param);
        $docList = ArrayHelper::getValue($docList, 'list');
        return $docList ?? [];
    }
}
