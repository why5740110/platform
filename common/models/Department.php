<?php

namespace common\models;

use common\libs\CommonFunc;
use common\sdks\baseapi\BaseapiSdk;
use common\sdks\snisiya\SnisiyaSdk;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tb_department".
 *
 * @property int $department_id 科室ID
 * @property string $department_name 科室名字
 * @property int $parent_id 父级科室(0:顶级)
 * @property int $status 是否正常(1:正常,0:禁用)
 * @property int $is_common 是否常见科室(1:常见,0:不常见)来源科室ID(家庭医生科室ID)
 * @property int $create_time 创建时间
 * @property int $miao_first_department_id 王氏一级科室id
 * @property int $miao_second_department_id 王氏二级科室id
 * @property int $is_match 科室是否匹配王氏科室 1：是 0：否
 */
class Department extends \common\models\BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tb_department';
    }

    /**
     * 常见科室缓存修改为王氏缓存
     * @param bool $update_cache
     * @return array|bool|mixed|\yii\db\ActiveRecord[]
     * @author xiujianying
     * @date 2020/7/25
     */
    public static function department($update_cache = false)
    {
        $department_key = 'hospital:tb_department';

        $sdk = SnisiyaSdk::getInstance();
        $data = $sdk->department();
        if ($update_cache) {
            ##修改常见科室为王氏科室
            $departmentData = CommonFunc::getMiaoKeshi();
            if ($departmentData) {
                $data = $departmentData;
                CommonFunc::setCodisCache($department_key, $data);
            }
        }
        return $data;
    }

    /**
     * 医院原来的一二级科室关系
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-10-21
     * @version 1.0
     * @param   boolean    $update_cache [description]
     * @return  [type]                   [description]
     */
    public static function department_platform($update_cache = false)
    {
        $department_key = 'hospital:tb_department:platform';
        $data = CommonFunc::getCodisCache($department_key);
        if ($update_cache) {
            $departmentData = Department::find()->where(['is_common' => 1,'parent_id'=>0])->select('department_id,department_name,parent_id')->asArray()->all();
            if ($departmentData) {
                $data = [];
                foreach ($departmentData as $v) {
                    if ($v['parent_id'] == 0) {
                        $row['department_id'] = $v['department_id'];
                        $row['department_name'] = $v['department_name'];
                        $data[$v['department_id']] = $row;
                    } else {
                        $row['department_id'] = $v['department_id'];
                        $row['department_name'] = $v['department_name'];

                        $data[$v['parent_id']]['second_arr'][] = $row;
                    }
                }
                CommonFunc::setCodisCache($department_key, $data);
            }
        }
        return $data;
    }

    /**
     * 获取地区
     * @return array
     * @throws \Exception
     * @author lipengbo <lipengbo@yuanxinjituan.com>
     * @date 2022-10-23
     */
    public static function commonDistrict(): array
    {
        $res = ArrayHelper::getValue((new BaseapiSdk())->getAllData(), 'data', []);
        $id2code = array_column($res, 'code', 'id');
        $data = [];
        foreach ($res as $v) {
            if (!empty($v['code'])) {
                $data[$v['code']] = [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'parentid' => $v['parentid'],
                    'code' => $v['code'],
                    'order' => 0,
                    'parentcode' => ArrayHelper::getValue($id2code, $v['parentid'], ''),
                    'suffix' => $v['suffix'],
                ];
            }
        }
        return $data;
    }

    /**
     * 地区  只更新
     * @param bool $update_cache
     * @return array|bool|mixed
     * @author xiujianying
     * @date 2020/7/25
     */
    public static function district($update_cache = false)
    {
        $district_key = 'hospital:district:arr';
        $data = [];
        if ($update_cache) {
            $district = self::commonDistrict();
            $data = [];
            if ($district) {
                /**
                 * [1] => Array
                 * (
                 * [id] => 1
                 * [name] => 北京
                 * [parentid] => 0
                 * [code] => 110000
                 * [order] => 1
                 * [parentcode] =>
                 * [suffix] => 市
                 * [pinyin] => beijing
                 * [city_arr] => Array
                 * (
                 * [0] => Array
                 * (
                 * [id] => 36
                 * [name] => 东城
                 * [parentid] => 1
                 * [code] => 110101
                 * [order] => 1
                 * [parentcode] => 110000
                 * [suffix] => 区
                 * [pinyin] => dongcheng
                 * )
                 *
                 * [1] => Array
                 * (
                 * [id] => 37
                 * [name] => 西城
                 */
                //组合数组
                foreach ($district as $v) {
                    if ($v['parentid'] == 0) {
                        $data[$v['id']] = $v;
                    }
                }
                foreach ($data as &$p) {
                    $p['city_arr'] = [];
                    foreach ($district as $v) {
                        if ($p['id'] == $v['parentid']) {
                            $p['city_arr'][] = $v;
                        }
                    }
                }

            }
            if ($data) {
                CommonFunc::setCodisCache($district_key, $data);
            }
        }
        return $data;
    }

    /**
     * 地区拼音 转 省、城市id
     * @param $pinyin
     * @return array
     * @author xiujianying
     * @date 2020/7/29
     */
    public static function pinyin2id($pinyin)
    {
        return false;
        $district = Department::district();
        foreach ($district as $v) {
            if ($v['pinyin'] == $pinyin) {
                return ['p_id' => $v['id'], 'c_id' => 0];
            }
            foreach ($v['city_arr'] as $cArr) {
                if ($cArr['pinyin'] == $pinyin) {
                    return ['p_id' => $v['id'], 'c_id' => $cArr['id']];
                }
            }
        }
        return [];
    }

    public static function getKeshi($id)
    {
        $data = Department::find()->where(['department_id'=>$id])->select([
            'department_name',
        ])->asArray()->scalar();
        return $data;
    }

    /**
     * 获取所有二级科室
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-09-12
     * @version 1.0
     * @param   string     $fkeshi_id [description]
     * @return  [type]                [description]
     */
    public static function getAllSkeshiList($fkeshi_id='')
    {
        if (!$fkeshi_id) {
            return [];
        }
        $list =  Department::find()->where(['parent_id'=>$fkeshi_id])->select('min(department_id) department_id,trim(department_name) department_name')->groupBy('trim(department_name)')->orderBy('department_id asc')->asArray()->all();
        return $list;
    }

    /**
     * 根据科室名称获取科室信息
     * @param $department_name
     * @param string $type parent代表一级科室
     * @return array|Department|\yii\db\ActiveRecord|null
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/9/14
     */
    public static function getKeshiByDepartmentName($department_name, $type = '')
    {
        $query = Department::find()
            ->select(['department_id', 'parent_id', 'status', 'is_match', 'miao_first_department_id', 'miao_second_department_id'])
            ->where(['department_name' => $department_name]);
        if ($type == 'parent') {
            $query->andWhere(['parent_id' => 0]);
        } else {
            $query->andWhere(['<>', 'parent_id', 0]);
        }
        $data = $query->asArray()->one();
        return $data;
    }

    /**
     * 创建公共科室
     * @param $department_name
     * @param $parent_id
     * @param string $type parent代表一级科室
     * @return array|Department|mixed|\yii\db\ActiveRecord|null
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * date 2021/9/15
     */
    public static function createDepartment($department_name, $parent_id = '-1', $type = '')
    {
        if (empty($department_name)) {
            return 0;
        }
        $commonDepartment = Department::getKeshiByDepartmentName($department_name, $type);
        if (!empty($commonDepartment)) {
            return $commonDepartment['department_id'];
        }

        $model = new Department();
        $model->department_name = $department_name;
        $model->create_time = time();
        if ($type == 'parent') {
            $model->parent_id = 0;
            $model->is_match = 1;
        } else {
            $model->parent_id = $parent_id;
            $model->is_match = 0;
        }

        $res = $model->save();
        if ($res) {
            return $model->attributes['department_id'];
        } else {
            return 0;
        }
    }
}
