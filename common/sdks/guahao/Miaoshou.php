<?php

namespace common\sdks\guahao;

use common\libs\CommonFunc;
use common\models\GuahaoHospitalModel;
use common\models\HospitalDepartmentRelation;
use common\sdks\GuahaoInterface;
use common\sdks\snisiya\SnisiyaSdk;
use yii\base\Controller;
use yii\helpers\ArrayHelper;

class nisiya extends Controller implements GuahaoInterface
{
    const TP_SICHUAN = 12;//四川
    public $pagesize = 100;

    /**
     * @var int 第三方平台类型
     */
    public $tp_platform = '';

    public $provice = [
        110000 => '北京市',
        120000 => '天津市',
        130000 => '河北省',
        140000 => '山西省',
        150000 => '内蒙古自治区',
        210000 => '辽宁省',
        220000 => '吉林省',
        230000 => '黑龙江省',
        310000 => '上海市',
        320000 => '江苏省',
        330000 => '浙江省',
        340000 => '安徽省',
        350000 => '福建省',
        360000 => '江西省',
        370000 => '山东省',
        410000 => '河南省',
        420000 => '湖北省',
        430000 => '湖南省',
        440000 => '广东省',
        450000 => '广西壮族自治区',
        460000 => '海南省',
        500000 => '重庆市',
        510000 => '四川省',
        520000 => '贵州省',
        530000 => '云南省',
        540000 => '西藏自治区',
        610000 => '陕西省',
        620000 => '甘肃省',
        630000 => '青海省',
        640000 => '宁夏回族自治区',
        650000 => '新疆维吾尔自治区',
        710000 => '台湾省',
        810000 => '香港特别行政区',
        820000 => '澳门特别行政区'
    ];

    public $provinceCity = [
        self::TP_SICHUAN => [
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 510100, 'cityName' => '成都市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 510300, 'cityName' => '自贡市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 510400, 'cityName' => '攀枝花市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 510500, 'cityName' => '泸州市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 510600, 'cityName' => '德阳市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 510700, 'cityName' => '绵阳市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 510800, 'cityName' => '广元市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 510900, 'cityName' => '遂宁市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 511000, 'cityName' => '内江市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 511100, 'cityName' => '乐山市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 511300, 'cityName' => '南充市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 511400, 'cityName' => '眉山市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 511500, 'cityName' => '宜宾市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 511600, 'cityName' => '广安市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 511700, 'cityName' => '达州市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 511800, 'cityName' => '雅安市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 511900, 'cityName' => '巴中市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 512000, 'cityName' => '资阳市'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 513200, 'cityName' => '阿坝藏族羌族自治州'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 513300, 'cityName' => '甘孜藏族自治州'],
            ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 513400, 'cityName' => '凉山彝族自治州'],
        ],
    ];

    public function __construct($tp_platform)
    {
        $this->tp_platform = $tp_platform;
    }

    public function actionGetTpHospital($tp_hospital_code = '')
    {
        $params = [
            'tp_platform' => $this->tp_platform,
        ];
        if ($tp_hospital_code) {
            $params['tp_hospital_code'] = $tp_hospital_code;
        }
        $provinceCity = ArrayHelper::getValue($this->provinceCity, $this->tp_platform);
        if (!empty($provinceCity)) {
            $baseHospitalLevel = CommonFunc::getHospitalLevel();
            foreach ($provinceCity as $val) {
                $params['province_code'] = $val['provinceCode'];
                $params['city_code'] = $val['cityCode'];
                $res = SnisiyaSdk::getInstance()->getGuahaoHospital($params);
                if (isset($res['list']) && $res['list']) {
                    echo $val['cityName'] . "--医院数》》" . $res['total'] . "-\n\r";
                    foreach ($res['list'] as $key => $item) {
                        if (isset($item['tp_hospital_code'])) {
                            $hos = GuahaoHospitalModel::find()->where(['tp_platform' => $this->tp_platform, 'tp_hospital_code' => $item['tp_hospital_code']])->one();
                            list ($status, $remarks, $verify) = $this->getTpStatus($item);
                            if ($hos) {
                                $hos->tp_allowed_cancel_day = $this->getTpConfig('tp_allowed_cancel_day', ArrayHelper::getValue($item, 'tp_allowed_cancel_day'));
                                $hos->tp_allowed_cancel_time = $this->getTpConfig('tp_allowed_cancel_time', ArrayHelper::getValue($item, 'tp_allowed_cancel_time'));
                                $hos->tp_guahao_description = ArrayHelper::getValue($item, 'tp_guahao_description', '');
                                $hos->tp_hospital_level = $item['tp_hospital_level'] == 11 ? '其他' : ArrayHelper::getValue($baseHospitalLevel, $item['tp_hospital_level'], '其他');
                                $hos->tp_open_day = $this->getTpConfig('tp_open_day', ArrayHelper::getValue($item, 'tp_open_day'));
                                $hos->tp_open_time = $this->getTpConfig('tp_open_time', ArrayHelper::getValue($item, 'tp_open_time'));
                                $hos->save();
                                echo $item['tp_hospital_name'] . "-已存在\n\r";
                            } else {
                                $hospModel = new GuahaoHospitalModel();
                                $hospModel->tp_hospital_code = $item['tp_hospital_code'];
                                $hospModel->tp_platform = $this->tp_platform;
                                $hospModel->hospital_name = $item['tp_hospital_name'];
                                $hospModel->city_code = $val['cityCode'];
                                $hospModel->status = $status;
                                $hospModel->create_time = time();
                                $hospModel->remarks = $remarks;
                                $hospModel->tp_guahao_section = 0;
                                $hospModel->tp_guahao_verify = $verify;
                                $hospModel->tp_allowed_cancel_day = $this->getTpConfig('tp_allowed_cancel_day', ArrayHelper::getValue($item, 'tp_allowed_cancel_day'));
                                $hospModel->tp_allowed_cancel_time = $this->getTpConfig('tp_allowed_cancel_time', ArrayHelper::getValue($item, 'tp_allowed_cancel_time'));
                                $hospModel->tp_guahao_description = ArrayHelper::getValue($item, 'tp_guahao_description', '');
                                $hospModel->province = $val['provinceName'];
                                $hospModel->tp_hospital_level = $item['tp_hospital_level'] == 11 ? '其他' : ArrayHelper::getValue($baseHospitalLevel, $item['tp_hospital_level'], '其他');
                                $hospModel->tp_open_day = $this->getTpConfig('tp_open_day', ArrayHelper::getValue($item, 'tp_open_day'));
                                $hospModel->tp_open_time = $this->getTpConfig('tp_open_time', ArrayHelper::getValue($item, 'tp_open_time'));
                                $hospModel->save();
                                echo "key：" . $key . $val['provinceName'] . ' 医院 ' . $item['tp_hospital_name'] . "-入库\n\r";
                            }
                        } else {
                            echo "key：" . $key . $val['provinceName'] . ' 医院 ' . "-tp_hospital_code为空\n\r";
                        }
                    }
                } else {
                    echo $val['provinceName'] . $val['cityName'] . "--医院数 无数据》》" . "-\n\r";
                }
            }
        }

        echo 'hospital end' . PHP_EOL;
    }

    /**
     * @param array $config
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/28
     */
    public function getTpStatus($config = [])
    {
        $status = 0;
        $remarks = "";
        $verify = "";

        if (ArrayHelper::getValue($config, 'pay_mode') == 1) {
            $remarks .= "需要在线支付,";
            $status = 2;
        }
        if (ArrayHelper::getValue($config, 'need_clinic_card') == 1) {
            $remarks .= "需要就诊卡,";
            $status = 2;
            $verify = 2;
        }
        if (ArrayHelper::getValue($config, 'need_build') == 1) {
            $remarks .= "需要在线建档,";
            $status = 2;
        }
        return [$status, $remarks, $verify];
    }

    /**
     * @param $type
     * @param string $value
     * @return mixed|string
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/28
     */
    public function getTpConfig($type, $value = '')
    {
        if (!empty($value)) {
            return $value;
        }
        switch ($type) {
            case'tp_allowed_cancel_day':
                $value = '1';
                break;
            case'tp_allowed_cancel_time':
                $value = '12:00';
                break;
            case'tp_open_day':
                $value = '0';
                break;
            case'tp_open_time':
                $value = '';
                break;
        }
        return $value;
    }

    /**
     * @param string $tp_hospital_code
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/28
     */
    public function pullDepartment($tp_hospital_code = '')
    {
        $page = 0;
        $pagesize = $this->pagesize;
        do {
            $offset = $page * $pagesize;
            $hos_query = GuahaoHospitalModel::find()->where(['=', 'tp_platform', $this->tp_platform]);
            $hos_query->andWhere(['=', 'status', 1]);
            if ($tp_hospital_code) {
                $hos_query->andWhere(['=', 'tp_hospital_code', $tp_hospital_code]);
            }
            $hospList = $hos_query->offset($offset)->limit($pagesize)->asArray()->all();
            if ($hospList) {
                foreach ($hospList as $v) {
                    $params = [
                        'tp_platform' => $this->tp_platform,
                        'tp_hospital_code' => ArrayHelper::getValue($v, 'tp_hospital_code'),
                    ];
                    $department = SnisiyaSdk::getInstance()->getGuahaoDepartment($params);
                    $department = ArrayHelper::getValue($department, 'list');
                    if ($department) {
                        foreach ($department as $ks) {
                            //科室id或科室名称为空的时候不拉取
                            if (empty($ks['tp_department_id']) || empty($ks['tp_department_name'])) {
                                echo '[科室]' . $v['hospital_name'] . '--' . $ks['tp_department_id'] . '--' . $ks['tp_department_name'] . '--' . date("Y-m-d H:i:s") . '科室id或科室名称不合法' . PHP_EOL;
                                continue;
                            }
                            $relationParams['tp_hospital_code'] = $v['tp_hospital_code'];
                            $relationParams['tp_platform'] = $this->tp_platform;
                            $relationParams['hospital_name'] = $v['hospital_name'];
                            $relationParams['hospital_id'] = $v['hospital_id'];
                            $relationParams['tp_department_id'] = $ks['tp_department_id'];
                            $relationParams['department_name'] = $ks['tp_department_name'];
                            $result = HospitalDepartmentRelation::autoImportDepartment($relationParams);
                            if ($result['code'] == 200) {
                                echo '[科室]' . $v['hospital_name'] . '--' . $ks['tp_department_id'] . '--' . $ks['tp_department_name'] . '--' . date("Y-m-d H:i:s") . $result['msg'] . PHP_EOL;
                            } else {
                                echo '[科室]' . $v['hospital_name'] . '--' . $ks['tp_department_id'] . '--' . $ks['tp_department_name'] . '--' . date("Y-m-d H:i:s") . $result['msg'] . PHP_EOL;
                            }
                            unset($relationParams);
                        }
                    } else {
                        echo ("医院:{$v['hospital_name']} 医院id:{$v['tp_hospital_code']}  " . date('Y-m-d H:i:s', time())) . '暂无数据' . PHP_EOL;
                    }
                }
            } else {
                echo '没有数据了或者医院未关联:' . strval($tp_hospital_code) . PHP_EOL;
            }
            $page++;
        } while (count($hospList) > 0);

        echo 'keshi end' . PHP_EOL;
    }

    /**
     * @param array $params
     * @return array|mixed
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/28
     */
    public function actionGetTpDoctor($params = [])
    {
        $param = [
            'tp_platform' => $this->tp_platform,
            'tp_hospital_code' => $params['tp_hospital_code'],
            'tp_department_id' => $params['tp_department_id'],
        ];
        $docList = SnisiyaSdk::getInstance()->getGuahaoDoctor($param);
        $docList = ArrayHelper::getValue($docList, 'list');
        return $docList ?? [];
    }

}
