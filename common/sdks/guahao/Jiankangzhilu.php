<?php

namespace common\sdks\guahao;

use common\models\GuahaoHospitalModel;
use common\models\HospitalDepartmentRelation;
use common\sdks\snisiya\SnisiyaSdk;
use yii\helpers\ArrayHelper;

class Jiankangzhilu extends \yii\base\Controller implements \common\sdks\GuahaoInterface
{

    const  TP_PLATFORM = 9;
    public $tp_platform = 9;
    public $pagesize  = 500;
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

    //福建省下的城市
    public $provinceCity = [
        //福建省
        ['provinceCode' => 350000, 'provinceName' => '福建省', 'cityCode' => 350100, 'cityName' => '福州市'],
        ['provinceCode' => 350000, 'provinceName' => '福建省', 'cityCode' => 350200, 'cityName' => '厦门市'],
        ['provinceCode' => 350000, 'provinceName' => '福建省', 'cityCode' => 350300, 'cityName' => '莆田市'],
        ['provinceCode' => 350000, 'provinceName' => '福建省', 'cityCode' => 350400, 'cityName' => '三明市'],
        ['provinceCode' => 350000, 'provinceName' => '福建省', 'cityCode' => 350500, 'cityName' => '泉州市'],
        ['provinceCode' => 350000, 'provinceName' => '福建省', 'cityCode' => 350600, 'cityName' => '漳州市'],
        ['provinceCode' => 350000, 'provinceName' => '福建省', 'cityCode' => 350700, 'cityName' => '南平市'],
        ['provinceCode' => 350000, 'provinceName' => '福建省', 'cityCode' => 350800, 'cityName' => '龙岩市'],
        ['provinceCode' => 350000, 'provinceName' => '福建省', 'cityCode' => 350900, 'cityName' => '宁德市'],

        //湖北省
        ['provinceCode' => 420000, 'provinceName' => '湖北省', 'cityCode' => 420100, 'cityName' => '武汉市'],
        ['provinceCode' => 420000, 'provinceName' => '湖北省', 'cityCode' => 420200, 'cityName' => '黄石市'],
        ['provinceCode' => 420000, 'provinceName' => '湖北省', 'cityCode' => 420300, 'cityName' => '十堰市'],
        ['provinceCode' => 420000, 'provinceName' => '湖北省', 'cityCode' => 420500, 'cityName' => '宜昌市'],
        ['provinceCode' => 420000, 'provinceName' => '湖北省', 'cityCode' => 420600, 'cityName' => '襄阳市'],
        ['provinceCode' => 420000, 'provinceName' => '湖北省', 'cityCode' => 420700, 'cityName' => '鄂州市'],
        ['provinceCode' => 420000, 'provinceName' => '湖北省', 'cityCode' => 420800, 'cityName' => '荆门市'],
        ['provinceCode' => 420000, 'provinceName' => '湖北省', 'cityCode' => 420900, 'cityName' => '孝感市'],
        ['provinceCode' => 420000, 'provinceName' => '湖北省', 'cityCode' => 421000, 'cityName' => '荆州市'],
        ['provinceCode' => 420000, 'provinceName' => '湖北省', 'cityCode' => 421100, 'cityName' => '黄冈市'],
        ['provinceCode' => 420000, 'provinceName' => '湖北省', 'cityCode' => 421200, 'cityName' => '咸宁市'],
        ['provinceCode' => 420000, 'provinceName' => '湖北省', 'cityCode' => 421300, 'cityName' => '随州市'],
        ['provinceCode' => 420000, 'provinceName' => '湖北省', 'cityCode' => 422800, 'cityName' => '恩施土家族苗族自治州'],

        //北京市 直辖市
        ['provinceCode' => 110000, 'provinceName' => '北京市', 'cityCode' => '', 'cityName' => ''],

        //天津市 直辖市
        ['provinceCode' => 120000, 'provinceName' => '天津市', 'cityCode' => '', 'cityName' => ''],

        //河北省
        ['provinceCode' => 130000, 'provinceName' => '河北省', 'cityCode' => 130100, 'cityName' => '石家庄市'],
        ['provinceCode' => 130000, 'provinceName' => '河北省', 'cityCode' => 130200, 'cityName' => '唐山市'],
        ['provinceCode' => 130000, 'provinceName' => '河北省', 'cityCode' => 130400, 'cityName' => '邯郸市'],
        ['provinceCode' => 130000, 'provinceName' => '河北省', 'cityCode' => 130500, 'cityName' => '邢台市'],
        ['provinceCode' => 130000, 'provinceName' => '河北省', 'cityCode' => 130600, 'cityName' => '保定市'],
        ['provinceCode' => 130000, 'provinceName' => '河北省', 'cityCode' => 131100, 'cityName' => '衡水市'],

        //山西省
        ['provinceCode' => 140000, 'provinceName' => '山西省', 'cityCode' => 140100, 'cityName' => '太原市'],
        ['provinceCode' => 140000, 'provinceName' => '山西省', 'cityCode' => 140200, 'cityName' => '大同市'],
        ['provinceCode' => 140000, 'provinceName' => '山西省', 'cityCode' => 140400, 'cityName' => '长治市'],
        ['provinceCode' => 140000, 'provinceName' => '山西省', 'cityCode' => 140500, 'cityName' => '晋城市'],
        ['provinceCode' => 140000, 'provinceName' => '山西省', 'cityCode' => 140600, 'cityName' => '朔州市'],
        ['provinceCode' => 140000, 'provinceName' => '山西省', 'cityCode' => 140800, 'cityName' => '运城市'],
        ['provinceCode' => 140000, 'provinceName' => '山西省', 'cityCode' => 140900, 'cityName' => '忻州市'],
        ['provinceCode' => 140000, 'provinceName' => '山西省', 'cityCode' => 141000, 'cityName' => '临汾市'],
        ['provinceCode' => 140000, 'provinceName' => '山西省', 'cityCode' => 141100, 'cityName' => '吕梁市'],

        //内蒙古自治区
        ['provinceCode' => 150000, 'provinceName' => '内蒙古自治区', 'cityCode' => 150100, 'cityName' => '呼和浩特市'],
        ['provinceCode' => 150000, 'provinceName' => '内蒙古自治区', 'cityCode' => 150200, 'cityName' => '包头市'],
        ['provinceCode' => 150000, 'provinceName' => '内蒙古自治区', 'cityCode' => 150300, 'cityName' => '乌海市'],
        ['provinceCode' => 150000, 'provinceName' => '内蒙古自治区', 'cityCode' => 150400, 'cityName' => '赤峰市'],
        ['provinceCode' => 150000, 'provinceName' => '内蒙古自治区', 'cityCode' => 150600, 'cityName' => '鄂尔多斯市'],
        ['provinceCode' => 150000, 'provinceName' => '内蒙古自治区', 'cityCode' => 150700, 'cityName' => '呼伦贝尔市'],
        ['provinceCode' => 150000, 'provinceName' => '内蒙古自治区', 'cityCode' => 152500, 'cityName' => '锡林郭勒盟'],

        //辽宁省
        ['provinceCode' => 210000, 'provinceName' => '辽宁省', 'cityCode' => 210300, 'cityName' => '鞍山市'],
        ['provinceCode' => 210000, 'provinceName' => '辽宁省', 'cityCode' => 210500, 'cityName' => '本溪市'],
        ['provinceCode' => 210000, 'provinceName' => '辽宁省', 'cityCode' => 210800, 'cityName' => '营口市'],

        //吉林省
        ['provinceCode' => 220000, 'provinceName' => '吉林省', 'cityCode' => 222400, 'cityName' => '延边朝鲜族自治州'],

        //黑龙江省
        ['provinceCode' => 230000, 'provinceName' => '黑龙江省', 'cityCode' => 230200, 'cityName' => '齐齐哈尔市'],
        ['provinceCode' => 230000, 'provinceName' => '黑龙江省', 'cityCode' => 231200, 'cityName' => '绥化市'],

        //上海市 直辖市
        ['provinceCode' => 310000, 'provinceName' => '上海市', 'cityCode' => '', 'cityName' => ''],

        //江苏省
        ['provinceCode' => 320000, 'provinceName' => '江苏省', 'cityCode' => 320100, 'cityName' => '南京市'],
        ['provinceCode' => 320000, 'provinceName' => '江苏省', 'cityCode' => 320300, 'cityName' => '徐州市'],
        ['provinceCode' => 320000, 'provinceName' => '江苏省', 'cityCode' => 320500, 'cityName' => '苏州市'],

        //浙江省
        ['provinceCode' => 330000, 'provinceName' => '浙江省', 'cityCode' => 330100, 'cityName' => '杭州市'],

        //安徽省
        ['provinceCode' => 340000, 'provinceName' => '安徽省', 'cityCode' => 340100, 'cityName' => '合肥市'],

        //江西省
        ['provinceCode' => 360000, 'provinceName' => '江西省', 'cityCode' => 360100, 'cityName' => '南昌市'],
        ['provinceCode' => 360000, 'provinceName' => '江西省', 'cityCode' => 360200, 'cityName' => '景德镇市'],
        ['provinceCode' => 360000, 'provinceName' => '江西省', 'cityCode' => 360300, 'cityName' => '萍乡市'],
        ['provinceCode' => 360000, 'provinceName' => '江西省', 'cityCode' => 360400, 'cityName' => '九江市'],
        ['provinceCode' => 360000, 'provinceName' => '江西省', 'cityCode' => 360500, 'cityName' => '新余市'],
        ['provinceCode' => 360000, 'provinceName' => '江西省', 'cityCode' => 360600, 'cityName' => '鹰潭市'],
        ['provinceCode' => 360000, 'provinceName' => '江西省', 'cityCode' => 360700, 'cityName' => '赣州市'],
        ['provinceCode' => 360000, 'provinceName' => '江西省', 'cityCode' => 360800, 'cityName' => '吉安市'],
        ['provinceCode' => 360000, 'provinceName' => '江西省', 'cityCode' => 360900, 'cityName' => '宜春市'],
        ['provinceCode' => 360000, 'provinceName' => '江西省', 'cityCode' => 361000, 'cityName' => '抚州市'],
        ['provinceCode' => 360000, 'provinceName' => '江西省', 'cityCode' => 361100, 'cityName' => '上饶市'],

        //山东省
        ['provinceCode' => 370000, 'provinceName' => '山东省', 'cityCode' => 370200, 'cityName' => '青岛市'],

        //河南省
        ['provinceCode' => 410000, 'provinceName' => '河南省', 'cityCode' => 410100, 'cityName' => '郑州市'],
        ['provinceCode' => 410000, 'provinceName' => '河南省', 'cityCode' => 410200, 'cityName' => '开封市'],
        ['provinceCode' => 410000, 'provinceName' => '河南省', 'cityCode' => 410400, 'cityName' => '平顶山市'],
        ['provinceCode' => 410000, 'provinceName' => '河南省', 'cityCode' => 410700, 'cityName' => '新乡市'],
        ['provinceCode' => 410000, 'provinceName' => '河南省', 'cityCode' => 410800, 'cityName' => '焦作市'],
        ['provinceCode' => 410000, 'provinceName' => '河南省', 'cityCode' => 410900, 'cityName' => '濮阳市'],
        ['provinceCode' => 410000, 'provinceName' => '河南省', 'cityCode' => 411000, 'cityName' => '许昌市'],
        ['provinceCode' => 410000, 'provinceName' => '河南省', 'cityCode' => 411300, 'cityName' => '南阳市'],
        ['provinceCode' => 410000, 'provinceName' => '河南省', 'cityCode' => 411500, 'cityName' => '信阳市'],
        ['provinceCode' => 410000, 'provinceName' => '河南省', 'cityCode' => 411600, 'cityName' => '周口市'],
        ['provinceCode' => 410000, 'provinceName' => '河南省', 'cityCode' => 411700, 'cityName' => '驻马店市'],

        //湖南省
        ['provinceCode' => 430000, 'provinceName' => '湖南省', 'cityCode' => 430100, 'cityName' => '长沙市'],

        //广东省
        ['provinceCode' => 440000, 'provinceName' => '广东省', 'cityCode' => 440100, 'cityName' => '广州市'],
        ['provinceCode' => 440000, 'provinceName' => '广东省', 'cityCode' => 440800, 'cityName' => '湛江市'],
        ['provinceCode' => 440000, 'provinceName' => '广东省', 'cityCode' => 440900, 'cityName' => '茂名市'],
        ['provinceCode' => 440000, 'provinceName' => '广东省', 'cityCode' => 441300, 'cityName' => '惠州市'],

        //广西壮族自治区
        ['provinceCode' => 450000, 'provinceName' => '广西壮族自治区', 'cityCode' => 450100, 'cityName' => '南宁市'],
        ['provinceCode' => 450000, 'provinceName' => '广西壮族自治区', 'cityCode' => 450200, 'cityName' => '柳州市'],
        ['provinceCode' => 450000, 'provinceName' => '广西壮族自治区', 'cityCode' => 450400, 'cityName' => '梧州市'],
        ['provinceCode' => 450000, 'provinceName' => '广西壮族自治区', 'cityCode' => 450500, 'cityName' => '北海市'],
        ['provinceCode' => 450000, 'provinceName' => '广西壮族自治区', 'cityCode' => 450800, 'cityName' => '贵港市'],
        ['provinceCode' => 450000, 'provinceName' => '广西壮族自治区', 'cityCode' => 450900, 'cityName' => '玉林市'],

        //海南省
        ['provinceCode' => 460000, 'provinceName' => '海南省', 'cityCode' => 460100, 'cityName' => '海口市'],

        //重庆市	直辖市
        ['provinceCode' => 500000, 'provinceName' => '重庆市', 'cityCode' => '', 'cityName' => ''],

        //四川省
        ['provinceCode' => 510000, 'provinceName' => '四川省', 'cityCode' => 510100, 'cityName' => '成都市'],

        //贵州省
        ['provinceCode' => 520000, 'provinceName' => '贵州省', 'cityCode' => 520100, 'cityName' => '贵阳市'],
        ['provinceCode' => 520000, 'provinceName' => '贵州省', 'cityCode' => 520400, 'cityName' => '安顺市'],
        ['provinceCode' => 520000, 'provinceName' => '贵州省', 'cityCode' => 520500, 'cityName' => '毕节市'],
        ['provinceCode' => 520000, 'provinceName' => '贵州省', 'cityCode' => 522600, 'cityName' => '黔东南苗族侗族自治州'],

        //云南省
        ['provinceCode' => 530000, 'provinceName' => '云南省', 'cityCode' => 530100, 'cityName' => '昆明市'],

        //西藏自治区
        ['provinceCode' => 540000, 'provinceName' => '西藏自治区', 'cityCode' => 542500, 'cityName' => '阿里地区'],
        ['provinceCode' => 540000, 'provinceName' => '西藏自治区', 'cityCode' => 540400, 'cityName' => '林芝市'],

        //陕西省
        ['provinceCode' => 610000, 'provinceName' => '陕西省', 'cityCode' => 610100, 'cityName' => '西安市'],
        ['provinceCode' => 610000, 'provinceName' => '陕西省', 'cityCode' => 610300, 'cityName' => '宝鸡市'],
        ['provinceCode' => 610000, 'provinceName' => '陕西省', 'cityCode' => 610600, 'cityName' => '延安市'],
        ['provinceCode' => 610000, 'provinceName' => '陕西省', 'cityCode' => 610900, 'cityName' => '安康市'],

        //甘肃省
        ['provinceCode' => 620000, 'provinceName' => '甘肃省', 'cityCode' => 620100, 'cityName' => '兰州市'],

        //青海省
        ['provinceCode' => 630000, 'provinceName' => '青海省', 'cityCode' => 630100, 'cityName' => '西宁市'],

        //宁夏回族自治区
        ['provinceCode' => 640000, 'provinceName' => '宁夏回族自治区', 'cityCode' => 640100, 'cityName' => '银川市'],

        //新疆维吾尔自治区
        ['provinceCode' => 650000, 'provinceName' => '新疆维吾尔自治区', 'cityCode' => 650100, 'cityName' => '乌鲁木齐市'],
        ['provinceCode' => 650000, 'provinceName' => '新疆维吾尔自治区', 'cityCode' => 659001, 'cityName' => '石河子市'],
    ];

    /**
     * 拉取福建医院
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2021-09-07
     * @version v1.0
     * @return  [type]     [description]
     */
    public function actionGetTpHospital($tp_hospital_code = '')
    {
        $params = [
            'tp_platform' => self::TP_PLATFORM,
            'pagesize'    => 15,
        ];
        if ($tp_hospital_code) {
            $params['tp_hospital_code'] = $tp_hospital_code;
        }
        foreach ($this->provinceCity as $val) {
            $page   = 1;
            do {
                $params['page']         = $page;
                $params['provinceCode'] = $val['provinceCode'];
                $params['cityCode']     = $val['cityCode'] ?? '';
                $res            = SnisiyaSdk::getInstance()->getGuahaoHospital($params);
                if (isset($res['list']) && $res['list']) {
                    echo $val['cityName'] . "--医院数》》" .$res['total'] . "-\n\r";
                    foreach ($res['list'] as $key => $item) {
                        if (isset($item['tp_hospital_code'])) {
                            $hos           = GuahaoHospitalModel::find()->where(['tp_platform' => self::TP_PLATFORM, 'tp_hospital_code' => $item['tp_hospital_code']])->one();
                            if ($hos) {
                                echo $item['hospital_name'] . "-已存在\n\r";
                            } else {
                                $paramConfig = [
                                    'tp_platform' => self::TP_PLATFORM,
                                    'hospitalId' => $item['tp_hospital_code']
                                ];
                                $configs = SnisiyaSdk::getInstance()->getHospitalByid($paramConfig);
                                $tp_check = isset($configs['tp_check']) ? $configs['tp_check'] : ['need_card' => 0, 'need_jianhuren' => 0, 'need_children' => 0];
                                list ($status, $remarks, $verify) = $this->getTpStatus($tp_check);
                                $fujian = new GuahaoHospitalModel();
                                $fujian->city_code = !empty($val['cityCode']) ? $val['cityCode'] : 0;
                                $fujian->province = isset($this->provice[$val['provinceCode']]) ? $this->provice[$val['provinceCode']] : "";
                                $fujian->hospital_name = $item['hospital_name'];
                                $fujian->tp_hospital_code = $item['tp_hospital_code'];
                                $fujian->tp_hospital_level = $item['tp_hospital_level'];
                                $fujian->create_time = time();
                                $fujian->status = $status;
                                $fujian->remarks = $remarks;
                                $fujian->tp_guahao_verify = $verify;
                                $fujian->corp_id = '';
                                $fujian->tp_platform = self::TP_PLATFORM;
                                $fujian->tp_guahao_section = 0;
                                $fujian->tp_guahao_description = '';
                                $fujian->tp_allowed_cancel_day = 1;
                                $fujian->tp_allowed_cancel_time = '12:00';
                                $fujian->tp_open_day = 0;
                                $fujian->tp_open_time = '';
                                $fujian->save();
                                echo "key：" . $key . $val['provinceName'] . ' 医院 ' . $item['hospital_name'] . "-入库\n\r";
                            }
                        } else {
                            echo "key：" . $key . $val['provinceName'] . ' 医院 ' . "-tp_hospital_code为空\n\r";
                        }
                    }
                } else {
                    echo $val['provinceName'] . $val['cityName'] . "--医院数 无数据》》" . "-\n\r";
                }
                $page++;
            } while (isset($res['list']) && $res['list']);

            //sleep(5);
        }
        echo 'hospital end' . PHP_EOL;
        //$this->pullDepartment($tp_hospital_code = '');

    }

    /**
     * 检测医院信息是否必填就诊卡或者是否必填监护人或者必填是否儿童
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2021-068-23
     * @version v1.0
     * @param   array     $tp_check
     * @return  []
     */
    public function getTpStatus ($config = [])
    {
        $status = 0;
        $remarks = "";
        $verify = "";
        $need_card_desc = " 必填就诊卡 ";
        $need_jianhuren_desc = " 必填监护人 ";
        $need_children_desc = " 必填儿童 ";
        if ($config['need_card'] == 1 || $config['need_jianhuren'] == 1 || $config['need_children'] == 1) {
            if ($config['need_card'] == 1) $verify .= "2";
            $status = 2;
            $remarks .= $need_card_desc . $need_jianhuren_desc . $need_children_desc;
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
                    $pageindex = 1;
                    do {
                        $params = [
                            'tp_platform' => self::TP_PLATFORM,
                            'tp_hospital_code' => ArrayHelper::getValue($v, 'tp_hospital_code'),
                            'page' => $pageindex,
                            'pagesize' => 100,
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
                                $relationParams['tp_hospital_code']    = $v['tp_hospital_code'];
                                $relationParams['tp_platform']         = self::TP_PLATFORM;
                                $relationParams['hospital_name']       = $v['hospital_name'];
                                $relationParams['hospital_id']         = $v['hospital_id'];
                                $relationParams['tp_department_id']    = $ks['tp_department_id'];
                                $relationParams['department_name']     = $ks['department_name'];
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
                        $pageindex++;
                    }while (!empty($department));

                    //unset($department);
                }
            } else {
                echo '没有数据了或者医院未关联:' . strval($tp_hospital_code). PHP_EOL;
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
        $param = [
            'tp_platform' => $params['tp_platform'],
            'tp_hospital_code' => $params['tp_hospital_code'],
            'tp_department_id' => $params['tp_department_id'],
            'page' => $params['page'],
            'pagesize' => $params['pagesize'] ?? 100,
        ];
        $docList = SnisiyaSdk::getInstance()->getGuahaoDoctor($param);
        $docList = ArrayHelper::getValue($docList, 'list');
        return $docList ?? [];
    }
}
