<?php

namespace console\controllers;

use common\libs\CommonFunc;
use common\models\BuildToEsModel;
use common\models\Department;
use common\models\DoctorEsModel;
use common\models\DoctorModel;
use common\models\DoctorInfoModel;
use common\models\GuahaoCooListModel;
use common\models\GuahaoHospitalModel;
use common\models\GuahaoOrderInfoModel;
use common\models\GuahaoOrderModel;
use common\models\GuahaoPlatformModel;
use common\models\GuahaoPlatformRelationHospitalModel;
use common\models\GuahaoScheduleModel;
use common\models\HospitalDepartmentRelation;
use common\models\TbDepartmentThirdPartyRelationModel;
use common\models\TbDoctorThirdPartyRelationModel;
use common\models\TmpDepartmentThirdPartyModel;
use common\models\TmpDoctorThirdPartyModel;
use common\models\TmpGuahaoHospitalDepartmentRelation;
use common\sdks\CenterSDK;
use common\sdks\PaySdk;
use common\sdks\snisiya\SnisiyaSdk;
use Matrix\Exception;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use console\models\TbTmpDoctorThirdPartyTmp;
use console\models\TbTmpDepartmentThirdPartyTmp;
use common\models\TbLog;
use common\models\GuahaoPlatformListModel;

class GuahaoController extends \yii\console\Controller
{
    public $start_time;

    public $pagesize  = 500;
    public $platform = [];
    //每个挂号平台对应的接口请求分页数  0表示第三方接口无分页(需要用到 来判断导入医生请求医生接口跳出分页循环)
    public $tp_plat_pagesize = [
        1 => 0,//henan-河南挂号
        2 => 0,//nanjing-南京挂号
        5 => 0,//jiankang160-健康160
        6 => 0,//nisiya-王氏医生加号
        7 => 1000,//shaanxi-陕西挂号
        8 => 0,//shanxi-山西挂号
        9 => 100,//jiankangzhilu-福建健康之路
        10 => 0,//tianjin-天津
    ];

    public function init()
    {
        parent::init();
        $this->start_time = microtime(true);
        //$this->platform = GuahaoPlatformListModel::getPlatformType();
    }

    /**
     * getSdk
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/10/30
     */
    public function getSdk($tp_platform = '')
    {
        $sdkInfo = GuahaoPlatformListModel::getPlatformSdk($tp_platform);
        if (empty($sdkInfo['sdk']) || empty($sdkInfo['tp_platform'])) {
            $arr = array_keys($this->platform);
            die('来源错误可选值为:' . implode(',', $arr) . "\n\r");
        }
        $common = "common\sdks\guahao\\" . ucfirst($sdkInfo['sdk']);
        $con = new $common($sdkInfo['tp_platform'], '');
        return $con;
    }

    /**
     * 获取医院
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/10/30
     */
    public function actionGetTpHospital($tp_platform = '')
    {


        //先定义一个数组，循环数组，依次假设当前的为红酒，因为下午卖的升数是上午卖的2倍，所以上午加下午卖的升数为3的倍数，即可得到以下
        $arr = [30,32,36,38,40,62];
        for ($i = 0; $i < count($arr); $i ++) {
            $tmp = $arr;
            unset($tmp[$i]);
            if (array_sum($tmp) % 3 == 0) {
                echo "红酒的升数为：" . $arr[$i];
            }
        }
        //红酒为40L的那桶，30L,32L,36L,38L,62L的为白酒
        die;




        while (count($arr) > 1) {
            $item = array_shift($arr);
            foreach ($arr as $value) {
                $tmp[] = [$item, $value];
            }
        }






        $arr = ['a', 'b', 'c', 'd', 'e'];
        $tmp = [];
        while (count($arr) > 1) {
            $item = array_shift($arr);
            foreach ($arr as $value) {
                $tmp[] = [$item, $value];
            }
        }
        print_r($tmp);die;




        $this->getSdk($tp_platform)->actionGetTpHospital();
    }

    /**
     * 获取医院数据 （只拉取一次）
     * @author wanghongying<wanghongying@yuanxinjituan.com>
     * @date 2022/05/15
     */
    public function actionGetTpHospitalOld($tp_platform = '')
    {
        $this->getSdk($tp_platform)->actionGetTpHospitalOld();
    }

    /**
     * 获取第三方科室
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-11-05
     * @version 1.0
     * @param   string     $tp_platform      [description]
     * @param   string     $tp_hospital_code [description]
     * @return  [type]                       [description]
     */
    public function actionGetDepartment($tp_platform = '', $tp_hospital_code = '')
    {
        $this->getSdk($tp_platform)->pullDepartment($tp_hospital_code);
    }

    /**
     * 根据科室获取医生
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date    2021-09-18
     * @version v1.0
     * @param   string     $tp_platform
     * @return  [type]     [description]
     */
    public function actionGetTpDoctor($tp_platform = '', $tp_hospital_code = '', $department_id = '')
    {
        $getSdkModel = $this->getSdk($tp_platform);
        $tp_platform_num = $this->platform[$tp_platform];
        $platformSize = isset($this->tp_plat_pagesize[$tp_platform_num]) ? $this->tp_plat_pagesize[$tp_platform_num] : 0;
        //获取医院列表
        $hospitalQuery = GuahaoHospitalModel::find()->where(['tp_platform' => $tp_platform_num]);
        $hospitalQuery->andWhere(['<>', 'tp_hospital_code', '']);
        $hospitalQuery->andWhere(['<>', 'hospital_id', 0]);
        $hospitalQuery->andWhere(['=', 'status', 1]);
        if (!empty($tp_hospital_code)) {
            $hospitalQuery->andWhere(['IN', 'tp_hospital_code', explode(',', $tp_hospital_code)]);
        }
        $hospitalList = $hospitalQuery->asArray()->all();
        if (empty($hospitalList)) {
            echo '没有符合条件的关联医院数据！' . PHP_EOL;die;
        }
        $execute_num = 0;
        $success_num = 0;
        foreach ($hospitalList as $hospital) {
            $hospitalCache = \common\models\BaseDoctorHospitals::HospitalDetail($hospital['hospital_id']);
            if (empty($hospitalCache)) {
                echo '医院:' . $hospital['hospital_name'] . '---->没有设置医院详情缓存' . PHP_EOL;
                continue;
            }
            $hospital['hospital_type'] = $hospitalCache['kind'] == '公立' ? 1 : 2;
            $where = [
                'tp_platform' => $tp_platform_num,
                'tp_hospital_code' => $hospital['tp_hospital_code'],
            ];
            if (!empty($department_id)) {
                $where['tp_department_id'] = $department_id;
            }
            $total = TbDepartmentThirdPartyRelationModel::find()->where($where)->count();
            echo '医院:' . $hospital['hospital_name'] . '已关联第三方科室数量:total--' . $total . PHP_EOL;

            if ($total) {
                for ($i = 0; $i < ceil($total / $this->pagesize); $i++) {
                    $offset = $i * $this->pagesize;
                    //科室
                    $depData = TbDepartmentThirdPartyRelationModel::find()->where($where)->offset($offset)->limit($this->pagesize)->asArray()->all();
                    if ($depData) {
                        foreach ($depData as $v) {
                            //获取科室关联的王氏信息
                            $hospitalRelationInfo = HospitalDepartmentRelation::find()->where(['id' => $v['hospital_department_id']])->asArray()->one();
                            if (empty($hospitalRelationInfo)) {
                                echo "[" . date('Y-m-d H:i:s') . "] 第三方科室id:" . $v['tp_department_id'] . " 没有关联王氏医院科室！" . PHP_EOL;
                                continue;
                            }
                            $pageindex = 1;
                            do {
                                //医院 科室获取医生
                                $params = [
                                    'tp_platform' => $tp_platform_num,
                                    'tp_hospital_code' => $hospital['tp_hospital_code'],
                                    'tp_department_id' => $v['tp_department_id'],
                                    'page' => $pageindex,
                                    'pagesize' => $platformSize,
                                ];
                                $docList = $getSdkModel->actionGetTpDoctor($params);
                                if ($docList) {
                                    foreach ($docList as &$doc) {
                                        $doc['tp_doctor_id'] = strval($doc['tp_doctor_id']);
                                        $doc['realname'] = CommonFunc::filterContent($doc['realname']);
                                        $doc['job_title'] = (strlen($doc['job_title']) > 20) ? "未知" : $doc['job_title'];
                                        //科室id或科室名称为空的时候不拉取
                                        if (empty($doc['tp_doctor_id']) || empty($doc['realname'])) {
                                            echo "医生id或医生名称不合法" . PHP_EOL;
                                            continue;
                                        }
                                        $has_demo =  CommonFunc::isDemoDoctor($doc['realname']);
                                        if ($has_demo) {
                                            echo '[医生]' . $doc['realname'] . "-测试医生过滤" . PHP_EOL;
                                            continue;
                                        }
                                        $doc = $this->formatDoc($doc, $hospital, $v['tp_department_id'], $hospitalRelationInfo);
                                        $doc['tp_platform'] = $tp_platform_num;
                                        $doc['tp_primary_id'] = isset($doc['tp_primary_id']) ? $doc['tp_primary_id'] : '';
                                        $doc['profile'] = isset($doc['profile']) ? $doc['profile'] : '';
                                        $execute_num++;
                                        $docResult = DoctorModel::autoImportDoctor($doc);
                                        if ($docResult['code'] == 200) {
                                            $success_num++;
                                            echo "医院:{$hospital['hospital_name']} 医院id:{$hospital['tp_hospital_code']} 科室:{$v['tp_department_id']}---->医生{$doc['tp_doctor_id']}--{$doc['realname']}-->完成" . PHP_EOL;
                                        } else {
                                            echo "医院:{$hospital['hospital_name']} 医院id:{$hospital['tp_hospital_code']} 科室:{$v['tp_department_id']}---->医生{$doc['tp_doctor_id']}--{$doc['realname']}-->" . $docResult['msg'] . PHP_EOL;
                                            continue;
                                        }
                                    }
                                } else {
                                    $int_page = (int)($i+1);
                                    echo ("第{$int_page}页 医院:{$hospital['hospital_name']} 医院id:{$hospital['tp_hospital_code']} 科室:{$v['tp_department_id']} " . date('Y-m-d H:i:s', time())) . '暂无医生数据' . PHP_EOL;
                                }
                                $pageindex++;
                                //不需要循环分页的挂号平台直接跳出当前分页循环
                                if ($platformSize <= 0) break;
                            } while (!empty($docList));
                        }
                    } else {
                        echo "医院:{$hospital['hospital_name']} 医院id:{$hospital['tp_hospital_code']}--->没有关联科室数据了" . PHP_EOL;
                    }
                    echo ("医院:{$hospital['hospital_name']} 医院id:{$hospital['tp_hospital_code']} 第{$i}页" . date('Y-m-d H:i:s', time())) . '处理完成！' . PHP_EOL;
                }
            }
        }
        echo "处理数量：{$execute_num}" . PHP_EOL;
        echo "成功数量：{$success_num}" . PHP_EOL;
        echo 'doctor:生成医生完成' . PHP_EOL;
    }

    /**
     * @param $doc
     * @param $hospital
     * @param $tp_department_id
     * @param $hospitalRelationInfo
     * @return mixed
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2021-09-22
     */
    public function formatDoc(&$doc, $hospital, $tp_department_id, $hospitalRelationInfo)
    {
        $doc['tp_hospital_code'] = $hospital['tp_hospital_code'];
        $doc['hospital_id'] = $hospital['hospital_id'];
        $doc['hospital_name'] = $hospital['hospital_name'];
        $doc['hospital_type'] = $hospital['hospital_type'];
        $doc['tp_department_id'] = $tp_department_id ?? "";
        $doc['frist_department_id'] = $hospitalRelationInfo['frist_department_id'] ?? 0;
        $doc['second_department_id'] = $hospitalRelationInfo['second_department_id'] ?? 0;
        $doc['frist_department_name'] = $hospitalRelationInfo['frist_department_name'] ?? "";
        $doc['second_department_name'] = $hospitalRelationInfo['second_department_name'] ?? "";
        $doc['miao_frist_department_id'] = $hospitalRelationInfo['miao_frist_department_id'] ?? 0;
        $doc['miao_second_department_id'] = $hospitalRelationInfo['miao_second_department_id'] ?? 0;
        $doc['tp_frist_department_id'] = "";
        return $doc;
    }

    /**
     * 删除平台医生es数据
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-11-11
     * @version 1.0
     * @param   string     $tp_platform [description]
     * @return  [type]                  [description]
     */
    public function actionDelEsDoctor($tp_platform = '', $hospital_id = 0)
    {
        if (!array_key_exists($tp_platform, $this->platform)) {
            die("来源错误\n\r");
        }
        $hosWhere = [
            'tp_platform' => $this->platform[$tp_platform],
        ];
        if ($hospital_id) {
            $hosWhere['hospital_id'] = $hospital_id;
        }
        $hosids = GuahaoHospitalModel::find()
            ->select('hospital_id')
            ->where($hosWhere)
            ->andWhere(['<>', 'hospital_id', 0])
            ->column();
        if (!$hosids) {
            die("来源医院不存在\n\r");
        }
        foreach ($hosids as $item) {
            $model                   = new DoctorEsModel();
            $where                   = [];
            $where['bool']['must'][] = [
                'term' => [
                    'hospital_id' => $item,
                ],
            ];
            $where['bool']['must'][] = [
                'range' => [
                    'doctor_id' => [
                        'gt' => 0,
                    ],
                ],
            ];
            $page  = 1;
            $limit = 500;
            do {
                $offset     = max(0, ($page - 1)) * $limit;
                $doctorlist = $model->find()->where([])->query($where)->offset($offset)->limit($limit)->orderBy('doctor_id asc')->all();
                if (!$doctorlist) {
                    echo '医院id' . $docItem['hospital_id'] . "--医生删除es数据成功\n\r";
                    break;
                }
                if ($doctorlist) {
                    foreach ($doctorlist as $k => $docItem) {
                        $res = DoctorModel::find()->select('doctor_id')->where(['doctor_id' => $docItem['doctor_id'], 'hospital_id' => $docItem['hospital_id']])->one();
                        if (!$res) {
                            DoctorEsModel::deleteDoctorEsData($docItem['doctor_id']);
                            $key = sprintf(Yii::$app->params['cache_key']['hospital_doctor_info'], $docItem['doctor_id']);
                            CommonFunc::setCodisCache($key, '');
                        }
                        echo '第' . $page . '页 医院id' . $docItem['hospital_id'] . '医生id ' . $docItem['doctor_id'] . "--删除es数据成功\n\r";
                    }
                }
                $docNum = count($doctorlist);
                unset($doctorlist);
                $page++;
            } while ($docNum > 0);
            unset($where, $model, $page, $limit, $offset, $has_parent);
        }
        echo "删除成功\n\r";
    }

    /**
     * 更新王氏医院id
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/9/27
     */
    // public function actionUpdateHospitalId($tp_platform = '')
    // {
    //     $this->getSdk($tp_platform)->actionUpdateHospitalId();
    // }


    /**
     * 更新插入平台医院新的对应科室关系
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-30
     * @version 1.0
     * @param   string     $value [description]
     */
    // public function actionUpdateDepartmentRelation($tp_platform = '')
    // {
    //     $this->getSdk($tp_platform)->updateDepartmentRelation();
    // }

    /**
     * 更新医生科室关系
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/10/30
     */
    // public function actionUpdateKeshi($tp_platform = '', $page = 1, $pageSize = 1000)
    // {
    //     if (!array_key_exists($tp_platform, $this->platform)) {
    //         die("来源错误\n\r");
    //     }
    //     do {
    //         $offset = max(($page - 1), 0) * $pageSize;
    //         $ress   = DoctorModel::find()->select('frist_department_id,second_department_id')
    //             ->where(['miao_frist_department_id' => 0, 'miao_second_department_id' => 0, 'tp_platform' => $this->platform[$tp_platform]])
    //             ->andWhere(['<>', 'frist_department_id', 0])
    //             ->offset($offset)->limit($pageSize)
    //             ->asArray()->all();

    //         if (empty($ress)) {
    //             echo '没有数据了---' . "\n";
    //             break;
    //         }
    //         foreach ($ress as $item) {
    //             $keshiInfo = HospitalDepartmentRelation::find()
    //                 ->select('frist_department_id,second_department_id,miao_frist_department_id,miao_second_department_id')
    //                 ->where([
    //                     'frist_department_id'  => $item['frist_department_id'],
    //                     'second_department_id' => $item['second_department_id'],
    //                     'tp_platform'          => $this->platform[$tp_platform],
    //                 ])
    //                 ->andWhere(['<>', 'miao_frist_department_id', 0])->asArray()->one();

    //             if ($keshiInfo) {
    //                 DoctorModel::updateAll([
    //                     'miao_frist_department_id'  => $keshiInfo['miao_frist_department_id'],
    //                     'miao_second_department_id' => $keshiInfo['miao_second_department_id'],
    //                 ], [
    //                     'frist_department_id'  => $item['frist_department_id'],
    //                     'second_department_id' => $item['second_department_id'],
    //                 ]);
    //                 echo $keshiInfo['miao_frist_department_id'] . '--' . $keshiInfo['miao_second_department_id'] . " -成功\n\r";
    //             }
    //         }
    //         $page++;
    //     } while (count($ress) > 0);
    // }

    /**
     * 更新头像地址
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/9/27
     */
    public function actionUpdateImg($tp_platform = '', $doctor_id = 0, $page = 1, $pageSize = 500)
    {
        ///data/upload/user_avatar/doctor_avatar/
        if (!array_key_exists($tp_platform, $this->platform)) {
            die("来源错误\n\r");
        }

        $where = [
            'tp_platform' => $this->platform[$tp_platform],
        ];
        if ($doctor_id) {
            $where['doctor_id'] = $doctor_id;
        }
        //根据数量判断需要多少文件夹，
        $query   = DoctorModel::find()->where($where)->andWhere(['<>','avatar',''])->asArray()->all();
        $folderArr = $this->actionRange(count($query));
        // 生成的文件夹数量
        $n = count($folderArr);

        do {
            $offset = max(($page - 1), 0) * $pageSize;
            /*$ress   = TbDoctorThirdPartyRelationModel::find()->where($where)
                ->offset($offset)->limit($pageSize)->asArray()->all();*/
            $ress   = DoctorModel::find()->where($where)->offset($offset)->limit($pageSize)->asArray()->all();
            if (empty($ress)) {
                echo '没有数据了---' . "\n";
                break;
            }
            foreach ($ress as $value) {
                $itemObj = DoctorModel::findOne($value['doctor_id']);
                if (empty($itemObj) || !empty($itemObj->avatar) || empty($itemObj->source_avatar)) {
                    continue;
                }

                if ((strpos($itemObj->source_avatar, 'http')) !== false) {
                    //上传头像
                    // 根据id % $n 获取相应的文件夹地址
                    $num      = ceil($itemObj->doctor_id % $n);
                    $fileDate = $folderArr[$num];
                    $img = CommonFunc::uploadImageOssByUrl($itemObj->source_avatar, $fileDate);

                    if (!$img['img_path']) {
                        echo $itemObj->doctor_id . " -生成头像失败!". PHP_EOL;
                        continue;
                    }
                     //存储头像路径
                    $itemObj->avatar = $img['img_path'];
                    if ($itemObj->save()) {
                        echo '当前数据 doctor_id : 【' .$itemObj->doctor_id.'】' . '分配的头像日期文件夹是：【'.$fileDate.'】'. PHP_EOL;
                        echo '返回的图片全路径地址是： : ' . strval($img['img_url']). PHP_EOL;
                        echo $itemObj->doctor_id . '--' . $img['img_path'] . " -生成头像成功". PHP_EOL. PHP_EOL;
                    }
                }
                usleep(rand(50, 200));
            }
            $page++;
            $num = count($ress);
            unset($ress);
        } while ($num > 0);
        echo "[" . date('Y-m-d H:i:s') . "] " . "完成！". PHP_EOL;
    }

    /**
     * 更新医生状态 是否关联医院线医生
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/10/30
     */
    // public function actionUpdateDoctorStatus($page = 1, $pageSize = 1000)
    // {
    //     do {
    //         $offset = max(($page - 1), 0) * $pageSize;
    //         $ress   = DoctorModel::find()->select('miao_doctor_id')
    //             ->where(['<>', 'miao_doctor_id', 0])
    //             ->offset($offset)->limit($pageSize)
    //             ->asArray()->all();

    //         if (empty($ress)) {
    //             echo '没有数据了---' . "\n";
    //             break;
    //         }

    //         foreach ($ress as $item) {
    //             $is_hospital_project = ($item['miao_doctor_id'] == 0) ? 0 : 1;
    //             $ress                = CenterSDK::getInstance()->updateuser(['doctor_id' => $item['miao_doctor_id'], 'params' => json_encode(['is_hospital_project' => $is_hospital_project])]);
    //             if ($ress) {
    //                 echo "[" . $item['miao_doctor_id'] . "] " . '----' . $is_hospital_project . "更新成功！\n";
    //             }
    //         }
    //         $page++;
    //     } while (count($ress) > 0);
    //     echo "[" . date('Y-m-d H:i:s') . "] " . "完成！\n";
    // }

    /**
     * 生成来源科室平台初始化  暂停使用
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-02-23
     * @version 1.0
     * @param   string     $tp_platform [description]
     * @return  [type]                  [description]
     */
    public function actionGenerateDepartment($tp_platform = '', $hospital_id = 0)
    {
        if (!array_key_exists($tp_platform, $this->platform)) {
            die("来源错误\n\r");
        }

        $tp_platform = $this->platform[$tp_platform];
        $page        = 1;
        $limit       = 1000;
        $where       = [
            'tp_platform' => $tp_platform,
        ];
        if ($hospital_id) {
            $where['hospital_id'] = $hospital_id;
        }
        //获取王氏一二级科室id和名称
        $miao_fkeshi_list = array_column(CommonFunc::getFkeshiInfos(), 'name', 'id');
        $miao_skeshi_list = array_column(CommonFunc::getSkeshiInfos(), 'name', 'id');
        $query = TmpGuahaoHospitalDepartmentRelation::find()->where($where)->andWhere(['>', 'hospital_id', 0]);
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
                $relationInfo                       = HospitalDepartmentRelation::find()->where([
                    'hospital_id'            => $value['hospital_id'],
                    'frist_department_name'  => $value['tp_frist_department_name'],
                    'second_department_name' => $value['tp_second_department_name'],
                ])->asArray()->one();
                if ($relationInfo) {
                    echo $value['id'] . (date('Y-m-d H:i:s', time())) . '医院id ' . $value['hospital_id'] . ' 科室 ' . $value['tp_second_department_name'] . '已存在！' . PHP_EOL;
                    continue;
                }
                //检测一级科室或二级科室是否为空
                if (empty($value['tp_frist_department_name']) || empty($value['tp_second_department_name'])) {
                    echo $value['id'] . '--' . (date('Y-m-d H:i:s', time())) . '医院id ' . $value['hospital_id'] . ' 一级科室名称或二级科室名称为空！' . PHP_EOL;
                    continue;
                }
                //验证王氏科室一/二级科室id和王氏一、二级科室名称是否对应
                $miaofkname = (isset($miao_fkeshi_list[$value['miao_frist_department_id']]) && !empty($miao_fkeshi_list[$value['miao_frist_department_id']])) ? $miao_fkeshi_list[$value['miao_frist_department_id']]  : "";
                $miaoskname = (isset($miao_skeshi_list[$value['miao_second_department_id']]) && !empty($miao_skeshi_list[$value['miao_second_department_id']])) ? $miao_skeshi_list[$value['miao_second_department_id']] : "";
                if ($miaofkname != $value['miao_frist_department_name']) {
                    echo $value['id'] . '--' . (date('Y-m-d H:i:s', time())) . '医院id ' . $value['hospital_id'] . ' 王氏一级科室id和王氏一级名称对应不上！' . PHP_EOL;
                    continue;
                }
                if ($miaoskname != $value['miao_second_department_name']) {
                    echo $value['id'] . '--' . (date('Y-m-d H:i:s', time())) . '医院id ' . $value['hospital_id'] . ' 王氏二级科室id和王氏二级名称对应不上！' . PHP_EOL;
                    continue;
                }
                $transition = Yii::$app->getDb()->beginTransaction();
                try {
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
                        //$fkeshiModek->relation_department_id = 0;
                        //$fkeshiModek->source_department_id   = 0;
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
                        //$skeshiModek->relation_department_id = 0;
                        //$skeshiModek->source_department_id   = 0;
                        $skeshiModek->create_time     = time();
                        $res                          = $skeshiModek->save();
                        if ($res) {
                            $department_id                       = $skeshiModek->attributes['department_id'];
                            $relationModel->second_department_id = $department_id;
                        } else {
                            $relationModel->second_department_id = 0;
                        }
                    }
                    $relationModel->frist_department_name     = $value['tp_frist_department_name'];
                    $relationModel->second_department_name    = $value['tp_second_department_name'];
                    $relationModel->hospital_id               = $value['hospital_id'];
                    $relationModel->miao_frist_department_id  = $value['miao_frist_department_id'];
                    $relationModel->miao_second_department_id = $value['miao_second_department_id'];
                    $relationModel->doctors_num               = (int) $value['doctors_num'];
                    $relationModel->create_time               = time();
                    $relationModel->admin_id                  = 0;
                    $relationModel->admin_name                = 'system';
                    $relationModel->related_disease           = '';
                    $depament_res                             = $relationModel->save();
                    if ($depament_res) {
                        ##回传临时科室关联id
                        $tmp_deparment_where = [
                            'tp_platform'      => $tp_platform,
                            'tp_department_id' => $value['tp_department_id'],
                            'tp_hospital_code' => $value['tp_hospital_id'],
                        ];
                        $tmp_deparment_info = TmpDepartmentThirdPartyModel::find()->where($tmp_deparment_where)->one();
                        if ($tmp_deparment_info) {
                            $tmp_deparment_info->third_fkname           = $value['tp_frist_department_name'];
                            $tmp_deparment_info->third_skname           = $tmp_deparment_info->department_name;
                            $tmp_deparment_info->hospital_department_id = $relationModel->attributes['id'];
                            $tmp_deparment_info->is_relation            = 1;
                            $tmp_deparment_info->save();
                            $tmp_deparment_relation_info = TbDepartmentThirdPartyRelationModel::find()->where([
                                'tp_platform'            => $tmp_deparment_info->tp_platform,
                                'tp_department_id'       => $tmp_deparment_info->tp_department_id,
                                'hospital_department_id' => $tmp_deparment_info->hospital_department_id,
                            ])->one();
                            if (!$tmp_deparment_relation_info) {
                                $tmp_deparment_relation                         = new TbDepartmentThirdPartyRelationModel();
                                $tmp_deparment_relation->hospital_department_id = $tmp_deparment_info->hospital_department_id;
                                $tmp_deparment_relation->tp_platform            = $tmp_deparment_info->tp_platform;
                                $tmp_deparment_relation->tp_department_id       = $tmp_deparment_info->tp_department_id;
                                $tmp_deparment_relation->create_time            = time();
                                $tmp_deparment_relation->status                 = 1;
                                $tmp_deparment_relation->save();
                            }
                        }
                        $transition->commit();
                        echo '第' . $value['id'] . '--' . $value['tp_second_department_name'] . '  科室保存成功！' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                    }
                } catch (\Exception $e) {
                    $transition->rollBack();
                    $msg = $e->getMessage();
                    \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 科室导入失败');
                    echo '第' . $value['id'] . '--' . $value['tp_second_department_name'] . " 导入失败：{$msg}！" . date('Y-m-d H:i:s') . PHP_EOL;
                }

                unset($value);
                unset($relationModel, $transition);
            }
            $num = count($keshi_list);
            unset($keshi_list);
            $page++;
        } while ($num > 0);

        echo "任务" . date('Y-m-d H:i:s') . "完成！\n";

    }

    /**
     * 获取耗时
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-11-10
     * @version 1.0
     */
    public function afterAction($action, $result)
    {
        $end_time  = microtime(true);
        $diff_time = round(($end_time - $this->start_time) / 60, 2);
        if ($diff_time < 1) {
            $spend_time = round(($end_time - $this->start_time), 2) . '秒';
        } else {
            $spend_time = round(($end_time - $this->start_time) / 60, 2) . '分钟';
        }
        echo "[" . date('Y-m-d H:i:s') . "] 耗时：{$spend_time} 处理完成！\n";
        return parent::afterAction($action, $result);
    }

    /**
     * 更新过期排班状态
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/4
     */
    public function actionUpdateExpiredSchedule()
    {
        GuahaoScheduleModel::updateAll(
            ['status' => 3],
            [
                'and',
                ['status' => [0, 1]],
                ['<', 'visit_valid_time', time()],
                ['<>', 'visit_valid_time', 0],
            ]
        );
        GuahaoScheduleModel::updateAll(
            ['status' => 3],
            [
                'and',
                ['status' => [0, 1]],
                ['<', 'visit_time', date('Y-m-d')],
            ]
        );
    }

    /**
     * 更新过期订单状态(每天凌晨23点30分执行，单次最多处理10000条)
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/4
     */
    public function actionUpdateExpiredOrder()
    {
        $params             = [];
        $params['page']     = 1;
        $params['pageSize'] = 100;
        $num                = 0;

        do {
            $data = GuahaoOrderModel::getExpiredOrder($params);
            if (!empty($data)) {
                foreach ($data as $value) {
                    $timeOut = GuahaoOrderModel::updateExpiredOrder($value['id']);
                    if ($timeOut) {
                        echo "[" . date('Y-m-d H:i:s') . "]  {$value['id']} 订单完成！\n";
                    }
                }
            } else {
                break;
            }
            $dataCount = count($data);
            $num++;
            unset($data);
        } while ($dataCount >= $params['pageSize'] && $num < 100);
    }

    /**
     * 医院附属信息初始化 只需执行一次
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/7
     */
    // public function actionHospitalGuahaoInfo()
    // {
    //     echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：\n";
    //     $query      = GuahaoHospitalModel::find();
    //     $totalQuery = clone $query;
    //     $totalCount = $totalQuery->count();
    //     $pageObj    = new Pagination([
    //         'totalCount' => $totalCount,
    //         'pageSize'   => 100,
    //     ]);
    //     $page = 1;

    //     do {
    //         $pageObj->setPage($page - 1, false);
    //         $list = $query->select('id')->orderBy(['id' => SORT_ASC])->offset($pageObj->offset)->limit($pageObj->limit)->asArray()->all();
    //         if (empty($list)) {
    //             break;
    //         } else {
    //             foreach ($list as $k => $v) {
    //                 $hosModel = GuahaoHospitalModel::findOne($v['id']);
    //                 if (!empty($hosModel)) {
    //                     $hospitalGuahaoinfo = CommonFunc::getHospitalGuahaoinfo($hosModel->tp_platform, $hosModel->tp_hospital_code);
    //                     if (!empty($hospitalGuahaoinfo)) {
    //                         $hosModel->tp_allowed_cancel_day  = ArrayHelper::getValue($hospitalGuahaoinfo, 'tp_allowed_cancel_day', '1');
    //                         $hosModel->tp_allowed_cancel_time = ArrayHelper::getValue($hospitalGuahaoinfo, 'tp_allowed_cancel_time', '12:00');
    //                         $hosModel->tp_guahao_description  = ArrayHelper::getValue($hospitalGuahaoinfo, 'tp_guahao_description', '');
    //                         $hosModel->save();
    //                     }
    //                 }
    //             }
    //         }
    //         echo "[" . date('Y-m-d H:i:s') . "] 第 {$page} 页处理完成！\n";
    //         $page++;
    //         $dataCount = count($list);
    //         unset($data);
    //     } while ($dataCount > 0);
    // }

    /**
     * 初始化订单附表脚本 只需执行一次
     * @throws \Exception
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/8
     */
    // public function actionGuahaoOrderInfoInit()
    // {
    //     echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：\n";
    //     $query      = GuahaoOrderModel::find()->where(['order_sn' => '']);
    //     $totalQuery = clone $query;
    //     $totalCount = $totalQuery->count();
    //     $pageObj    = new Pagination([
    //         'totalCount' => $totalCount,
    //         'pageSize'   => 100,
    //     ]);
    //     $page = 1;

    //     do {
    //         $pageObj->setPage($page - 1, false);
    //         $list = $query->select('id')->orderBy(['id' => SORT_ASC])->offset($pageObj->offset)->limit($pageObj->limit)->asArray()->all();
    //         if (empty($list)) {
    //             break;
    //         } else {
    //             foreach ($list as $k => $v) {
    //                 $model = GuahaoOrderModel::findOne($v['id']);
    //                 if (!empty($model)) {
    //                     $model->order_sn = $model->tp_order_id;
    //                     $model->save();

    //                     $infoModel                  = new GuahaoOrderInfoModel();
    //                     $infoModel->order_id        = $model->id;
    //                     $infoModel->tp_json         = $model->tp_json;
    //                     $infoModel->doctor_title    = $model->doctor_title;
    //                     $infoModel->taketime_desc   = $model->taketime_desc;
    //                     $infoModel->takeway         = $model->takeway;
    //                     $infoModel->visit_starttime = $model->visit_starttime;
    //                     $infoModel->visit_endtime   = $model->visit_endtime;
    //                     $infoModel->visit_address   = $model->visit_address;
    //                     $infoModel->visit_number    = $model->visit_number;
    //                     $infoModel->remark          = $model->remark;
    //                     $infoModel->save();
    //                 }
    //             }
    //         }
    //         echo "[" . date('Y-m-d H:i:s') . "] 第 {$page} 页处理完成！\n";
    //         $page++;
    //         $dataCount = count($list);
    //         unset($data);
    //     } while ($dataCount > 0);
    // }

    /**
     * 待退款状态 请求支付中心
     * @throws \Exception
     * @author xiujianying
     * @date 2020/12/11
     */
    public function actionRunRefund()
    {
        echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：\n";
        $notifyUrl = ArrayHelper::getValue(\Yii::$app->params, 'api_url.self') . '/pay/refund-back?encryption=false'; //退款回调
        $data      = GuahaoOrderModel::find()->where(['pay_status' => 4])->limit(100)->all();
        if ($data) {
            foreach ($data as $v) {
                //提交退款 改为退款中 等回调完改退款完成（pay_status=6 state=1取消）
                $v->pay_status = 5; //退款中
                $v->save();
                //走商城退款
                $sdk    = PaySdk::getInstance();
                $params = [
                    //'pay_no' => ArrayHelper::getValue($info,'pay_no'),
                    'pay_out_trade_no'    => $v->order_sn,
                    'refund_fee'          => $v->visit_cost / 100,
                    'refund_notify_url'   => $notifyUrl,
                    'refund_out_trade_no' => $v->order_sn . '_refund',
                    'refund_desc'         => '挂号退款',
                ];
                $refundRes = $sdk::refund($params);
                if (ArrayHelper::getValue($refundRes, 'code') == 1) {
                    echo $v->order_sn . '--success';
                } else {
                    echo $v->order_sn . '--' . ArrayHelper::getValue($refundRes, 'msg');
                }
                sleep(1);
            }
        }
    }

    /**
     * 只需执行一次 初始化订单表miaoid和complete_time
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2020/12/29
     */
    // public function actionGuahaoOrderInit()
    // {
    //     echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：\n";
    //     $query      = GuahaoOrderModel::find();
    //     $totalQuery = clone $query;
    //     $totalCount = $totalQuery->count();
    //     $pageObj    = new Pagination([
    //         'totalCount' => $totalCount,
    //         'pageSize'   => 100,
    //     ]);
    //     $page = 1;

    //     do {
    //         $pageObj->setPage($page - 1, false);
    //         $list = $query->select('id')->orderBy(['id' => SORT_ASC])->offset($pageObj->offset)->limit($pageObj->limit)->asArray()->all();
    //         if (empty($list)) {
    //             break;
    //         } else {
    //             foreach ($list as $k => $v) {
    //                 $model = GuahaoOrderModel::findOne($v['id']);
    //                 if (!empty($model)) {
    //                     $doctorInfo            = DoctorModel::getInfo($model->doctor_id);
    //                     $model->miao_doctor_id = $doctorInfo['miao_doctor_id'] ?? 0;
    //                     if (empty($model->complete_time) && $model->state == 3) {
    //                         $model->complete_time = time();
    //                     }
    //                     $model->save();
    //                 }
    //             }
    //         }
    //         echo "[" . date('Y-m-d H:i:s') . "] 第 {$page} 页处理完成！\n";
    //         $page++;
    //         $dataCount = count($list);
    //         unset($data);
    //     } while ($dataCount > 0);
    // }

    /**
     * 导入医生  暂停使用
     * @param string $tp_platform_str
     * @param string $tp_hospital_code
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/2/23
     */
    public function actionGenerateDoctor($tp_platform_str = '', $tp_hospital_code = '')
    {
        if (!array_key_exists($tp_platform_str, $this->platform)) {
            die("来源错误\n\r");
        }

        $error_arr   = [];
        $execute_num = 0;
        $success_num = 0;

        $tp_platform = $this->platform[$tp_platform_str];

        //获取医院列表
        $hospitalQuery = GuahaoHospitalModel::find()->where(['tp_platform' => $tp_platform]);
        $hospitalQuery->andWhere(['<>', 'tp_hospital_code', '']);
        $hospitalQuery->andWhere(['<>', 'hospital_id', 0]);
        $hospitalQuery->andWhere(['status' => 1]);
        $hospitalQuery->andWhere(['has_import' => 0]);
        if (!empty($tp_hospital_code)) {
            $tp_hospital_code = explode(',', $tp_hospital_code);
            $hospitalQuery->andWhere(['tp_hospital_code' => $tp_hospital_code]);
        }
        $hospitalList = $hospitalQuery->asArray()->all();

        foreach ($hospitalList as $hospital) {
            //获取科室id列表
            $hospitalDepartmentIdList = TmpDepartmentThirdPartyModel::find()->where([
                'tp_platform'      => $tp_platform,
                'tp_hospital_code' => $hospital['tp_hospital_code'],
                'is_relation'      => 1,
            ])->asArray()->all();
            if (empty($hospitalDepartmentIdList)) {
                echo "[" . date('Y-m-d H:i:s') . "] " . $hospital['hospital_name'] . " 没有科室数据！\n";
                continue;
            }
            $hospitalDepartmentIdList = array_column($hospitalDepartmentIdList, 'hospital_department_id', 'tp_department_id');

            //获取科室列表
            $hospitalDepartmentList = HospitalDepartmentRelation::find()->where(['id' => array_values($hospitalDepartmentIdList)])->asArray()->all();
            if (empty($hospitalDepartmentList)) {
                echo "[" . date('Y-m-d H:i:s') . "] " . $hospital['hospital_name'] . " 没有科室数据！\n";
                continue;
            }
            $hospitalDepartmentList = array_column($hospitalDepartmentList, null, 'id');

            $page  = 1;
            $limit = 1000;
            $query = TmpDoctorThirdPartyModel::find()->where(['tp_platform' => $tp_platform]);
            $query->andWhere(['tp_hospital_code' => $hospital['tp_hospital_code']]);
            $query->andWhere(['<>', 'tp_doctor_id', '']);
            $query->andWhere(['in', 'tp_department_id', array_keys($hospitalDepartmentIdList)]);
            $query->andWhere(['<>', 'realname', '']);
            $query->andWhere(['is_relation' => 0]);

            do {
                $offset      = max(0, ($page - 1)) * $limit;
                $doctor_list = $query->offset($offset)->limit($limit)->asArray()->all();
                if (!empty($doctor_list)) {
                    foreach ($doctor_list as $key => $value) {
                        $relationInfo = DoctorModel::find()->where([
                            'tp_platform'      => $tp_platform,
                            'tp_hospital_code' => $value['tp_hospital_code'],
                            'tp_department_id' => $value['tp_department_id'],
                            'tp_doctor_id'     => $value['tp_doctor_id'],
                            'status'           => 1,
                        ])->asArray()->one();
                        if ($relationInfo) {
                            echo "[" . date('Y-m-d H:i:s') . "] " . $value['tp_doctor_id'] . $value['realname'] . " 已存在！\n";
                            continue 1;
                        }

                        $execute_num++;
                        $transition = Yii::$app->getDb()->beginTransaction();
                        try {
                            //检测是否有相同医生存在
                            $primary_id = 0;
                            $doctorInfo = DoctorModel::find()->where([
                                'tp_platform'      => $tp_platform,
                                'tp_hospital_code' => $value['tp_hospital_code'],
                                'tp_doctor_id'     => $value['tp_doctor_id'],
                                'primary_id'       => 0,
                            ])->asArray()->one();
                            if ($doctorInfo) {
                                $primary_id = $doctorInfo['doctor_id'];
                            }

                            //保存数据
                            $doctorModel                     = new DoctorModel();
                            $doctorModel->primary_id         = $primary_id;
                            $doctorModel->realname           = $value['realname'];
                            $doctorModel->tp_platform        = $value['tp_platform'];
                            $doctorModel->avatar             = '';
                            $doctorModel->source_avatar      = $value['source_avatar'];
                            //$doctorModel->good_at            = $value['good_at'];
                            //$doctorModel->profile            = $value['profile'];
                            //$doctorModel->province           = $value['province'] ?? '';
                            //$doctorModel->city               = $value['city'] ?? '';
                            //$doctorModel->district           = $value['district'] ?? '';
                            $doctorModel->job_title_id       = $value['job_title_id'];
                            $doctorModel->job_title          = $value['job_title'] ?: '未知';
                            //$doctorModel->professional_title = $value['professional_title'];
                            $doctorModel->hospital_id        = $hospital['hospital_id'];
                            $doctorModel->hospital_name      = $hospital['hospital_name'];
                            $doctorModel->hospital_type      = 1;
                            $doctorModel->miao_doctor_id     = 0;

                            //科室信息
                            $doctorModel->frist_department_id       = $hospitalDepartmentList[$hospitalDepartmentIdList[$value['tp_department_id']]]['frist_department_id'] ?? 0;
                            $doctorModel->second_department_id      = $hospitalDepartmentList[$hospitalDepartmentIdList[$value['tp_department_id']]]['second_department_id'] ?? 0;
                            $doctorModel->frist_department_name     = $hospitalDepartmentList[$hospitalDepartmentIdList[$value['tp_department_id']]]['frist_department_name'] ?? '';
                            $doctorModel->second_department_name    = $hospitalDepartmentList[$hospitalDepartmentIdList[$value['tp_department_id']]]['second_department_name'] ?? '';
                            $doctorModel->miao_frist_department_id  = $hospitalDepartmentList[$hospitalDepartmentIdList[$value['tp_department_id']]]['miao_frist_department_id'] ?? 0;
                            $doctorModel->miao_second_department_id = $hospitalDepartmentList[$hospitalDepartmentIdList[$value['tp_department_id']]]['miao_second_department_id'] ?? 0;

                            $doctorModel->tp_hospital_code          = $value['tp_hospital_code'] ?? "";
                            $doctorModel->tp_doctor_id              = $value['tp_doctor_id'] ?? "";
                            $doctorModel->tp_frist_department_id    = $value['tp_frist_department_id'] ?? "";
                            $doctorModel->tp_department_id          = $value['tp_department_id'] ?? "";
                            $doctorModel->create_time               = time();
                            $doctorModel->is_plus                   = 1;
                            $res                                    = $doctorModel->save();
                            if ($res) {
                                $doctor_id = $doctorModel->attributes['doctor_id'];
                                //新增医生附属信息
                                $doctorInfoModel                      = new DoctorInfoModel();
                                $doctorInfoModel->doctor_id           = $doctor_id;
                                $doctorInfoModel->good_at             = strip_tags($value['good_at']);
                                $doctorInfoModel->profile             = strip_tags($value['profile']);
                                $doctorInfoModel->professional_title  = $value['professional_title'];
                                $doctorInfoModel->related_disease     = '';
                                $doctorInfoModel->initial             = '';
                                $doctorInfoModel->create_time         = time();
                                $doctorInfoModel->save();

                            } else {
                                throw new \Exception(json_encode($doctorModel->getErrors(), JSON_UNESCAPED_UNICODE));
                            }

                            /*//关联信息
                            $doctorRelationModel                         = new TbDoctorThirdPartyRelationModel();
                            $doctorRelationModel->doctor_id              = $doctor_id;
                            $doctorRelationModel->realname               = $value['realname'];
                            $doctorRelationModel->tp_platform            = $tp_platform;
                            $doctorRelationModel->tp_doctor_id           = $value['tp_doctor_id'];
                            $doctorRelationModel->tp_hospital_code       = $value['tp_hospital_code'];
                            $doctorRelationModel->hospital_name          = $value['hospital_name'];
                            $doctorRelationModel->tp_frist_department_id = $value['tp_frist_department_id'];
                            $doctorRelationModel->frist_department_name  = $value['frist_department_name'];
                            $doctorRelationModel->tp_department_id       = $value['tp_department_id'];
                            $doctorRelationModel->second_department_name = $value['second_department_name'];
                            $doctorRelationModel->create_time            = time();
                            $doctorRelationModel->status                 = 1;
                            $doctorRelationModel->admin_id               = 0;
                            $doctorRelationModel->admin_name             = 'system';
                            $relationRes                                 = $doctorRelationModel->save();
                            if (!$relationRes) {
                                throw new \Exception(json_encode($doctorRelationModel->getErrors(), JSON_UNESCAPED_UNICODE));
                            }*/

                            $tmpModel              = TmpDoctorThirdPartyModel::findOne($value['id']);
                            $tmpModel->doctor_id   = $doctor_id;
                            $tmpModel->is_relation = 1;
                            $tmpModel->update_time = time();
                            $tmpRes                = $tmpModel->save();
                            if (!$tmpRes) {
                                throw new \Exception(json_encode($tmpModel->getErrors(), JSON_UNESCAPED_UNICODE));
                            }

                            $transition->commit();
                            $success_num++;
                            echo $value['tp_doctor_id'] . "完成！\n";
                        } catch (\Exception $e) {
                            $transition->rollBack();
                            $msg = $e->getMessage();
                            \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生导入失败');
                            echo "[" . date('Y-m-d H:i:s') . "] " . $value['tp_doctor_id'] . $value['realname'] . " 导入失败：{$msg}！\n";
                            $error_arr[] = ['tp_doctor_id' => $value['tp_doctor_id'], 'msg' => $e->getMessage()];
                        }
                    }
                    echo "[" . date('Y-m-d H:i:s') . "] " . $hospital['hospital_name'] . " 第{$page}页处理完成！\n";
                }
                $num = count($doctor_list);
                unset($doctor_list);
                $page++;
            } while ($num > 0);

            //更新tb_guahao_hospital.has_import = 已导入
            $hospitalModel             = GuahaoHospitalModel::findOne($hospital['id']);
            $hospitalModel->has_import = 1;
            $hospitalModel->save();
            echo "[" . date('Y-m-d H:i:s') . "] " . $hospital['hospital_name'] . " 处理完成！\n";

            unset($hospitalDepartmentIdList);
            unset($hospitalDepartmentList);
        }

        echo "处理数量：$execute_num\n";
        echo "成功数量：$success_num\n";
        if (count($error_arr) > 0) {
            echo "处理失败信息ID：\n";
            print_r($error_arr);
        }
    }

    /**
     * 一、 医院关联完 判断是否能导入 删除对应表数据
     * /usr/local/php7/bin/php /data/wwwroot/nisiya.top/yii guahao/platfrom-del-data jiankang160
     * 医院关联完 删除对应表数据
     *
     * 没问题后 delete from tb_doctor where status in(11,12)  更新缓存
     *
     * @param $platfrom
     * @param string $hospital_id
     * @param string $real_del 是否真正删除数据  默认
     * @throws \Exception
     * @author xiujianying
     * @date 2021/2/23
     */
    public function actionPlatfromDelData($platfrom, $real_del = 0, $hospital_id = '')
    {
        if (!isset($this->platform[$platfrom])) {
            echo '异常来源';
            exit;
        }
        $platfromId = ArrayHelper::getValue($this->platform, $platfrom);
        $where      = ['status' => 1, 'has_import' => 0, 'tp_platform' => $platfromId];
        if ($hospital_id) {
            $where['hospital_id'] = $hospital_id;
        }
        $data = GuahaoHospitalModel::find()->where($where)->all();
        if ($data) {
            foreach ($data as $v) {
                // tp_platform 平台类型  tp_hospital_code 第三方医院id 根据平台类型 因表关系变动，这里更改查询条件 liuyingwei 2021-08-16 开始
                $exists = \Yii::$app->db->createCommand("select count(*) from tb_doctor where hospital_id=" . $v->hospital_id . " and tp_platform != 0")->queryScalar();
                //这里更改查询条件 liuyingwei 2021-08-16 结束
                if ($exists) {
                    //存在不导入
                    $v->has_import = -1;
                    if ($real_del) {
                        $v->save();
                    }

                    echo "[" . date('Y-m-d H:i:s') . "--hospid:" . $v->hospital_id . '--code:' . $v->tp_hospital_code . "] 存在关联关系不导入：\n";
                } else {
                    //不存在删除
                    /**
                     * ①、 根据hospital_id 删除 tb_doctor
                     * ②、 根据hospital_id 删除 tb_hospital_department_relation.id，
                     * ③、 根据tb_hospital_department_relation.id=tb_department_third_party_relation.hospital_department_id删除tb_department_third_party_relation 医院的科室表。
                     */
                    echo "[" . date('Y-m-d H:i:s') . "--hospid:" . $v->hospital_id . '--code:' . $v->tp_hospital_code . "] 不存在关联关系：\n";
                    //①tb_doctor
                    if ($real_del) {
                        DoctorModel::updateAll(['status' => 11], ['hospital_id' => $v->hospital_id, 'status' => 1]);
                        DoctorModel::updateAll(['status' => 12], ['hospital_id' => $v->hospital_id, 'status' => 0]);
                    }
                    $count = DoctorModel::find()->where(['hospital_id' => $v->hospital_id, 'status' => [0, 1]])->count();
                    echo '预删除 tb_doctor--' . $count . "\n";

                    //②tb_hospital_department_relation
                    $hospData = HospitalDepartmentRelation::find()->where(['hospital_id' => $v->hospital_id])->all();
                    echo "[" . date('Y-m-d H:i:s') . "]tb_hospital_department_relation start \n";
                    if ($hospData) {
                        foreach ($hospData as $hosp) {
                            //③
                            //tb_department_third_party_relation
                            //echo "[" . date('Y-m-d H:i:s') . "]tb_department_third_party_relation start \n";
                            if ($real_del) {
                                TbDepartmentThirdPartyRelationModel::deleteAll(['hospital_department_id' => $hosp->id]);
                            }
                            //tb_tmp_department_third_party
                            //echo "[" . date('Y-m-d H:i:s') . "]tb_tmp_department_third_party start \n";
                            if ($real_del) {
                                TmpDepartmentThirdPartyModel::updateAll(['is_relation' => 0], ['hospital_department_id' => $hosp->id]);
                                $hosp->delete();
                            }
                            //echo "[" . date('Y-m-d H:i:s') . "]tb_hospital_department_relation--".$hosp->id." \n";
                        }
                    }
                    echo "[" . date('Y-m-d H:i:s') . "]tb_hospital_department_relation end \n";

                }
                echo "\n\n";
            }
        }

    }

    /**
     *  【六】# 医院关联完,执行修改对应表数据状态,确定没有问题,至少保持2 天 (执行删除对应表数据)
     *  php /Users/lyw/dnmp/www/nisiya.top/yii guahao/delete-doctor
     *	/usr/local/php7/bin/php /data/wwwroot/nisiya.top/yii guahao/delete-doctor 1 >> /tmp/delete_doctor.log
     * @param int $real_del
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-08-26
     */
    public function actionDeleteDoctor($real_del=0)
    {
        echo "[" . date('Y-m-d H:i:s') . "] doctor and doctor info delete start ". PHP_EOL;
        $doctorIdList = DoctorModel::find()->where(['in','status',[11,12]])->select('doctor_id')->asArray()->all();
        foreach($doctorIdList as $k=>$v){
            $transition = Yii::$app->getDb()->beginTransaction();
            try {
                if ($real_del) {
                    $doctor = DoctorModel::find()->where(['doctor_id' => $v['doctor_id']])->one();
                    $doctor->delete();
                    TmpDoctorThirdPartyModel::updateAll(['is_relation' => 0,'doctor_id'=> 0],['doctor_id' => $v['doctor_id']]);
                    echo '医生 doctor_id 【'.strval($v['doctor_id']). '】删除成功'. PHP_EOL;
                    $transition->commit();
                } else {
                    echo '医生 doctor_id 【'.strval($v['doctor_id']). '】预删除'. PHP_EOL;
                }
            }catch (\Exception $e){
                $transition->rollBack();
                $msg = $e->getMessage();
                \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 删除失败');
            }
        }
        echo "[" . date('Y-m-d H:i:s') . "] doctor and doctor info delete  end ". PHP_EOL. PHP_EOL;
        exit;
    }

    /**
     * 二、处理能导入的 列出要对应的科室
     * /usr/local/php7/bin/php /data/wwwroot/nisiya.top/yii guahao/export-tmp-dep jiankang160 > /home/xiujianyingtmp_dep.csv
     * 关联完的医院 导出科室 让运营对应王氏科室
     * @param $platfrom
     * @param int $type
     * @throws \Exception
     * @author xiujianying
     * @date 2021/2/23
     */
    public function actionExportTmpDep($platfrom, $type = 0)
    {
        if (!isset($this->platform[$platfrom])) {
            echo '异常来源';
            exit;
        }
        $platfromId = ArrayHelper::getValue($this->platform, $platfrom);
        // 这里新增一条件， 如果type==1 就是代表导出没有对应医院id 的 第三方医院 （liuyingwei 2021-08-13）
        if ($type == 1) {
            $where = ['tp_platform' => $platfromId];
        } else {
            $where = ['status' => 1, 'has_import' => 0, 'tp_platform' => $platfromId];
        }

        $guahao = GuahaoHospitalModel::find()->where($where)->asArray()->all();
        if ($guahao) {
            $tpHospArr    = [];
            $hospcodeToId = [];
            foreach ($guahao as $g) {
                $tpHospArr[]                            = $g['tp_hospital_code'];
                $hospcodeToId[$g['tp_hospital_code']]   = $g['hospital_id'];
                $hospcodeToName[$g['tp_hospital_code']] = $g['hospital_name'];

            }

            $data = TmpDepartmentThirdPartyModel::find()->where(['tp_platform' => $platfromId, 'is_relation' => 0, 'tp_hospital_code' => $tpHospArr])->asArray()->all();
            echo '合作来源,医院id,第三方医院code,医院名称,医院一级科室,医院二级科室id,医院二级科室,王氏一级科室id,王氏一级科室名称,王氏二级科室id,王氏二级科室名称' . "\n";
            if ($data) {
                foreach ($data as $v) {
                    echo $platfrom . ',' . ArrayHelper::getValue($hospcodeToId, $v['tp_hospital_code']) . ',' . $v['tp_hospital_code'] . ',' . ArrayHelper::getValue($hospcodeToName, $v['tp_hospital_code']) . ',' . $v['third_fkname'] . ',' . $v['tp_department_id'] . ',' . $v['department_name'] . ',' . ',' . ',' . ',' . "\n";
                }
            }
        } else {
            echo '没有数据';
        }
        exit;
    }

    /**
     * 三、运营对应完科室 生成sql 提工单
     * /usr/local/php7/bin/php /data/wwwroot/nisiya.top/yii guahao/csv2sql > /home/xiujianying/sql.sql
     * 运营对应完的表格生成sql 插入到tmp_guahao_hospital_department_relation表中
     * @throws \Exception
     * @author xiujianying
     * @date 2021/2/23
     */
    public function actionCsv2sql()
    {
        $file = '/home/users/liuyingwei/liutmp_tb_guahao_hospital.csv';
        $data = file($file);
        file_put_contents("/home/users/liuyingwei/tmp_guahao_hospital_department_relation_sql.log", '');
        if ($data) {
            foreach ($data as $k => $v) {
                if ($k > 0) {
                    $arr        = explode(',', $v);
                    $arr        = array_map('trim', $arr);
                    $platfromId = ArrayHelper::getValue($this->platform, $arr[0]);
                    //判断整条数据是否有问题 如果 合作来源错误， 医院id 为空，或者科室id 为空就直接跳出循环并记录日志 liuyingwei (2021-8-18)
                    if(!$platfromId ||  empty($arr[1]) || empty($arr[7])){
                        $msg = 'Excle 序号【'.strval(intval($k)+1).'】医院名称【'.$arr[3].'】数据有误';
                        file_put_contents("/home/users/liuyingwei/tmp_guahao_hospital_department_relation_sql.log", strval($msg).PHP_EOL, FILE_APPEND);
                        continue;
                    }
                    $sql        = "INSERT INTO `tmp_guahao_hospital_department_relation` (`tp_frist_department_name`,`tp_second_department_name`,`tp_department_id`,`hospital_id`,`tp_platform`,`tp_hospital_id`,`tp_hospital_name`,`miao_frist_department_id`,`miao_frist_department_name`,`miao_second_department_id`,`miao_second_department_name`) VALUES ( '$arr[4]', '$arr[6]', '$arr[5]', '$arr[1]', '$platfromId', '$arr[2]','$arr[3]', '$arr[7]', '$arr[8]', '$arr[9]', '$arr[10]');";

                    echo $sql . "\n";
                }
            }
        }

        exit;
    }

    /**
     * 【导出第三方医院】后给运营，运营补充完线上医院id后 ，根据csv 生成测试update sql
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-08-13
     */
    public function actionCsv3sql()
    {
        $file = '/home/users/liuyingwei/liutmp_tb_guahao_hospital.csv';
        $data = file($file);
        file_put_contents("/home/users/liuyingwei/liutmp_tb_guahao_hospital.log", '');
        if ($data) {
            foreach ($data as $k => $v) {
                if ($k > 0) {
                    $arr = explode(',', $v);
                    $arr = array_map('trim', $arr);
                    $platfromId = ArrayHelper::getValue($this->platform, $arr[0]);
                    //判断整条数据是否有问题 如果 合作来源错误， 医院id 为空，或者科室id 为空就直接跳出循环并记录日志
                    if(!$platfromId || empty($arr[1])){
                        $msg = 'Excle 序号【'.strval(intval($k)+1).'】医院名称【'.$arr[3].'】数据有误';
                        file_put_contents("/home/users/liuyingwei/liutmp_tb_guahao_hospital.log", strval($msg).PHP_EOL, FILE_APPEND);
                        continue;
                    }

                    $sql = "update `tb_guahao_hospital` SET `hospital_id` = " . $arr[1] . ",`status`=1,`has_import`=0 where `tp_platform` = " . $platfromId . " and tp_hospital_code=" . $arr[2] . ";";
                    echo $sql . "\n";
                }
            }
        }
        exit;
    }


    //准备sql，导入第三方医院数据、第三方科室数据、第三方医生数据、整理完之后的第三方临时医院科室对照数据，存储到生产环境环境tmp各个表（走接口拉取时间很长）

    /**
     *  ① 第三方医院数据
     * @param $platfrom
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-08-13
     */
    public function actionExportTmpHospitalSql($platfrom)
    {
        if (!isset($this->platform[$platfrom])) {
            echo '异常来源';
            exit;
        }
        $platfromId = ArrayHelper::getValue($this->platform, $platfrom);
        $where = ['tp_platform' => $platfromId];
        $guahao = GuahaoHospitalModel::find()->where($where)->all();
        if ($guahao) {
            foreach ($guahao as $g) {
                $sql = "INSERT INTO `tb_guahao_hospital` (`tp_hospital_code`,`tp_platform`,`hospital_id`,`hospital_name`,`corp_id`,`city_code`,`status`,`remarks`,`tp_guahao_section`,`tp_guahao_verify`,`tp_allowed_cancel_day`,`tp_allowed_cancel_time`,`tp_guahao_description`,`province`,`tp_hospital_level`,`has_import`,`tp_open_day`,`tp_open_time`)VALUES('$g->tp_hospital_code',$g->tp_platform,$g->hospital_id,'$g->hospital_name','$g->corp_id',$g->city_code,$g->status,'$g->remarks',$g->tp_guahao_section,'$g->tp_guahao_verify',$g->tp_allowed_cancel_day,'$g->tp_allowed_cancel_time','$g->tp_guahao_description','$g->province','$g->tp_hospital_level',$g->has_import,$g->tp_open_day,'$g->tp_open_time');";
                echo $sql . "\n";
            }
        } else {
            echo '没有数据';
        }
        exit;
    }

    /**
     * ② 第三方科室数据
     * @param $platfrom
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-08-13
     */
    public function actionExportTmpDepSql($platfrom)
    {
        if (!isset($this->platform[$platfrom])) {
            echo '异常来源';
            exit;
        }
        $platfromId = ArrayHelper::getValue($this->platform, $platfrom);
        $where = ['tp_platform' => $platfromId];

        $guahao = TmpDepartmentThirdPartyModel::find()->where($where)->all();
        if ($guahao) {
            foreach ($guahao as $g) {
                $sql = "INSERT INTO `tb_tmp_department_third_party` (`tp_platform`, `tp_hospital_code`, `hospital_name`, `third_fkid`, `third_fkname`, `third_skid`, `third_skname`, `tp_department_id`, `department_name`, `hospital_department_id`, `is_relation`, `tp_open_day`, `tp_open_time`) VALUES ($g->tp_platform, '$g->tp_hospital_code', '$g->hospital_name', '$g->third_fkid', '$g->third_fkname', '$g->third_skid', '$g->third_skname', '$g->tp_department_id', '$g->department_name', $g->hospital_department_id, $g->is_relation, $g->tp_open_day, '$g->tp_open_time');";
                echo $sql . "\n";
            }
        } else {
            echo '没有数据';
        }
    }

    /**
     *  ③ 第三方医生数据
     * @param $platfrom
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-08-13
     */
    public function actionExportTmpDoctorSql($platfrom)
    {
        if (!isset($this->platform[$platfrom])) {
            echo '异常来源';
            exit;
        }
        $platfromId = ArrayHelper::getValue($this->platform, $platfrom);
        $where = ['tp_platform' => $platfromId];

        $guahao = TmpDoctorThirdPartyModel::find()->where($where)->all();
        if ($guahao) {
            foreach ($guahao as $g) {
                $sql = "INSERT INTO `tb_tmp_doctor_third_party` (`tp_platform`, `tp_doctor_id`, `tp_primary_id`, `realname`, `source_avatar`, `good_at`, `profile`, `province`, `city`, `district`, `job_title_id`, `job_title`, `professional_title`, `tp_hospital_code`, `hospital_name`, `tp_frist_department_id`, `frist_department_name`, `tp_department_id`, `second_department_name`, `doctor_id`, `is_relation`, `status`) VALUES ($g->tp_platform, '$g->tp_doctor_id', '$g->tp_primary_id', '$g->realname', '$g->source_avatar', '$g->good_at', '$g->profile', '$g->province', '$g->city', '$g->district', $g->job_title_id, '$g->job_title', '$g->professional_title', '$g->tp_hospital_code', '$g->hospital_name', '$g->tp_frist_department_id', '$g->frist_department_name', '$->tp_department_id', '$g->second_department_name', $g->doctor_id, $g->is_relation, $g->status);";
                echo $sql . "\n";
            }
        } else {
            echo '没有数据';
        }
    }


    /**
     * 删除测试数据
     * @param $platform
     * @param int $real_del
     * @param string $tp_hospital_code
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     * @author xiujianying
     * @date 2021/2/25
     */
    public function actionDelTestData($platform='', $real_del = 0, $tp_hospital_code='')
    {
        if (!$platform) die("请输入来源");
        $admin_info = ['admin_id' => 0, 'admin_name' => '系统'];
        $hos_where['tp_platform'] = $platform;
        if (!empty($tp_hospital_code)) {
            $hos_where['tp_hospital_code'] = $tp_hospital_code;
        }

        $guahaoData = GuahaoHospitalModel::find()->where($hos_where)->all();
        if ($guahaoData) {
            foreach ($guahaoData as $guahao) {
                echo 'gh--' . $guahao->tp_hospital_code . '--' . $guahao->hospital_id . '--' . "\n";
                if ($guahao->hospital_id) {

                    $hosp_exists = GuahaoHospitalModel::find()->where(['hospital_id' => $guahao->hospital_id])->andWhere(['<>', 'tp_platform', $platform])->exists();
                    if (!$hosp_exists) {
                        if ($real_del) {
                            //清除科室
                            $hosDepObj = TbDepartmentThirdPartyRelationModel::find()->where($hos_where)->all();
                            if ($hosDepObj) {
                                foreach ($hosDepObj as $dep) {
                                    echo 'department--' . $dep->tp_department_id . "\n";
                                    $depQuery = TbDepartmentThirdPartyRelationModel::find()->where(['id' => $dep->id])->one();
                                    if ($depQuery && $real_del) {
                                        $depQuery->delete();
                                        $depName = '';
                                        //清除关联科室
                                        $depRelaQuery = HospitalDepartmentRelation::find()->where(['id' => $dep->hospital_department_id])->one();
                                        if ($depRelaQuery) {
                                            $depName = $depRelaQuery->second_department_name ?? "";
                                            $depRelaQuery->delete();
                                        }
                                        $editContent  = '删除第三方医院 ' . $dep->tp_hospital_code  . $guahao->hospital_name . '的科室 科室ID ' . $dep->tp_department_id . $depName;
                                        TbLog::addLog($editContent, "删除第三方医院{$guahao->hospital_name}的科室", $admin_info);
                                    }
                                }
                            }
                            //HospitalDepartmentRelation::deleteAll(['hospital_id' => $guahao->hospital_id]);
                            HospitalDepartmentRelation::hospitalDepartment($guahao->hospital_id, true);
                        }
                        echo 'HospitalDepartmentRelation del' . "\n";
                    } else {
                        echo '其他平台关联过，不能够删' . "\n";
                    }
                }
                echo '未关联' . "\n";

                if ($real_del) {
                    if(intval($guahao->status) ==2){ // 禁用状态保持
                        $guahao->status      = 2;
                    }else{
                        $guahao->status      = 0;
                    }

                    $guahao->has_import  = -1;
                    $guahao->hospital_id = 0;
                    $guahao->save();
                    $editContent  = '第三方医院 ' . $guahao->tp_hospital_code . $guahao->hospital_name . ' 取消关联王氏医院';
                    TbLog::addLog($editContent, '取消关联王氏医院', $admin_info);
                }
                echo 'guahao_hospital  del' . "\n\n";
            }
        }

        //tb_dodtor tb_doctor_info
        $docObj = DoctorModel::find()->where($hos_where)->all();
        if ($docObj) {
            foreach ($docObj as $doc) {
                echo 'doctor--' . $doc->doctor_id . "\n";
                $docQuery = DoctorModel::find()->where(['doctor_id' => $doc->doctor_id])->one();
                //判断是否关联其他平台数据 不存在则删除tb_doctor
                if (!$doc->is_plus) {
                    if ($docQuery) {
                        if ($real_del) {
                            $docQuery->delete();
                        }
                        echo 'tb_doctor del' . "\n";
                        // 删除 tb_doctor_info
                        if ($real_del) {
                            $infoQuery = DoctorInfoModel::find()->where(['doctor_id' => $doc->doctor_id])->one();
                            if ($infoQuery) {
                                $infoQuery->delete();
                            }
                        }
                        echo 'tb_doctor_info del' . "\n";
                    }
                }
                if ($real_del) {
                    if ($doc) {
                        $doc->delete();
                    }
                    GuahaoScheduleModel::deleteByDoctorId($doc['tp_doctor_id'],$doc['doctor_id'],$doc['tp_platform']);
                    echo 'Doctor  del Guahao Schedule' . "\n\n";
                    $dModel = new DoctorModel();
                    if ($doc->doctor_id && $docQuery->hospital_id) {
                        $dModel->UpdateInfo($doc->doctor_id, $docQuery->hospital_id);
                    }
                }
                $editContent  = '删除第三方医院 ' . $guahao->tp_hospital_code . $guahao->hospital_name . ' 医生 ' . $doc->doctor_id . $doc->realname;
                TbLog::addLog($editContent, "删除第三方医院{$guahao->hospital_name}的医生", $admin_info);
                echo 'Doctor  del' . "\n\n";
            }
        } else {
            echo '没医生  end';
        }
    }


    /**
     * 清除第三方医院下的科室 (慎用)
     * @param $platform 来源
     * @param string $tp_hospital_code 第三方医院id
     * @param string $tp_doctor_id 第三方医生id
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     * @author wanghongying
     * @date 2021/11/16
     */
    public function actionClearDepartment($platform='', $tp_hospital_code='', $tp_department_id='')
    {
        if (!$platform) die("请输入来源");
        $admin_info = ['admin_id' => 0, 'admin_name' => '系统'];
        $dep_where['tp_platform'] = $platform;
        $doc_where['tp_platform'] = $platform;
        if (!empty($tp_hospital_code)) {
            $dep_where['tp_hospital_code'] = $tp_hospital_code;
            $doc_where['tp_hospital_code'] = $tp_hospital_code;
        }
        if (!empty($tp_doctor_id)) {
            $dep_where['tp_department_id'] = $tp_department_id;
        }

        $guahaoData = GuahaoHospitalModel::find()->where($doc_where)->all();
        if ($guahaoData) {
            foreach ($guahaoData as $guahao) {
                if ($guahao->hospital_id) {
                    //清除科室
                    $hosDepObj = TbDepartmentThirdPartyRelationModel::find()->where($dep_where)->all();
                    if ($hosDepObj) {
                        foreach ($hosDepObj as $dep) {
                            echo 'department--' . $dep->tp_department_id . "\n";
                            $depQuery = TbDepartmentThirdPartyRelationModel::find()->where(['id' => $dep->id])->one();
                            if ($depQuery) {
                                $depQuery->delete();
                                $depName = '';
                                //清除关联科室
                                $depRelaQuery = HospitalDepartmentRelation::find()->where(['id' => $dep->hospital_department_id])->one();
                                if ($depRelaQuery) {
                                    $depName = $depRelaQuery->second_department_name ?? "";
                                    $depRelaQuery->delete();
                                    HospitalDepartmentRelation::hospitalDepartment($depRelaQuery->hospital_id, true);
                                }
                                $editContent  = '删除第三方医院 ' . $dep->tp_hospital_code  . $guahao->hospital_name . '的科室 科室ID ' . $dep->tp_department_id . $depName;
                                TbLog::addLog($editContent, "删除第三方医院{$guahao->hospital_name}的科室", $admin_info);
                            }
                        }
                        echo 'guahao_hospital  del' . "\n\n";
                    }
                }
            }
        }
    }

    /**
     * 清除第三方医院下的医生 (慎用)
     * @param $platform 来源
     * @param string $tp_hospital_code 第三方医院id
     * @param string $tp_doctor_id 第三方医生id
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     * @author wanghongying
     * @date 2021/11/16
     */
    public function actionClearDoctor($platform='', $tp_hospital_code='', $tp_doctor_id='')
    {
        if (!$platform) die("请输入来源");
        $admin_info = ['admin_id' => 0, 'admin_name' => '系统'];
        $doc_where['tp_platform'] = $platform;
        if (!empty($tp_hospital_code)) {
            $doc_where['tp_hospital_code'] = $tp_hospital_code;
        }
        if (!empty($tp_doctor_id)) {
            $doc_where['tp_doctor_id'] = $tp_doctor_id;
        }
        $docObj = DoctorModel::find()->where($doc_where)->all();

        if ($docObj) {
            foreach ($docObj as $doc) {
                echo 'doctor--' . $doc->doctor_id . "\n";
                $docQuery = DoctorModel::find()->where(['doctor_id' => $doc->doctor_id])->one();
                //判断是否关联其他平台数据 不存在则删除tb_doctor
                if (!$doc->is_plus) {
                    if ($docQuery) {
                        $docQuery->delete();
                        echo 'tb_doctor del' . "\n";
                        // 删除 tb_doctor_info
                        $infoQuery = DoctorInfoModel::find()->where(['doctor_id' => $doc->doctor_id])->one();
                        if ($infoQuery) {
                            $infoQuery->delete();
                        }
                        echo 'tb_doctor_info del' . "\n";
                    }
                }

                if ($doc) {
                    $doc->delete();
                }

                GuahaoScheduleModel::deleteByDoctorId($doc['tp_doctor_id'],$doc['doctor_id'],$doc['tp_platform']);
                echo 'Doctor  del Guahao Schedule' . "\n\n";
                $dModel = new DoctorModel();
                if ($doc->doctor_id && $docQuery->hospital_id) {
                    $dModel->UpdateInfo($doc->doctor_id, $docQuery->hospital_id);
                }

                $editContent  = '删除第三方医院 ' . $doc->tp_hospital_code . $doc->hospital_name . ' 医生 ' . $doc->doctor_id . $doc->realname;
                TbLog::addLog($editContent, "删除第三方医院{$doc->hospital_name}的医生", $admin_info);
                echo 'Doctor  del' . "\n\n";
            }
        } else {
            echo '没医生  end';
        }

    }

    /**
     *  删除排班缓存
     * @param $platform 来源
     * @param string $hospital_id 关联医院id
     * @param int $real_del
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-10-25
     */
    public function actionDelTestSchedule($platform, $hospital_id=0, $real_del = 0)
    {
        if (YII_ENV != 'prod') {
            $where['tp_platform']   = $platform;
            $where['status']        = 1;
            if($hospital_id != 0){
                $where['hospital_id'] = $hospital_id;
            }
            $docObj = GuahaoScheduleModel::find()->where($where)->all();
            $countNum = count($docObj);
            $num =$countNum;

            if ($docObj) {
                foreach ($docObj as $doc) {
                    if ($real_del) {
                        GuahaoScheduleModel::deleteByDoctorId($doc['tp_doctor_id'],$doc['doctor_id'],$doc['tp_platform']);
                        $num--;
                        echo '总数【'. $countNum. '】还剩余【'.$num. '】Doctor  del Guahao Schedule' . "\n\n";
                    }
                }
                echo '总数【'. $countNum."】\n\n";
            } else {
                echo '没医生  end';
            }
        }
    }

    /**
     * 统计地区医院
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/4/25
     */
    public function actionDistrictHospital()
    {
        echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：\n";
        //获取地区
        $district = SnisiyaSdk::getInstance()->getDistrict();
        foreach ($district as $sheng) {
            $where = [
                'region_pinyin' => $sheng['pinyin'],
                'page' => 1,
                'pagesize' => 1
            ];
            $res = SnisiyaSdk::getInstance()->getHospitalList($where);
            if (ArrayHelper::getValue($res, 'totalCount', 0) > 0) {
                echo "{$sheng['name']}-全部-有\n";
            } else {
                echo "{$sheng['name']}-全部-无\n";
            }

            if (!empty($sheng['city_arr'])) {
                foreach ($sheng['city_arr'] as $city) {
                    $where = [
                        'region_pinyin' => $city['pinyin'],
                        'page' => 1,
                        'pagesize' => 1
                    ];
                    $res = SnisiyaSdk::getInstance()->getHospitalList($where);
                    if (ArrayHelper::getValue($res, 'totalCount', 0) > 0) {
                        echo "{$sheng['name']}-{$city['name']}-有\n";
                    } else {
                        echo "{$sheng['name']}-{$city['name']}-无\n";
                    }
                }
            }
        }
    }

    /**
     *  根据需要迁移的数量，判断生成几个文件夹
     * @param int $countNum
     * @return array
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-09-03
     */
    public function actionRange($countNum = 10)
    {
        $imgNum = ceil($countNum / 10);

        // 这里是如果图片数量过大，暂且定位每个文件夹图片数量 1000
        if($countNum > 10000){
            $folderNum = ceil($countNum / 1000);
        }else{
            $folderNum = 9;
        }
        // 如果当前的图片小于 20 一个文件夹足矣
        if ($imgNum > 1) {
            $number = range(0, $folderNum);
        }else{
            $number = [0];  // 当天日期
        }
        $folderArr = [];
        foreach ($number as $v){
            $folderArr[$v] = strval(date("Y/m/d", strtotime("-" . strval($v) . " day")));
        }
        return $folderArr;
    }

//    /**
//     * 执行脚本，删除陕西错误第三方医生数据
//     * @return array
//     * @author zhangfan <zhangfan01@yuanxin-inc.com>
//     * date 2021/9/11
//     */
//    public function actionDelTpDoctor()
//    {
//        $tp_platform = 7;
//        $page = 1;
//        $limit = 100;
//        $query = TbTmpDoctorThirdPartyTmp::find()->select('doctor_id,is_relation,realname')->where(['tp_platform' => $tp_platform, 'is_relation' => 1]);
//        do {
//            $offset = max(0, ($page - 1)) * $limit;
//            $doctor_list = $query->offset($offset)->limit($limit)->asArray()->all();
//            if (!empty($doctor_list)) {
//                foreach ($doctor_list as $key => $value) {
//                    $relationInfo = TmpDoctorThirdPartyModel::find()->where([
//                        'tp_platform' => $tp_platform,
//                        'doctor_id' => $value['doctor_id'],
//                        'is_relation' => 1,
//                        'realname' => $value['realname'],
//                    ])->one();
//
//                    if (empty($relationInfo)) {
//                        echo "[" . date('Y-m-d H:i:s') . "] " . $value['doctor_id'] . $value['realname'] . " 不存在！\n";
//                        continue;
//                    }
//
//                    $transition = Yii::$app->getDb()->beginTransaction();
//                    try {
//                        $relationInfo->doctor_id = 0;
//                        $relationInfo->is_relation = 0;
//                        $res = $relationInfo->save();
//                        if (!$res) {
//                            throw new \Exception(json_encode($relationInfo->getErrors(), JSON_UNESCAPED_UNICODE));
//                        }
//                        $transition->commit();
//                        echo $value['realname'] . "完成！\n";
//                    } catch (\Exception $e) {
//                        $transition->rollBack();
//                        $msg = $e->getMessage();
//                        echo "[" . date('Y-m-d H:i:s') . "] " . $value['doctor_id'] . $value['realname'] . " 失败：{$msg}！\n";
//                    }
//                }
//                echo "[" . date('Y-m-d H:i:s') . "]  第{$page}页处理完成！\n";
//            }
//            $num = count($doctor_list);
//            unset($doctor_list);
//            $page++;
//        } while ($num > 0);
//
//        //处理手动关联
//        $errId = ['344153', '344151', '344148'];
//        foreach ($errId as $doctor_id) {
//            $transition = Yii::$app->getDb()->beginTransaction();
//            try {
//                $doctor = DoctorModel::find()->where(['doctor_id' => $doctor_id])->one();
//                if (empty($doctor)) {
//                    throw new \Exception("[" . date('Y-m-d H:i:s') . "] " . $doctor_id . " 不存在！\n");
//                }
//                $doctor->delete();
//
//                $relationInfo = TmpDoctorThirdPartyModel::find()->where([
//                    'tp_platform' => $tp_platform,
//                    'doctor_id' => $doctor_id,
//                    'is_relation' => 1,
//                ])->one();
//                if (empty($relationInfo)) {
//                    throw new \Exception("[" . date('Y-m-d H:i:s') . "] " . $doctor_id . " 不存在！\n");
//                }
//
//                $relationInfo->doctor_id = 0;
//                $relationInfo->is_relation = 0;
//                $res = $relationInfo->save();
//                if (!$res) {
//                    throw new \Exception(json_encode($relationInfo->getErrors(), JSON_UNESCAPED_UNICODE));
//                }
//                $transition->commit();
//                echo $doctor_id . "完成！\n";
//            } catch (\Exception $e) {
//                $transition->rollBack();
//                $msg = $e->getMessage();
//                echo "[" . date('Y-m-d H:i:s') . "] " . $doctor_id . " 失败：{$msg}！\n";
//            }
//        }
//    }

//    /**
//     * 修复陕西第三方科室信息
//     * @return array
//     * @author zhangfan <zhangfan01@yuanxin-inc.com>
//     * date 2021/9/11
//     */
//    public function actionFixTpDepartment()
//    {
//        $tp_platform = 7;
//        $exists = \Yii::$app->db->createCommand("SELECT tp_hospital_code,tp_department_id,department_name FROM tb_tmp_department_third_party_tmp WHERE tp_platform = 7 AND char_length(tp_department_id) > 32")->queryAll();
//        if (!empty($exists)) {
//            //匹配医院id相同、科室id前32位相同、科室名称相同
//            foreach ($exists as $value) {
//                $transition = Yii::$app->getDb()->beginTransaction();
//                try {
//                    $relationInfo = TmpDepartmentThirdPartyModel::find()->where([
//                        'tp_hospital_code' => $value['tp_hospital_code'],
//                        'tp_department_id' => substr($value['tp_department_id'], 0, 32),
//                        'department_name' => $value['department_name'],
//                    ])->one();
//                    if (empty($relationInfo)) {
//                        throw new \Exception($value['tp_hospital_code'] . "-" . $value['department_name'] . " 不存在！");
//                    }
//
//                    $relationInfo->tp_department_id = $value['tp_department_id'];
//                    $res = $relationInfo->save();
//                    if (!$res) {
//                        throw new \Exception(json_encode($relationInfo->getErrors(), JSON_UNESCAPED_UNICODE));
//                    }
//
//                    //如果已关联 更新关联表
//                    if ($relationInfo->is_relation == 1) {
//                        $departmentRelation = TbDepartmentThirdPartyRelationModel::find()->where([
//                            'hospital_department_id' => $relationInfo->hospital_department_id,
//                            'tp_department_id' => substr($value['tp_department_id'], 0, 32),
//                            'tp_platform' => 7,
//                        ])->one();
//                        if (empty($departmentRelation)) {
//                            throw new \Exception($value['tp_hospital_code'] . "-" . $value['department_name'] . " 错误！");
//                        }
//                        $departmentRelation->tp_department_id = $value['tp_department_id'];
//                        $resDep = $departmentRelation->save();
//                        if (!$resDep) {
//                            throw new \Exception(json_encode($relationInfo->getErrors(), JSON_UNESCAPED_UNICODE));
//                        }
//                    }
//
//                    $transition->commit();
//                    echo $value['tp_department_id'] . "完成！\n";
//                } catch (\Exception $e) {
//                    $transition->rollBack();
//                    $msg = $e->getMessage();
//                    echo "[" . date('Y-m-d H:i:s') . "] " . $value['tp_department_id'] . " 失败：{$msg}！\n";
//                }
//            }
//        }
//    }

    /**
     * 修复陕西第三方医生信息
     * @return array
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/9/11
     */
    public function actionFixTpDoctor()
    {
        $tp_platform = 7;
        $page = 1;
        $limit = 100;
        $query = TmpDoctorThirdPartyModel::find()->select('id')->where(['tp_platform' => $tp_platform, 'is_relation' => 1]);
        do {
            $offset = max(0, ($page - 1)) * $limit;
            $doctor_list = $query->offset($offset)->limit($limit)->asArray()->all();
            if (!empty($doctor_list)) {
                foreach ($doctor_list as $key => $value) {
                    $relationInfo = TmpDoctorThirdPartyModel::find()->where([
                        'id' => $value['id'],
                    ])->one();
                    if (empty($relationInfo)) {
                        echo "[" . date('Y-m-d H:i:s') . "] " . $value['id'] . " 不存在！\n";
                        continue;
                    }

                    $transition = Yii::$app->getDb()->beginTransaction();
                    try {
                        $doctorRelation = DoctorModel::find()->where([
                            'doctor_id' => $relationInfo->doctor_id,
                            'tp_platform' => 7,
                        ])->one();
                        if (empty($doctorRelation)) {
                            throw new \Exception($relationInfo->doctor_id . " 错误！");
                        }
                        $doctorRelation->tp_department_id = $relationInfo->tp_department_id;
                        $doctorRelation->tp_doctor_id = $relationInfo->tp_doctor_id;
                        $resDep = $doctorRelation->save();
                        if (!$resDep) {
                            throw new \Exception(json_encode($relationInfo->getErrors(), JSON_UNESCAPED_UNICODE));
                        }

                        $transition->commit();
                        echo $relationInfo->doctor_id . "完成！\n";
                    } catch (\Exception $e) {
                        $transition->rollBack();
                        $msg = $e->getMessage();
                        echo "[" . date('Y-m-d H:i:s') . "] " . $value['id'] . " 错误：{$msg}！\n";
                    }
                }
                echo "[" . date('Y-m-d H:i:s') . "]  第{$page}页处理完成！\n";
            }
            $num = count($doctor_list);
            unset($doctor_list);
            $page++;
        } while ($num > 0);
    }

    /**
     * 排班停诊订单
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2021/10/20
     */
    public function actionHospitalClose($tp_platform = '')
    {
        $this->getSdk($tp_platform)->actionHospitalClose();
    }

    //
    /**
     * 推送阿里订单 (固定时间) /guahao/push-ali-order
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022/06/14
     */
    public function actionPushAliOrder($date = '2022-06-10 20:30:00')
    {
        echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：\n";
        $query = GuahaoOrderModel::find()->where(['coo_platform' => 2]);
        $query->andWhere(['<>', 'state', 6]);
        $query->andWhere(['>=', 'create_time', strtotime($date)]);

        $totalQuery = clone $query;
        $totalCount = $totalQuery->count();
        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => 100,
        ]);
        $page = 1;
        do {
            $pageObj->setPage($page - 1, false);
            $list = $query->select('id,tp_platform')->orderBy(['id' => SORT_ASC])->offset($pageObj->offset)->limit($pageObj->limit)->asArray()->all();
            if (empty($list)) {
                break;
            } else {
                foreach ($list as $k => $v) {
                    $job_id = CommonFunc::guahaoPushQueue($v['id'], 2, 2, $v['tp_platform']);
                    echo "[" . date('Y-m-d H:i:s') . "] {$v['id']} 处理完成：{$job_id}\n";
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
     * 推送百度订单 (固定时间) /guahao/push-baidu-order
     * /usr/local/php7.4.8/bin/php /data/wwwroot/nisiya.top/yii /guahao/push-baidu-order >> /tmp/push_baidu_order.log 2>&1
     * @author wanghongying <wanghongying@yuanxinjituan.com>
     * @date 2022/06/16
     */
    public function actionPushBaiduOrder($date = '2022-10-20')
    {
        echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：\n";
        $query = GuahaoOrderModel::find()->where(['coo_platform' => 1]);
        $query->andWhere(['IN', 'state', [1,2]]);
        $query->andWhere(['>=', 'visit_time', $date]);

        $actionArr = [
            0 => 3,
            1 => 2,
            2 => 2,
            3 => 1,
        ];
        $totalQuery = clone $query;
        $totalCount = $totalQuery->count();
        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => 100,
        ]);
        $page = 1;
        do {
            $pageObj->setPage($page - 1, false);
            $list = $query->select('id,tp_platform,state')->orderBy(['id' => SORT_ASC])->offset($pageObj->offset)->limit($pageObj->limit)->asArray()->all();

            if (empty($list)) {
                break;
            } else {
                foreach ($list as $k => $v) {
                    if (in_array($v['state'], array_keys($actionArr))) {
                        $action = (isset($actionArr[$v['state']])) ? $actionArr[$v['state']] : 0;
                        if ($action) {
                            $job_id = CommonFunc::guahaoPushQueue($v['id'], 2, $action, $v['tp_platform']);
                        }
                    }
                    echo "[" . date('Y-m-d H:i:s') . "] {$v['id']} 处理完成：{$job_id}\n";
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
     * 推送就诊日期今天以后的订单到合作方
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/11/12
     */
    public function actionPushOrderToCoo()
    {
        echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：\n";
        $query = GuahaoOrderModel::find()->where(['coo_platform' => 1]);
        $query->andWhere(['state' => 1]);
        $query->andWhere(['>=', 'visit_time', date('Y-m-d')]);

        $totalQuery = clone $query;
        $totalCount = $totalQuery->count();
        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => 100,
        ]);
        $page = 1;

        do {
            $pageObj->setPage($page - 1, false);
            $list = $query->select('id,tp_platform')->orderBy(['id' => SORT_ASC])->offset($pageObj->offset)->limit($pageObj->limit)->asArray()->all();
            if (empty($list)) {
                break;
            } else {
                foreach ($list as $k => $v) {
                    $job_id = CommonFunc::guahaoPushQueue($v['id'], 2, 2, $v['tp_platform']);
                    echo "[" . date('Y-m-d H:i:s') . "] {$v['id']} 处理完成：{$job_id}\n";
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
     * 初始化被百度开放的来源医院
     * @param string $coo_id
     * @param string $tp_platform
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-15
     */
    public function actionAddCooRelationHospital($coo_id="", $tp_platform='')
    {
        // 1 确定来源
        // 确定开放了哪些第三方来源的医院
        // 查询数据写入数据库
        echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：\n";
        if(empty($coo_id)){
            $cooId = 1; // 百度
        }else{
            $cooId = $coo_id; // 百度
        }
        $tpPlatformArr = GuahaoPlatformListModel::getOpenCooTpPlatformIdListByCooId($cooId);

        $query = GuahaoHospitalModel::find()
            ->where(['in','tp_platform',$tpPlatformArr]);
        $query->andWhere(['status' => 1]);


        $totalQuery = clone $query;
        $totalCount = $totalQuery->count();
        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => 100,
        ]);
        $page = 1;
        $error_arr = [];
        $model = new GuahaoPlatformRelationHospitalModel();
        do {
            $pageObj->setPage($page - 1, false);

            $list = $query->select('tp_platform,tp_hospital_code')->orderBy(['id' => SORT_ASC])->offset($pageObj->offset)->limit($pageObj->limit)->asArray()->all();
            if (empty($list)) {
                break;
            } else {
                foreach ($list as $k => $v) {
                    try {
                        $modelClone = clone $model;
                        $modelClone->tp_platform = $v['tp_platform'];
                        $modelClone->tp_hospital_code = $v['tp_hospital_code'];
                        $modelClone->coo_platform = $cooId;
                        $modelClone->remarks = '初始化开放来源';
                        $modelClone->create_time = time();
                        $modelClone->update_time = time();
                        $modelClone->status = 1            ;
                        $modelClone->admin_id = 0;
                        $modelClone->admin_name = 'system';
                        $modelClone->save();
                    } catch (\Exception $e) {
                        $msg = $e->getMessage();
                        echo "[" . date('Y-m-d H:i:s') . "] 来源：【" . $v['tp_platform'] ."】第三方医院code：【".$v['tp_hospital_code'] . "】 开放失败：{$msg}！".PHP_EOL;
                        $error_arr[] = [
                            'tp_platform' => $v['tp_platform'],
                            'tp_hospital_code' => $v['tp_hospital_code'],
                            'msg' => $e->getMessage()];
                    }
                }
            }
            echo "[" . date('Y-m-d H:i:s') . "] 第 {$page} 页处理完成！\n";
            $page++;
            $dataCount = count($list);
            unset($data);
        } while ($dataCount > 0);
        if (count($error_arr) > 0) {
            echo "处理失败信息信息：\n";
            print_r($error_arr);
        }
        echo "[" . date('Y-m-d H:i:s') . "] 处理完成！\n";
    }

    /**
     *  初始化对接平台数据
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-12-15
     */
    public function actionAddTpPlatform()
    {
        $insertData =[
            [1,"河南","henan","GhHenanSdk","department",1,1],
            [2,"南京","nanjing","GhNanjingSdk","department",1,1],
            [3,"好大夫","haodaifu","GhHaodaifuSdk","doctor",2,0],
            [4,"王氏加号","nisiya","","",2,0],
            [5,"健康160","jiankang160","GhJiankang160Sdk","department",1,1],
            [6,"王氏医生加号","","GhJiahaoSdk","doctor",2,0],
            [7,"陕西","shaanxi","GhShaanxiSdk","department",1,1],
            [8,"山西","shanxi","GhShanxiSdk","department",1,1],
            [9,"健康之路","jiankangzhilu","GhJiankangzhiluSdk","department",1,1],
            [10,"天津","tianjin","GhTianjinSdk","doctor",1,1],
        ];

        foreach ($insertData as $val){
            $findData = GuahaoPlatformListModel::find()->where(['tp_platform'=>$val[0],'tp_type'=>$val[2]])->one();
            if($findData){
                $data['id'] = $findData->id;
                $data['status'] = $findData->status;
            }else{
                $data['status'] = $val[6];
            }
            $data['admin_id'] = 0;
            $data['admin_name'] = 'system';
            $data['tp_platform'] = $val[0];
            $data['platform_name'] = $val[1];
            $data['tp_type'] = $val[2];
            $data['sdk'] = $val[3];
            $data['get_paiban_type'] = $val[4];
            $data['schedule_type'] = $val[5];
            $data['open_time'] = date('Y-m-d H:i:s',time());
            $res = GuahaoPlatformListModel::dataSave($data);
            if ($res) {
                echo $val[1] . '=>success' . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
            } else {
                echo $val[1] . $res. '=>error' . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
            }
        }
    }

    /**
     * 更新百度医生es 测试用
     * @param int $coo_platform_id
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/12/14
     */
    public function actionTestDoctorCooCache($coo_platform_id = 1)
    {
        $docWhere = [
            'd.hospital_type' => 1,
            'd.status' => 1,
            'tpr.coo_platform' => $coo_platform_id,
            'tpr.status' => 1,
        ];

        $page = 1;
        $limit = 1000;
        $query = DoctorModel::find()
            ->alias('d')
            ->join('INNER JOIN', ['tpr' => 'tb_guahao_platform_relation_hospital'], 'd.tp_platform=tpr.tp_platform AND d.tp_hospital_code=tpr.tp_hospital_code')
            ->where($docWhere);

        $model = new BuildToEsModel();
        do {
            $offset = max(0, ($page - 1)) * $limit;
            $doctor_list = $query->select(['d.doctor_id'])->offset($offset)->limit($limit)->orderBy('d.doctor_id desc')->asArray()->all();
            if (!empty($doctor_list)) {
                foreach ($doctor_list as $key => $value) {
                    $res = $model->db2esByIdDoctor($value['doctor_id']);
                    if ($res['code'] == 1) {
                        echo $value['doctor_id'] . 'success' . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                    } else {
                        echo $value['doctor_id'] . $res['msg'] . '___' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                    }
                }
                echo "[" . date('Y-m-d H:i:s') . "]  第{$page}页处理完成！\n";
            }
            $num = count($doctor_list);
            unset($doctor_list);
            $page++;
        } while ($num > 0);
    }

    /**
     * 初始化对接王氏来源数据
     * @param string $coo 来源编号
     * @param string $cooName 来源名称
     * @param string $docking 对接模式
     * @param string $date 开放日期
     * @param int $status  开放状态
     * @throws \yii\db\Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-03-19
     */
    public function actionAddCoo($coo='',$cooName="",$docking="",$date="",$status=1)
    {
        $data['coo_platform'] = $coo;
        $data['coo_name'] =$cooName;
        $data['status'] =$status;
        $data['docking'] =$docking;
        $res = GuahaoCooListModel::find()->where($data)->one();
        if(empty($date)){
            $data['open_time'] = date('Y-m-d',time());
        } else {
            $data['open_time'] = $date;
        }
        if($res){
            echo '已存在';
        }else{
            $result = Yii::$app->db->createCommand()->insert('tb_guahao_coo_list', $data)->execute();
            if($result){
                echo 'success';
            }else{
                echo 'error';
            }
        }
        // 无论程序执行成功或失败， 都更新缓存， 防止缓存中无数据
        $data = GuahaoCooListModel::getCooPlatformListCache(true);
        echo json_encode($data,JSON_UNESCAPED_UNICODE);

    }
    /**
     *  删除对接来源数据（慎用）
     * @param string $platform 来源
     * @param string $del_type 删除对象类型
     * @param int $real_del  是否删除
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-01-06
     */
    public function actionDeleteTpPlatformData($platform='', $del_type='',$real_del = 0)
    {
        if (YII_ENV != 'prod') {
            if (!$platform) die("请输入来源");
            $admin_info = ['admin_id' => 0, 'admin_name' => '系统'];
            $hos_where['tp_platform'] = $platform;
            $key = Yii::$app->params['cache_key']['platform_list'];
            $tpPlatformArr =  CommonFunc::getCodisCache($key);

            $arrArray = array_reduce($tpPlatformArr,function(&$arrArray,$v){
                $arrArray[] = $v['tp_platform'];
                return $arrArray;
            });

            if(!in_array(intval($platform),$arrArray)) {
                $platformArray = array_reduce($tpPlatformArr,function(&$platformArray,$v){
                    $platformArray[] = '来源【'.$v['platform_name'].'->platform为: '.$v['tp_platform'].' 】';
                    return $platformArray;
                });
                print_r($platformArray);
                die("来源错误， 请确认 ");
            }

            $delType = ['hospital','department','doctor'];
            if(!in_array(strval($del_type), $delType)) {
                print_r($delType);
                die('删除对象类型错误: '. implode( ' / ',$delType));
            }

            echo '删除【'.$del_type.'】start'. "\n\n";


            if($real_del){
                echo '3 秒后开始删除'.PHP_EOL;
            }else{
                echo '3 秒后开始查验数据'.PHP_EOL;
            }
            sleep(3);

            $hos_where['tp_platform'] = $platform;

            $page = 1;
            $limit = 10;
            $query = GuahaoHospitalModel::find()
                ->where($hos_where);

            do {
                $offset = max(0, ($page - 1)) * $limit;
                $hospitalList = $query->select('tp_hospital_code,tp_platform,hospital_name')->offset($offset)->limit($limit)->orderBy('id desc')->asArray()->all();
                if (!empty($hospitalList)) {
                    foreach ($hospitalList as $key => $value) {
                        // 循环医院
                        switch($del_type){
                            case "hospital":
                                echo "del hospital ". "\n\n";
                                $this->actionDelTpPlatformHospital($value, $real_del, $admin_info);
                                break;
                            case "department":
                                echo 'del department'. "\n\n";
                                $this->actionDelTpPlatformDepartment($value, $real_del, $admin_info);
                                break;
                            case "doctor":
                                echo "del doctor ". "\n\n";
                                $this->actionDelTpPlatformDoctor($value, $real_del, $admin_info);
                                break;
                        }
                    }
                    echo "[" . date('Y-m-d H:i:s') . "]  第{$page}页处理完成！\n";
                }
                $num = count($hospitalList);
                unset($hospitalList);
            } while ($num > 0);
        }


    }


    /**
     * @param $value
     * @param $real_del
     * @param $admin_info
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-01-06
     */
    protected function actionDelTpPlatformHospital($value, $real_del, $admin_info)
    {
        $hosWhere['tp_hospital_code'] = $value['tp_hospital_code'];
        $hosWhere['tp_platform'] = $value['tp_platform'];
        $hospitalData = GuahaoHospitalModel::find()->where($hosWhere)->one();

        if ($hospitalData) {
            if($real_del){
                // 删除完医院， 就要删除 科室和医生
                echo 'Deleting departments ......'. "\n\n";
                $this->actionDelTpPlatformDepartment($value, $real_del, $admin_info);
                echo 'Deleting doctors ......'. "\n\n";
                $this->actionDelTpPlatformDoctor($value, $real_del, $admin_info);

                echo 'del ok ......'. "\n\n";
                sleep(1);
                $hospitalData->delete();
                $editContent  = '删除第三方医院 ' . $hospitalData->tp_hospital_code . $hospitalData->hospital_name;
                TbLog::addLog($editContent, "删除第三方医院{$hospitalData->hospital_name}", $admin_info);
                echo 'Hospital  del true' . "\n\n";
                echo 'Hospital update cache'. "\n\n";
                GuahaoHospitalModel::getTpHospitalCache($value['tp_platform'], $value['tp_hospital_code'], true);
            }

        }
    }

    /**
     *  删除医生
     * @param $value
     * @param $real_del
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-01-06
     */
    protected function actionDelTpPlatformDoctor($value,$real_del,$admin_info)
    {
        $doctorWhere['tp_hospital_code'] = $value['tp_hospital_code'];
        $doctorWhere['tp_platform'] = $value['tp_platform'];
        $docObj = DoctorModel::find()->where($doctorWhere)->all();

        if ($docObj) {
            foreach ($docObj as $doc) {
                echo 'doctor--' . $doc->doctor_id . "\n";
                $docQuery = DoctorModel::find()->where(['doctor_id' => $doc->doctor_id])->one();
                //关联不关联都删除
                if ($real_del) {
                    if ($docQuery) {
                        $docQuery->delete();
                        echo 'tb_doctor del' . "\n";
                        // 删除 tb_doctor_info
                        $infoQuery = DoctorInfoModel::find()->where(['doctor_id' => $doc->doctor_id])->one();
                        if ($infoQuery) {
                            $infoQuery->delete();
                        }
                        echo 'tb_doctor_info del' . "\n";
                    }
                    GuahaoScheduleModel::deleteByDoctorId($doc->tp_doctor_id,$doc->doctor_id,$doc->tp_platform);
                    echo 'Doctor  del Guahao Schedule' . "\n\n";
                    $dModel = new DoctorModel();
                    if ($doc->doctor_id && $docQuery->hospital_id) {
                        $dModel->UpdateInfo($doc->doctor_id, $docQuery->hospital_id);
                    }

                    $editContent  = '删除第三方医院 ' . $doc->tp_hospital_code . $doc->hospital_name . ' 医生 ' . $doc->doctor_id . $doc->realname;
                    TbLog::addLog($editContent, "删除第三方医院{$doc->hospital_name}的医生", $admin_info);
                    echo 'Doctor  del' . "\n\n";
                }
            }
        } else {
            echo '没医生  end'. "\n";
        }
    }


    /**
     *  删除科室
     * @param $value
     * @param $real_del
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2022-01-06
     */
    protected function actionDelTpPlatformDepartment($value, $real_del, $admin_info)
    {
        $depWhere['tp_hospital_code'] = $value['tp_hospital_code'];
        $depWhere['tp_platform'] = $value['tp_platform'];
        $hosDepObj = TbDepartmentThirdPartyRelationModel::find()->where($depWhere)->all();
        if ($hosDepObj) {
            foreach ($hosDepObj as $dep) {
                echo 'department--' . $dep->tp_department_id . "\n";
                $depQuery = TbDepartmentThirdPartyRelationModel::find()->where(['id' => $dep->id])->one();
                if ($depQuery) {
                    if ($real_del) {
                        $depQuery->delete();
                    }
                    $depName = '';
                    //清除关联科室
                    $depRelaQuery = HospitalDepartmentRelation::find()->where(['id' => $dep->hospital_department_id])->one();
                    if ($depRelaQuery) {
                        $depName = $depRelaQuery->second_department_name ?? "";
                        if ($real_del) {
                            $depRelaQuery->delete();
                            HospitalDepartmentRelation::hospitalDepartment($depRelaQuery->hospital_id, true);
                        }
                    }
                    $msg =  '删除第三方医院 ' . $dep->tp_hospital_code  . $value['hospital_name'] . '的科室 科室ID ' . $dep->tp_department_id . $depName.PHP_EOL;
                    if ($real_del) {
                        $editContent  = '删除第三方医院 ' . $dep->tp_hospital_code  . $value['hospital_name'] . '的科室 科室ID ' . $dep->tp_department_id . $depName;
                        TbLog::addLog($editContent, "删除第三方医院{$value['hospital_name']}的科室", $admin_info);
                    }
                    echo $msg;
                }
            }
            if ($real_del) {
                echo 'guahao_hospital  del' . "\n\n";
            }
        }

    }

}
