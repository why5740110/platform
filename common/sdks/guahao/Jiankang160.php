<?php
/**
 * 健康160
 * @file Jiankang160.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/1/25
 */

namespace common\sdks\guahao;

use common\libs\CommonFunc;
use common\models\GuahaoHospitalModel;
use common\models\HospitalDepartmentRelation;
use common\sdks\snisiya\SnisiyaSdk;
use yii\helpers\ArrayHelper;

class Jiankang160 extends \yii\base\Controller implements \common\sdks\GuahaoInterface
{

    const TP_PLATFORM = 5;
    public $pagesize  = 20;

    /**
     * 拉取医院
     * @throws \Exception
     * @author xiujianying
     * @date 2021/1/25
     */
    public function actionGetTpHospitalOld()
    {
        $hospArr = $this->getBaseHospital();

        foreach ($hospArr as $ss) {
            $ssArr = explode('||', $ss);
            $res[] = ['tp_hospital_code' => $ssArr[1], 'hospital_name' => $ssArr[2], 'province' => $ssArr[0], 'tp_hospital_level' => $ssArr[4]];
        }

        if ($res) {
            foreach ($res as $v) {
                $byidArr['tp_platform']      = self::TP_PLATFORM;
                $byidArr['tp_hospital_code'] = ArrayHelper::getValue($v, 'tp_hospital_code');
                $hospRow                     = SnisiyaSdk::getInstance()->getHospitalByid($byidArr);

                //医院名称
                $hosp = SnisiyaSdk::getInstance()->getGuahaoHospital($byidArr);
                $hosp = ArrayHelper::getValue($hosp, 'list');
                $name = ArrayHelper::getValue($hosp, '0.hospital_name');

                if (!$name) {
                    echo '[医院id]' . $byidArr['tp_hospital_code'] . "-获取失败\n";
                    continue;
                }

                $hospModel = new GuahaoHospitalModel();
                $exist     = $hospModel->find()->where([
                    'tp_hospital_code' => ArrayHelper::getValue($v, 'tp_hospital_code'),
                    'tp_platform'      => self::TP_PLATFORM,
                ])->exists();
                if ($exist) {
                    echo '[医院]' . $name . "-已存在\n";
                } else {
                    $hospModel->hospital_name         = $name;
                    $hospModel->tp_hospital_code      = $v['tp_hospital_code'];
                    $hospModel->create_time           = time();
                    $hospModel->status                = 0;
                    $hospModel->tp_platform           = self::TP_PLATFORM;
                    $hospModel->tp_guahao_verify      = ArrayHelper::getValue($hospRow, 'tp_guahao_verify', '');
                    $hospModel->tp_guahao_description = ArrayHelper::getValue($hospRow, 'tp_guahao_description', '');
                    $hospModel->province              = $v['province'];
                    $hospModel->tp_hospital_level     = $v['tp_hospital_level'];
                    $hospModel->save();
                    echo $name . "-入库\n";
                }
                //$this->actionGetTpDoctor($v['tp_hospital_code']);
            }
        } else {
            echo '没有数据了' . "\n";
        }
        echo 'hospital end' . PHP_EOL;
        sleep(2);
    }

    /**
     * 增量脚本 查医院循环科室医生
     * @param string $tp_hospital_code
     * @throws \Exception
     * @author xiujianying
     * @date 2021/3/15
     */
    public function actionGetTpHospital($tp_hospital_code = '')
    {
        $where['tp_platform'] = self::TP_PLATFORM;
        if ($tp_hospital_code) {
            $where['tp_hospital_code'] = $tp_hospital_code;
        }
        $hospList = GuahaoHospitalModel::find()->where($where)->select('id,hospital_id,tp_hospital_code,tp_open_day,tp_open_time,status')->asArray()->all();
        if ($hospList) {
            foreach ($hospList as $v) {

                //获取放号时间
                $timeConfigParams = [
                    'tp_platform' => self::TP_PLATFORM,
                    'tp_hospital_code' => ArrayHelper::getValue($v, 'tp_hospital_code'),
                ];
                $timeConfig = SnisiyaSdk::getInstance()->getTimeConfig($timeConfigParams);
                if (!empty($timeConfig)) {
                    $tp_open_day = ArrayHelper::getValue($timeConfig, 'tp_open_day', 0);
                    $tp_open_time = ArrayHelper::getValue($timeConfig, 'tp_open_time', '');
                    if ($v['tp_open_day'] != $tp_open_day || $v['tp_open_time'] != $tp_open_time) {
                        $hospitalModel = GuahaoHospitalModel::findOne($v['id']);
                        $hospitalModel->tp_open_day = $tp_open_day;
                        $hospitalModel->tp_open_time = $tp_open_time;
                        $hospitalModel->save();
                        //更新缓存es
                        if ($v['status'] == 1) {
                            CommonFunc::UpHospitalCache($v['hospital_id']);
                        }
                    }
                }

                //$this->pullDepartment($v['tp_hospital_code']);
            }
        }
    }

    /**
     * 根据医院获取科室
     * @throws \Exception
     * @author xiujianying
     * @date 2021/1/25
     */
    public function pullDepartment($tp_hospital_code = '')
    {
        $page     = 0;
        $pagesize = $this->pagesize;
        do {
            $offset               = $page * $pagesize;
            $where['tp_platform'] = self::TP_PLATFORM;
            if ($tp_hospital_code) {
                $where['tp_hospital_code'] = $tp_hospital_code;
            }
            $where['status'] = 1;
            $hospList = GuahaoHospitalModel::find()->where($where)->offset($offset)->limit($pagesize)->asArray()->all();
            if ($hospList) {
                foreach ($hospList as $v) {
                    $params = [
                        'tp_platform'      => self::TP_PLATFORM,
                        'tp_hospital_code' => ArrayHelper::getValue($v, 'tp_hospital_code'),
                    ];
                    $department = SnisiyaSdk::getInstance()->getGuahaoDepartment($params);
                    $department = ArrayHelper::getValue($department, 'list');
                    if ($department) {
                        foreach ($department as $ks) {
                            //获取放号时间
                            $timeConfigParams = [
                                'tp_platform' => self::TP_PLATFORM,
                                'tp_hospital_code' => ArrayHelper::getValue($v, 'tp_hospital_code'),
                                'tp_department_id' => ArrayHelper::getValue($ks, 'tp_department_id'),
                            ];
                            $timeConfig = SnisiyaSdk::getInstance()->getTimeConfig($timeConfigParams);
                            $tp_open_day = ArrayHelper::getValue($timeConfig, 'tp_open_day', 0);
                            $tp_open_time = ArrayHelper::getValue($timeConfig, 'tp_open_time', '');

                            $relationParams['tp_hospital_code']    = $v['tp_hospital_code'];
                            $relationParams['tp_platform']         = self::TP_PLATFORM;
                            $relationParams['hospital_name']       = $v['hospital_name'];
                            $relationParams['tp_department_id']    = strval($ks['tp_department_id']); //第三方科室id长度过长
                            $relationParams['hospital_id']          = $v['hospital_id'];
                            $relationParams['department_name']     = $ks['department_name'];
                            //放号时间
                            $relationParams['tp_open_day']         = $tp_open_day;
                            $relationParams['tp_open_time']        = $tp_open_time;
                            // (liuyingwei 科室自动导入 待调用 函数方法 2021-09-14 )
                            $result = HospitalDepartmentRelation::autoImportDepartment($relationParams);

                            if($result['code'] == 200){
                                echo $relationParams['department_name'] . $result['msg'] . date('Y-m-d H:i:s', time()) . PHP_EOL;
                            }else{
                                echo $relationParams['department_name'] . $result['msg'] . date('Y-m-d H:i:s', time()) . PHP_EOL;
                            }
                            unset($relationParams);

                        }
                    }

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
            'tp_platform'      => $params['tp_platform'],
            'tp_hospital_code' => $params['tp_hospital_code'],
            'tp_department_id' => $params['tp_department_id'],
        ];
        $docList = SnisiyaSdk::getInstance()->getGuahaoDoctor($param);
        $docList = ArrayHelper::getValue($docList, 'list');
        return $docList ?? [];
    }

    public function actionUpdateHospitalId()
    {
        // TODO: Implement actionUpdateHospitalId() method.
    }

    /**
     * 获取健康160的基础医院数据
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022-06-09
     * @return array
     */
    public function getBaseHospital()
    {
        $hospArr[] = '浙江||100001705||温州医科大学附属第一医院||国营||三级甲等';
        $hospArr[] = '合肥||100002630||六安市人民医院||国营||三级甲等';
        $hospArr[] = '东莞||344||东莞市厚街医院||国营||三级甲等';
        $hospArr[] = '上海||100000676||上海长海医院||国营||三级甲等';
        $hospArr[] = '武汉||200006063||华中科技大学同济医学院附属梨园医院||国营||三级甲等';
        $hospArr[] = '武汉||200000401||湖北六七二中西医结合骨科医院||国营||三级医院';
        $hospArr[] = '合肥||100001209||合肥市第二人民医院||国营||三级甲等';
        $hospArr[] = '广东||100000397||汕头市第二人民医院||国营||三级医院';
        $hospArr[] = '武汉||100001994||国药东风花果医院||国营||二级甲等';
        $hospArr[] = '广东||200021856||信宜市人民医院||国营||二级甲等';
        $hospArr[] = '广州||100000829||广州医科大学附属肿瘤医院||国营||三级甲等';
        $hospArr[] = '浙江||100003142||永嘉县人民医院||国营||二级甲等';
        $hospArr[] = '广州||200037716||广州市越秀区中医医院||国营||二级甲等';
        $hospArr[] = '广东||200037785||中国人民解放军第422医院||国营||三级甲等';
        $hospArr[] = '广州||100000001||广州市荔湾区人民医院||国营||二级甲等';
        $hospArr[] = '山东||200005561||李沧区中心医院||国营||二级甲等';
        $hospArr[] = '广东||100015243||吴川市人民医院||国营||二级甲等';
        $hospArr[] = '浙江||100016771||瓯海区第三人民医院||国营||二级乙等';
        $hospArr[] = '广州||200026052||广东药科大学附属第一医院 ||国营||三级甲等';
        $hospArr[] = '合肥||100001910||芜湖市中医院||国营||三级甲等';
        $hospArr[] = '重庆||200031803||重庆市妇幼保健院(七星岗院区)||国营||三级甲等';
        $hospArr[] = '广州||100000020||广州市妇女儿童医疗中心珠江新城院区||国营||三级甲等';
        $hospArr[] = '广东||100017927||佛山市禅城区南庄医院||国营||二级甲等';
        $hospArr[] = '山东||200005609||青岛市肛肠医院||国营||二级甲等';
        $hospArr[] = '广东||100000322||清远市人民医院||国营||三级甲等';
        $hospArr[] = '广州||100000357||从化中医院||国营||二级甲等';
        $hospArr[] = '广东||100000303||河源市人民医院||国营||三级乙等';
        $hospArr[] = '合肥||100001966||安庆市第一人民医院||国营||三级甲等';
        $hospArr[] = '淮南||200015152||淮南市第五人民医院||国营||二级甲等';
        $hospArr[] = '成都||200007651||四川省建筑医院||国营||二级甲等';
        $hospArr[] = '广州||200052201||广东药科大学附属第三医院（新市医院）||国营||三级医院';
        $hospArr[] = '深圳||200022601||深圳市第三人民医院（试运行）||国营||三级甲等';
        $hospArr[] = '合肥||100002684||铜陵市人民医院||国营||三级甲等';
        $hospArr[] = '深圳||200055352||龙华妇幼测试||国营||特等医院';
        $hospArr[] = '重庆||200014852||重庆市精神卫生中心（金紫山）||国营||三级甲等';
        $hospArr[] = '浙江||100016806||洞头县人民医院||国营||二级甲等';
        $hospArr[] = '广州||100000353||广州市白云区人民医院||国营||二级甲等';
        $hospArr[] = '淮南||200060202||安徽理工大学附属眼科医院||国营||二级医院';
        $hospArr[] = '深圳||156||深圳市大鹏新区葵涌人民医院||国营||一级甲等';
        $hospArr[] = '深圳||271||深圳市龙岗区第四人民医院||国营||一级甲等';
        $hospArr[] = '东莞||280||东莞市石排医院||国营||一级甲等';
        $hospArr[] = '广东||100000302||肇庆市皮肤病医院||国营||一级甲等';
        $hospArr[] = '浙江||100001708||乐清市人民医院||国营||三级乙等';
        $hospArr[] = '广州||100000072||广州市花都区人民医院||国营||三级乙等';
        $hospArr[] = '浙江||100016766||中国人民解放军第一一八医院||国营||三级乙等';
        $hospArr[] = '浙江||100016765||温州市第七人民医院（温州市心理卫生中心）||国营||三级乙等';
        $hospArr[] = '深圳||8||深圳市宝安区妇幼保健院||国营||三级医院';
        $hospArr[] = '深圳||3||深圳市宝安第二人民医院（集团）(原沙井人民医院)||国营||三级医院';
        $hospArr[] = '深圳||124||深圳市龙岗区妇幼保健院||国营||三级医院';
        $hospArr[] = '深圳||140||深圳市宝安区中心医院（原深圳市宝安区西乡人民医院）||国营||三级医院';
        $hospArr[] = '深圳||138||中国科学院大学深圳医院||国营||三级医院';
        $hospArr[] = '深圳||143||深圳市龙华区中心医院||国营||三级医院';
        $hospArr[] = '深圳||109||深圳市眼科医院||国营||三级医院';
        $hospArr[] = '深圳||161||南山区蛇口人民医院||国营||三级医院';
        $hospArr[] = '深圳||121||深圳市龙岗区人民医院||国营||三级医院';
        $hospArr[] = '深圳||129||深圳市南山区妇幼保健院||国营||三级医院';
        $hospArr[] = '深圳||127||深圳市罗湖区妇幼保健院||国营||三级医院';
        $hospArr[] = '深圳||100015578||深圳大学总医院||国营||三级医院';
        $hospArr[] = '深圳||130||南方科技大学医院||国营||三级医院';
        $hospArr[] = '深圳||139||深圳市龙华区人民医院||国营||三级医院';
        $hospArr[] = '深圳||200018651||南方医科大学深圳医院||国营||三级医院';
        $hospArr[] = '深圳||4||中山大学附属第八医院(深圳福田)||国营||三级医院';
        $hospArr[] = '深圳||155||深圳市龙岗区第三人民医院||国营||三级医院';
        $hospArr[] = '深圳||128||深圳市罗湖区中医院||国营||三级医院';
        $hospArr[] = '深圳||381||深圳市龙岗区耳鼻咽喉医院||国营||三级医院';
        $hospArr[] = '深圳||200020102||深圳市萨米医疗中心||国营||三级医院';
        $hospArr[] = '深圳||200017901||中国医学科学院阜外医院深圳医院(孙逸仙心血管医院)||国营||三级医院';
        $hospArr[] = '深圳||134||深圳市盐田区人民医院||国营||三级医院';
        $hospArr[] = '深圳||200008051||中国医学科学院肿瘤医院深圳医院||国营||三级医院';
        $hospArr[] = '深圳||200025359||中山大学附属第七医院||国营||三级医院';
        $hospArr[] = '深圳||200032202||深圳市宝安纯中医治疗医院||国营||三级医院';
        $hospArr[] = '武汉||200020801||国药东风口腔医院||国营||三级医院';
        $hospArr[] = '重庆||200011357||重庆医科大学附属大学城医院||国营||三级医院';
        $hospArr[] = '深圳||21||北京大学深圳医院||国营||三级甲等';
        $hospArr[] = '深圳||22||深圳市第二人民医院||国营||三级甲等';
        $hospArr[] = '深圳||113||深圳市宝安区人民医院||国营||三级甲等';
        $hospArr[] = '深圳||122||深圳市龙岗中心医院||国营||三级甲等';
        $hospArr[] = '深圳||104||深圳市第三人民医院||国营||三级甲等';
        $hospArr[] = '深圳||100||深圳市人民医院||国营||三级甲等';
        $hospArr[] = '深圳||114||深圳市宝安中医院（集团）||国营||三级甲等';
        $hospArr[] = '深圳||131||深圳市南山区人民医院||国营||三级甲等';
        $hospArr[] = '深圳||105||深圳市妇幼保健院||国营||三级甲等';
        $hospArr[] = '深圳||103||深圳市中医院||国营||三级甲等';
        $hospArr[] = '深圳||125||深圳市罗湖区人民医院||国营||三级甲等';
        $hospArr[] = '广州||100000081||广东省第二中医院||国营||三级甲等';
        $hospArr[] = '深圳||298||深圳市人民医院龙华分院||国营||三级甲等';
        $hospArr[] = '深圳||108||深圳市康宁医院||国营||三级甲等';
        $hospArr[] = '深圳||118||广州中医药大学深圳医院||国营||三级甲等';
        $hospArr[] = '深圳||355||北京中医药大学深圳医院（龙岗区中医院）||国营||三级甲等';
        $hospArr[] = '东莞||340||东莞市滨海湾中心医院(原东莞市第五人民医院)||国营||三级甲等';
        $hospArr[] = '广州||100000096||广州军区广州总医院附属一五七医院||国营||三级甲等';
        $hospArr[] = '东莞||200025651||东莞市妇幼保健院服务预约||国营||三级甲等';
        $hospArr[] = '合肥||100014603||安徽省中医院||国营||三级甲等';
        $hospArr[] = '深圳||190||深圳平乐骨伤科医院罗湖院区||国营||三级甲等';
        $hospArr[] = '东莞||268||东莞市妇幼保健院||国营||三级甲等';
        $hospArr[] = '合肥||100001020||安徽省立医院||国营||三级甲等';
        $hospArr[] = '长沙||100000417||湖南省醴陵市中医院||国营||三级甲等';
        $hospArr[] = '淮南||100002175||淮南市第一人民医院（寿县贫困人群大病门诊预约）||国营||三级甲等';
        $hospArr[] = '广州||319||南方医科大学南方医院||国营||三级甲等';
        $hospArr[] = '深圳||380||深圳平乐骨伤科医院坪山院区||国营||三级甲等';
        $hospArr[] = '重庆||100001263||重庆医科大学附属第一医院||国营||三级甲等';
        $hospArr[] = '武汉||100015867||国药东风总医院（东风汽车公司总医院）||国营||三级甲等';
        $hospArr[] = '广州||100000135||南方医科大学第三附属医院||国营||三级甲等';
        $hospArr[] = '东莞||272||东莞市松山湖中心医院||国营||三级甲等';
        $hospArr[] = '合肥||200005755||安徽省立医院南区||国营||三级甲等';
        $hospArr[] = '浙江||100001704||温州医科大学附属第二医院||国营||三级甲等';
        $hospArr[] = '浙江||100016763||温州市中西医结合医院（市儿童医院）||国营||三级甲等';
        $hospArr[] = '浙江||100001707||温州市人民医院||国营||三级甲等';
        $hospArr[] = '重庆||200003907||重庆新桥医院||国营||三级甲等';
        $hospArr[] = '贵州||200031452||遵义医科大学第二附属医院||国营||三级甲等';
        $hospArr[] = '浙江||100001709||温州医科大学附属眼视光医院||国营||三级甲等';
        $hospArr[] = '浙江||100016764||温州市中医院||国营||三级甲等';
        $hospArr[] = '长沙||100000046||湖南省中医药研究院附属医院||国营||三级甲等';
        $hospArr[] = '重庆||200011356||重庆市中医院（道门口）||国营||三级甲等';
        $hospArr[] = '广州||342||广东三九脑科医院 ||国营||三级甲等';
        $hospArr[] = '重庆||100001864||重庆三峡中心医院||国营||三级甲等';
        $hospArr[] = '山东||200005701||青岛大学附属医院（本部）||国营||三级甲等';
        $hospArr[] = '北京||200015052||北京中医药大学东直门医院国际部||国营||三级甲等';
        $hospArr[] = '山东||100001838||中国人民解放军第401医院||国营||三级甲等';
        $hospArr[] = '长沙||100000604||岳阳市二人民医院||国营||三级甲等';
        $hospArr[] = '山东||200005656||青岛市市立医院(本部)||国营||三级甲等';
        $hospArr[] = '合肥||200039201||安徽医科大学第一附属医院高新院区||国营||三级甲等';
        $hospArr[] = '重庆||200004755||北碚中医院(城南院区)||国营||三级甲等';
        $hospArr[] = '重庆||200031801||重庆医科大学附属第二医院(临江门院区)||国营||三级甲等';
        $hospArr[] = '合肥||100014583||滁州市第一人民医院||国营||三级甲等';
        $hospArr[] = '重庆||200011453||重庆医科大学附属第一医院金山医院||国营||三级甲等';
        $hospArr[] = '合肥||100002882||宿州市立医院||国营||三级甲等';
        $hospArr[] = '山东||200005706||青岛大学附属医院（黄岛院区）||国营||三级甲等';
        $hospArr[] = '广州||100000002||广州医科大学附属口腔医院||国营||三级甲等';
        $hospArr[] = '合肥||100001190||合肥市第一人民医院||国营||三级甲等';
        $hospArr[] = '山东||200005557||青岛大学附属医院（东区）||国营||三级甲等';
        $hospArr[] = '广州||100001950||中国人民解放军第458医院||国营||三级甲等';
        $hospArr[] = '广州||100000016||广州医学院第三附属医院||国营||三级甲等';
        $hospArr[] = '广州||200037715||广州市南沙中心医院||国营||三级甲等';
        $hospArr[] = '广州||100000029||广州市妇女儿童医疗中心(广州市妇婴医院院区) ||国营||三级甲等';
        $hospArr[] = '重庆||200031705||重庆市江津区中心医院||国营||三级甲等';
        $hospArr[] = '广州||200037720||广州中医药大学第三附属医院（芳村中医院）||国营||三级甲等';
        $hospArr[] = '广州||100000019||广州市胸科医院||国营||三级甲等';
        $hospArr[] = '广东||258||佛山市第一人民医院||国营||三级甲等';
        $hospArr[] = '广州||100000104||广州市第一人民医院||国营||三级甲等';
        $hospArr[] = '广东||29||惠州华康医院(惠州华康骨伤医院)||国营||三级甲等';
        $hospArr[] = '广东||100000385||汕头大学医学院第一附属医院||国营||三级甲等';
        $hospArr[] = '山东||200030203||青岛市皮肤病防治院||国营||三级甲等';
        $hospArr[] = '广州||100000862||广州市妇女儿童医疗中心(广州市儿童医院)||国营||三级甲等';
        $hospArr[] = '重庆||100017023||南川区人民医院||国营||三级甲等';
        $hospArr[] = '重庆||200031901||重庆医科大学附属第二医院(江南院区)||国营||三级甲等';
        $hospArr[] = '合肥||200049397||安徽省中医院（安中医附一）||国营||三级甲等';
        $hospArr[] = '山东||200030152||青岛同安妇婴医院||国营||三级甲等';
        $hospArr[] = '重庆||200031704||重庆医科大学附属康复医院(大公馆院区)||国营||三级甲等';
        $hospArr[] = '浙江||100016767||温州牙科医院||国营||二级乙等';
        $hospArr[] = '上海||200005151||上海市金山区亭林医院||国营||二级乙等';
        $hospArr[] = '浙江||100016810||温州延生堂中医门诊部||国营||二级乙等';
        $hospArr[] = '浙江||100016736||温州叶同仁综合门诊部||国营||二级乙等';
        $hospArr[] = '浙江||100016808||平阳县第二人民医院||国营||二级乙等';
        $hospArr[] = '广东||100016568||潮州市湘桥区人民医院||国营||二级乙等';
        $hospArr[] = '深圳||200014051||深圳市龙华区妇幼保健院||国营||二级医院';
        $hospArr[] = '深圳||145||深圳市福田区第二人民医院||国营||二级医院';
        $hospArr[] = '深圳||154||深圳市大鹏新区妇幼保健院||国营||二级医院';
        $hospArr[] = '深圳||147||深圳市龙岗区第六人民医院||国营||二级医院';
        $hospArr[] = '深圳||204||深圳市中医肛肠医院(福田)||国营||二级医院';
        $hospArr[] = '深圳||150||深圳市大鹏新区南澳人民医院||国营||二级医院';
        $hospArr[] = '深圳||397||深圳市龙岗区骨科医院||国营||二级医院';
        $hospArr[] = '东莞||200011301||东莞市东城医院||国营||二级医院';
        $hospArr[] = '淮南||100002255||淮南市第四人民医院||国营||二级医院';
        $hospArr[] = '淮南||200023053||淮南市第三人民医院||国营||二级医院';
        $hospArr[] = '东莞||200047904||东莞虎门南栅医院||国营||二级医院';
        $hospArr[] = '深圳||141||深圳市宝安区福永人民医院||国营||二级甲等';
        $hospArr[] = '深圳||325||深圳市宝安区松岗人民医院||国营||二级甲等';
        $hospArr[] = '深圳||152||深圳市坪山区人民医院||国营||二级甲等';
        $hospArr[] = '深圳||137||深圳市宝安区石岩人民医院||国营||二级甲等';
        $hospArr[] = '深圳||151||深圳市龙岗区第二人民医院||国营||二级甲等';
        $hospArr[] = '深圳||119||深圳市福田区妇幼保健院||国营||二级甲等';
        $hospArr[] = '东莞||314||东莞市东部中心医院||国营||二级甲等';
        $hospArr[] = '深圳||153||深圳市龙岗区第五人民医院(原平湖医院)||国营||二级甲等';
        $hospArr[] = '淮南||200015201||淮南市妇幼保健院||国营||二级甲等';
        $hospArr[] = '东莞||200025362||常平医院服务预约||国营||二级甲等';
        $hospArr[] = '东莞||328||东莞市黄江医院||国营||二级甲等';
        $hospArr[] = '上海||100003079||上海市静安区闸北中心医院||国营||二级甲等';
        $hospArr[] = '东莞||352||东莞市樟木头人民医院||国营||二级甲等';
        $hospArr[] = '成都||200011351||成都中医药大学第二附属医院||国营||二级甲等';
        $hospArr[] = '浙江||100016774||乐清中医院||国营||二级甲等';
        $hospArr[] = '浙江||100016772||乐清市第三人民医院||国营||二级甲等';
        $hospArr[] = '武汉||100001993||国药东风茅箭医院||国营||二级甲等';
        $hospArr[] = '东莞||307||东莞市中堂医院||国营||二级甲等';
        $hospArr[] = '广东||200037844||肇庆市高要区人民医院||国营||二级甲等';
        $hospArr[] = '广州||200057551||广州天河区中医院||国营||二级甲等';
        $hospArr[] = '淮南||100002242||淮南市中医院||国营||二级甲等';
        $hospArr[] = '重庆||200037395||重庆市第七人民医院||国营||二级甲等';
        $hospArr[] = '山东||200005560||城阳区第三人民医院||国营||二级甲等';
        $hospArr[] = '重庆||200041778||重庆市梁平区人民医院||国营||二级甲等';
        $hospArr[] = '长沙||200041263||永顺县人民医院||国营||二级甲等';
        $hospArr[] = '山东||200005714||莱西市市立医院||国营||二级甲等';

        $hospArr[] = '北京||200037414||北京大学首钢医院||国营||三级医院';
        $hospArr[] = '北京||200037420||中国人民解放军总医院京东医疗区||国营||二级甲等';
        $hospArr[] = '北京||200037428||北京航天总医院||国营||三级医院';
        $hospArr[] = '北京||200037465||清华大学附属垂杨柳医院||国营||三级医院';
        $hospArr[] = '北京||200037469||北京市海淀医院||国营||三级医院';
        $hospArr[] = '北京||200037484||首都医科大学附属北京世纪坛医院||国营||三级甲等';
        $hospArr[] = '北京||200037489||北京市东城区第一人民医院||国营||二级甲等';
        $hospArr[] = '广州||100000855||广州医科大学附属第二医院||国营||三级甲等';
        $hospArr[] = '广州||100000135||南方医科大学第三附属医院||国营||二级甲等';
        $hospArr[] = '广州||200005957||中山大学附属第六医院||国营||三级甲等';

        $hospArr[] = '重庆||200031703||重庆长寿区中医院||国营||二级甲等';
        $hospArr[] = '重庆||200031855||重庆市壁山区人民医院||国营||三级甲等';
        $hospArr[] = '重庆||100002932||重庆市长寿区人民医院||国营||三级甲等';

        $hospArr[] = '河南||100014803||河南省胸科医院||国营||三级甲等';
        $hospArr[] = '河南||100015603||黄河中心医院||国营||二级甲等';
        $hospArr[] = '河南||100016203||河南省人民医院||国营||三级甲等';
        $hospArr[] = '河南||100016523||郑州大学第五附属医院||国营||三级甲等';
        $hospArr[] = '河南||200041155||郑州市第十六人民医院||民营||二级甲等';
        $hospArr[] = '河南||200050607||郑州大桥医院（彩超室）||民营||二级医院';
        $hospArr[] = '河南||200052107||郑州人民医院（南院区）||国营||三级甲等';
        $hospArr[] = '河南||200052551||郑州友好肝胆医院||民营||二级医院';
        $hospArr[] = '河南||200054551||河南中都中医皮肤病医院||民营||二级医院';
        $hospArr[] = '河南||200070751||郑州民生耳鼻喉医院||民营||二级医院';
        $hospArr[] = '河南||100001965||许昌市中心医院||国营||三级甲等';
        $hospArr[] = '河南||100001970||鹤壁市人民医院||国营||三级甲等';
        $hospArr[] = '河南||100001975||河南科技大学第一附属医院||国营||三级甲等';
        $hospArr[] = '河南||100001977||河南科技大学第二附属医院||国营||三级甲等';
        $hospArr[] = '河南||100001979||河南科技大学第五附属医院（洛阳市第五人民医院）||国营||三级丙等';
        $hospArr[] = '河南||100001980||洛阳市第三人民医院||国营||三级丙等';
        $hospArr[] = '河南||100002033||漯河市中心医院||国营||三级甲等';
        $hospArr[] = '河南||100002111||安阳市第六人民医院||国营||三级丙等';
        $hospArr[] = '河南||100002143||南阳市中心医院||国营||三级甲等';
        $hospArr[] = '河南||100002145||南阳医学高等专科学校第一附属医院||国营||三级甲等';
        $hospArr[] = '河南||100002159||南阳市第二人民医院||国营||三级甲等';
        $hospArr[] = '河南||100002161||商丘市第一人民医院||国营||三级甲等';
        $hospArr[] = '河南||100002166||新乡市中心医院||国营||三级甲等';
        $hospArr[] = '河南||100002245||信阳市中心医院||国营||三级甲等';
        $hospArr[] = '河南||200097273||郑州长峰医院||民营||一级医院';
        $hospArr[] = '河南||200098655||郑州博大泌尿外科医院||民营||一级医院';
        $hospArr[] = '河南||100000830||郑州市中医院||国营||三级甲等';
        $hospArr[] = '河南||100000832||郑州人民医院||国营||三级甲等';
        $hospArr[] = '河南||100000834||郑州市第二人民医院||国营||二级甲等';
        $hospArr[] = '河南||100000835||郑州市第三人民医院||国营||三级医院';
        $hospArr[] = '河南||100000836||河南省儿童医院（郑东院区）||国营||三级甲等';
        $hospArr[] = '河南||100000838||郑州市妇幼保健院||国营||三级医院';
        $hospArr[] = '河南||100000839||郑州市第六人民医院||国营||三级医院';
        $hospArr[] = '河南||100000841||郑州市骨科医院||国营||三级甲等';
        $hospArr[] = '河南||100000842||郑州市第九人民医院||国营||三级医院';
        $hospArr[] = '河南||100000844||郑州大学第二附属医院||国营||三级甲等';
        $hospArr[] = '河南||100000849||郑州市第八人民医院（郑州市精神卫生中心）||国营||二级医院';
        $hospArr[] = '河南||100000852||河南省直第三人民医院||国营||三级医院';
        $hospArr[] = '河南||100000854||河南大学附属郑州颐和医院||民营||三级医院';
        $hospArr[] = '河南||200006234||新郑市第二人民医院||国营||二级医院';
        $hospArr[] = '河南||200006802||河南省肿瘤医院||国营||三级甲等';
        $hospArr[] = '河南||200009951||中国人民解放军联勤保障部队第九八八医院（西区）||国营||三级甲等';
        $hospArr[] = '河南||200010401||南阳卧龙医院||国营||二级甲等';
        $hospArr[] = '河南||200011604||郑州人民医院（东院区）||国营||三级甲等';
        $hospArr[] = '河南||200021762||郑州市管城中医院||国营||二级甲等';
        $hospArr[] = '河南||200021952||镇平县人民医院||国营||三级乙等';
        $hospArr[] = '河南||200022652||禹州市人民医院||国营||二级甲等';
        $hospArr[] = '河南||200025854||商丘市第五人民医院||国营||二级甲等';
        $hospArr[] = '河南||200027411||阜外华中心血管病医院||国营||三级甲等';
        $hospArr[] = '河南||200043343||登封市人民医院||国营||三级乙等';
        $hospArr[] = '河南||200063860||郑州长江中医院||民营||二级医院';
        $hospArr[] = '河南||200063877||郑州国医堂医院||民营||一级医院';
        $hospArr[] = '河南||200064202||郑州圣玛妇产医院||民营||二级医院';
        $hospArr[] = '河南||200083754||郑州市二七区人民医院||国营||一级医院';
        $hospArr[] = '河南||100018365||南阳市胸科医院||国营||三级丙等';
        $hospArr[] = '河南||200000753||河南省西平县人民医院||国营||二级甲等';
        $hospArr[] = '河南||200001401||南阳市第一人民医院||国营||三级甲等';
        $hospArr[] = '河南||200001802||河南省中医院（河南中医药大学第二附属医院）||国营||三级甲等';
        $hospArr[] = '河南||200002855||河南大学淮河医院||国营||三级甲等';
        $hospArr[] = '河南||200002904||南阳南石医院||国营||三级甲等';
        $hospArr[] = '河南||200003204||河南中医药大学第一附属医院||国营||三级甲等';
        $hospArr[] = '河南||200012802||郑州市中心医院||国营||三级甲等';
        $hospArr[] = '河南||200015552||郑州大桥医院||民营||二级医院';
        $hospArr[] = '河南||200017702||河南中医药大学第一附属医院豫东医院（睢县中医院）||国营||二级甲等';
        $hospArr[] = '河南||200018051||中国人民解放军联勤保障部队第九八八医院（空军医院）||国营||三级甲等';
        $hospArr[] = '河南||200059883||郑州市精神病防治医院||国营||一级甲等';
        $hospArr[] = '河南||200062011||郑州南区口腔医院||民营||二级医院';
        $hospArr[] = '河南||200062801||河南省儿童医院（东三街院区+南院区+西院区）||国营||三级甲等';
        $hospArr[] = '河南||200067104||郑州美中商都妇产医院||民营||二级医院';
        $hospArr[] = '河南||200101162||郑州中医骨伤病医院||民营||三级甲等';
        $hospArr[] = '河南||200101179||郑州北方医院||民营||一级医院';
        $hospArr[] = '河南||200101190||河南天佑中西医结合肿瘤医院||民营||三级医院';
        $hospArr[] = '河南||200101199||郑州誉美医院||民营||二级医院';
        $hospArr[] = '河南||200101255||河南三博脑科医院||民营||三级医院';
        $hospArr[] = '河南||200101268||河南曙光肛肠医院||民营||二级医院';
        $hospArr[] = '河南||200101269||郑州丰益肛肠医院||民营||一级医院';
        $hospArr[] = '河南||200101567||郑州万安妇产医院||民营||二级医院';

        return $hospArr;
    }
}
