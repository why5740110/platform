<?php

namespace common\sdks\guahao;

use common\libs\GuahaoCallback;
use common\models\GuahaoHospitalModel;
use common\models\GuahaoOrderModel;
use common\models\HospitalDepartmentRelation;
use common\sdks\snisiya\SnisiyaSdk;
use yii\helpers\ArrayHelper;

class Tianjin extends \yii\base\Controller implements \common\sdks\GuahaoInterface
{

    const  TP_PLATFORM = 10;
    public $tp_platform = 10;
    public $pagesize  = 500;

    /**
     * 拉取福建医院
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2021-10-11
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionGetTpHospital($tp_hospital_code = '')
    {
        $params = [
            'tp_platform' => self::TP_PLATFORM
        ];
        if ($tp_hospital_code) {
            $params['tp_hospital_code'] = $tp_hospital_code;
        }

        $res = SnisiyaSdk::getInstance()->getGuahaoHospital($params);
        if (isset($res['list']) && $res['list']) {
            foreach ($res['list'] as $key => $item) {
                if (isset($item['tp_hospital_code'])) {
                    $hos = GuahaoHospitalModel::find()->where(['tp_platform' => self::TP_PLATFORM, 'tp_hospital_code' => $item['tp_hospital_code']])->one();
                    if ($hos) {
                        echo $item['hospital_name'] . "-已存在\n\r";
                    } else {
                        $fujian = new GuahaoHospitalModel();
                        $fujian->city_code = 0;
                        $fujian->province = "";
                        $fujian->hospital_name = $item['hospital_name'];
                        $fujian->tp_hospital_code = $item['tp_hospital_code'];
                        $fujian->tp_hospital_level = $item['tp_hospital_level'];
                        $fujian->create_time = time();
                        $fujian->status = 0;
                        $fujian->remarks = '';
                        $fujian->tp_guahao_verify = '';
                        $fujian->corp_id = '';
                        $fujian->tp_platform = self::TP_PLATFORM;
                        $fujian->tp_guahao_section = 0;
                        $fujian->tp_guahao_description = '';
                        $fujian->tp_allowed_cancel_day = 1;
                        $fujian->tp_allowed_cancel_time = '12:00';
                        $fujian->tp_open_day = 0;
                        $fujian->tp_open_time = '';
                        $fujian->save();
                        echo "key：" . $key . ' 医院 ' . $item['hospital_name'] . "-入库\n\r";
                    }
                } else {
                    echo "key：" . $key . ' 医院 ' . "-tp_hospital_code为空\n\r";
                }
            }
        } else {
            echo "医院无数据》》" . "-\n\r";
        }
        echo 'hospital end' . PHP_EOL;
    }

    /**
     * 根据医院获取科室
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2021-10-11
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
                        'tp_platform' => self::TP_PLATFORM,
                        'tp_hospital_code' => ArrayHelper::getValue($v, 'tp_hospital_code'),
                    ];
                    $department = SnisiyaSdk::getInstance()->getGuahaoDepartment($params);
                    $department = ArrayHelper::getValue($department, 'list');
                    if ($department) {
                        foreach ($department as $ks) {
                            //科室id或科室名称为空的时候不拉取
                            if (empty($ks['tp_department_id']) || empty($ks['department_name'])) {
                                echo '[科室]' . $v['hospital_name'] . '--' . $ks['tp_department_id'] . '--' . $ks['department_name'] . '--' . date("Y-m-d H:i:s") .'科室id或科室名称不合法'.PHP_EOL;
                                continue;
                            }
                            $relationParams['tp_hospital_code'] = $v['tp_hospital_code'];
                            $relationParams['tp_platform'] = self::TP_PLATFORM;
                            $relationParams['hospital_name'] = $v['hospital_name'];
                            $relationParams['hospital_id'] = $v['hospital_id'];
                            $relationParams['tp_department_id'] = $ks['tp_department_id'];
                            $relationParams['department_name'] = $ks['department_name'];
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
                }
            } else {
                echo '没有数据了或者医院未关联:' . strval($tp_hospital_code). PHP_EOL;
            }
            $page++;
        } while (count($hospList) > 0);
        echo 'keshi end' . PHP_EOL;
    }

    /**
     * 根据科室获取医生
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2021-10-11
     * @version v1.0
     * @param   array     $params [description]
     * @return  [type]            [description]
     */
    public function actionGetTpDoctor($params = [])
    {
        $param = [
            'tp_platform' => $params['tp_platform'],
            'tp_hospital_code' => $params['tp_hospital_code'],
            'tp_department_id' => $params['tp_department_id'],
        ];
        $docList = SnisiyaSdk::getInstance()->getGuahaoDoctor($param);
        $docList = ArrayHelper::getValue($docList, 'list');
        return $docList ?? [];
    }

    /**
     * 医生停诊回调订单信息
     * @param
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * date  2021-10-20
     * @return
     */
    public function actionHospitalClose()
    {
        $today = date('Y-m-d', time());
        $orderData = GuahaoOrderModel::find()->alias('o')
            ->join('LEFT JOIN', ['oi' => 'tb_guahao_order_info'], 'o.id=oi.order_id')
            ->where(['=', 'o.tp_platform', self::TP_PLATFORM])
            ->andWhere(['=', 'o.state', 0])
            ->andWhere(['>=', 'o.visit_time', $today])
            ->select(['o.id', 'o.order_sn', 'o.tp_hospital_code', 'o.tp_order_id', 'oi.scheduling_id', 'oi.tp_scheduling_id'])
            ->orderBy('o.id ASC')
            ->asArray()
            ->all();
        $successNum = 0;
        $count = count($orderData);
        foreach ($orderData as $v) {
            $params['tp_platform'] = self::TP_PLATFORM;
            $params['tp_hospital_code'] = $v['tp_hospital_code'];
            $params['tp_scheduling_id'] = $v['tp_scheduling_id'];
            $data = SnisiyaSdk::getInstance()->getTpHospitalClose($params);

            if (isset($data['tj_status']) && $data['tj_status'] == 1) {
                $successNum++;
                $info = GuahaoCallback::orderStop($v['order_sn']);
                echo "订单号：{$v['order_sn']} 所对应的排班信息（{$params['tp_scheduling_id']}）--已停诊!! 数据信息：" . json_decode($info, JSON_UNESCAPED_UNICODE);
            }
        }
        echo "总共{$count}订单，完成 {$successNum} 个订单数据！！\n";
    }

}
