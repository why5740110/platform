<?php

namespace common\models;

use common\libs\CommonFunc;
use Yii;
use yii\data\Pagination;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class TmpDoctorThirdPartyModel extends \yii\db\ActiveRecord
{
    public static $is_relation_list = [
        0 => '未关联',
        1 => '已关联',
        // 2=>'禁用',
    ];

    public static function tableName()
    {
        return '{{%tb_tmp_doctor_third_party}}';
    }

    public static function getList($params)
    {
        $page        = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize    = isset($params['limit']) ? intval($params['limit']) : 10;
        $doctorQuery = self::find()
            ->select('*');
        if (!empty($params['tp_platform'])) {
            $doctorQuery->where(['tp_platform' => trim($params['tp_platform'])]);
        }
        if (!empty($params['is_relation'])) {
            if ($params['is_relation'] == 1) {
                $doctorQuery->andWhere(['is_relation' => 1]);
            } else {
                $doctorQuery->andWhere(['is_relation' => 0]);
            }
        }
        if (!empty($params['doctor'])) {
            $doctorQuery->andWhere(['like', 'realname', trim($params['doctor'])]);
        }

        //科室信息
        if (!empty($params['keshi'])) {
            $doctorQuery->andWhere(['like', 'frist_department_name', $params['keshi']]);
        }
        if (!empty($params['keshi'])) {
            $doctorQuery->orWhere(['like', 'second_department_name', $params['keshi']]);
        }
        if (!empty($params['hospital_name'])) {
            $doctorQuery->andWhere(['like', 'hospital_name', $params['hospital_name']]);
        }

        $totalCountQuery = clone $doctorQuery;
        $totalCount      = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount' => $totalCount,
            'pageSize'   => $pageSize,
        ]);
        $pageObj->setPage($page - 1);
        $posts = $doctorQuery->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('id desc')->asArray()->all();
        return $posts;
    }

    public static function getCount($params)
    {
        $doctorQuery = self::find()->select('*');
        if (!empty($params['tp_platform'])) {
            $doctorQuery->where(['tp_platform' => trim($params['tp_platform'])]);
        }
        if (!empty($params['is_relation'])) {
            if ($params['is_relation'] == 1) {
                $doctorQuery->andWhere(['is_relation' => 1]);
            } else {
                $doctorQuery->andWhere(['is_relation' => 0]);
            }
        }
        if (!empty($params['doctor'])) {
            $doctorQuery->andWhere(['like', 'realname', trim($params['doctor'])]);
        }

        //科室信息
        if (!empty($params['keshi'])) {
            $doctorQuery->andWhere(['like', 'frist_department_name', $params['keshi']]);
        }
        if (!empty($params['keshi'])) {
            $doctorQuery->orWhere(['like', 'second_department_name', $params['keshi']]);
        }
        if (!empty($params['hospital_name'])) {
            $doctorQuery->andWhere(['like', 'hospital_name', $params['hospital_name']]);
        }
        $posts = $doctorQuery->asArray()->count();
        return $posts;
    }

    /**
     * 获取第三方医生基本信息
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-17
     * @version v1.0
     * @param   integer    $item_id [description]
     * @return  [type]              [description]
     */
    public static function getTbDoctorItem($item_id = 0,$extra_fields = [])
    {
        $fields = [
            'tp_platform', 'tp_doctor_id','source_avatar',
            'tp_hospital_code', 'tp_frist_department_id','tp_department_id',
        ];
        if ($extra_fields) {
            $fields = array_merge($fields,$extra_fields);
        }
        $item_info = self::find()->select($fields)->where(['id' => $item_id])->asArray()->one();
        return $item_info;
    }

    /**
     * 保存第三放医生
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-03-27
     * @version 1.0
     * @param   array      $item [description]
     * @return  [type]           [description]
     */
    public static function saveDoctor($item = [])
    {
        try {
            $id = ArrayHelper::getValue($item, 'id', '');
            if ($id) {
                $docModel = TmpDoctorThirdPartyModel::find()->where(['id' => $id])->one();
            } else {
                $docModel              = new TmpDoctorThirdPartyModel();
                $docModel->create_time = time();
            }
            $doctorTitles                     = array_flip(CommonFunc::getTitle()) ?? [];
            $docModel->tp_doctor_id           = ArrayHelper::getValue($item, 'tp_doctor_id', '');
            $docModel->tp_platform            = ArrayHelper::getValue($item, 'tp_platform', 6);
            $docModel->tp_primary_id          = ArrayHelper::getValue($item, 'tp_primary_id', '');
            $docModel->realname               = ArrayHelper::getValue($item, 'realname', '');
            $docModel->source_avatar          = ArrayHelper::getValue($item, 'source_avatar', '');
            $docModel->good_at                = ArrayHelper::getValue($item, 'good_at', '');
            $docModel->profile                = ArrayHelper::getValue($item, 'profile', '');
            $doctorjobtitle                   = $item['job_title'] ?? '未知';
            $docModel->job_title              = $doctorjobtitle;
            $docModel->job_title_id           = $doctorTitles[$doctorjobtitle] ?? 99;
            $docModel->hospital_name          = ArrayHelper::getValue($item, 'hospital_name', '');
            $docModel->tp_frist_department_id = ArrayHelper::getValue($item, 'tp_frist_department_id', '');
            $docModel->frist_department_name  = ArrayHelper::getValue($item, 'frist_department_name', '');
            $docModel->tp_department_id       = ArrayHelper::getValue($item, 'tp_department_id', '');
            $docModel->second_department_name = ArrayHelper::getValue($item, 'second_department_name', '');
            $docModel->tp_hospital_code       = ArrayHelper::getValue($item, 'tp_hospital_code', '');
            $docModel->update_time            = time();
            $status                           = $docModel->save();
            if (!$status) {
                throw new \Exception(json_encode($docModel->getErrors(), JSON_UNESCAPED_UNICODE));
            }
            $res = ['code' => 0];
        } catch (\Exception $e) {
            \Yii::warning($e->getMessage(), __CLASS__ . '::' . __METHOD__ . '保存第三放医生error');
            $res = ['code' => 1, 'msg' => $e->getMessage()];
        }
        return $res;

    }

    /**
     * 更新医生信息
     */
    public static function updateDoctor($item, $did, $from)
    {
        $doc = self::find()->where([
            'tp_platform'  => $from,
            'tp_doctor_id' => $did,
        ])->one();
        if ($doc) {
            $doc->realname               = $item['realname'] ?? '';
            $doc->source_avatar          = $item['source_avatar'] ?? '';
            $doc->good_at                = $item['good_at'] ?? '';
            $doc->profile                = $item['profile'] ?? '';
            $doctorjobtitle              = $item['job_title'] ?? '未知';
            $doc->job_title_id           = $doctorTitles[$doctorjobtitle] ?? 99;
            $doc->job_title              = $doctorjobtitle;
            $doc->hospital_name          = $item['hospital_name'];
            $doc->tp_frist_department_id = $item['third_fkid'] ?? 0;
            $doc->tp_department_id       = $item['third_skid'] ?? 0;
            $doc->frist_department_name  = $item['third_fkname'] ?? '';
            $doc->second_department_name = $item['third_skname'] ?? '';
            $doc->tp_hospital_code       = $item['tp_hospital_code'];
            $doc->create_time            = time();
            $doc->save();
        }
    }
}
