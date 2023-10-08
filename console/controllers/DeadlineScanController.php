<?php

namespace console\controllers;

use common\models\minying\ResourceDeadlineModel;

class DeadlineScanController extends CommonController
{
    /**
     * 到期更新医生或者医院状态
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-04
     */
    public function actionUpdateStatus()
    {
        $list = ResourceDeadlineModel::find()
            ->where(['<', 'end_time', time()])
            ->all();

        echo "[" . date('Y-m-d H:i:s') . "] 开始处理数据：" . PHP_EOL;
        /** @var ResourceDeadlineModel $model */
        foreach ($list as $model) {
            // 停用医院
            if ($model->resource_type == ResourceDeadlineModel::RESOURCE_TYPE_HOSPITAL) {
                ResourceDeadlineModel::freezeHospital($model);
            }
            // 停用医生
            if ($model->resource_type == ResourceDeadlineModel::RESOURCE_TYPE_DOCTOR) {
                ResourceDeadlineModel::freezeDoctor($model);
            }
        }
        echo "[" . date('Y-m-d H:i:s') . "] 处理完成！" . PHP_EOL;
    }
}