<?php

namespace backend\controllers;

use common\libs\CryptoTools;
use common\models\TbLog;
use common\models\LogHospitalApiLogNew;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class LogController extends BaseController
{
    //public $enableCsrfValidation = false;
    public $page_size            = 20;

    public function actionList()
    {
        $requestParams              = Yii::$app->request->getQueryParams();
        $requestParams['page']      = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit']     = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;
        $requestParams['admin_name']      = isset($requestParams['admin_name']) ? trim($requestParams['admin_name']) : '';
        $requestParams['description']      = isset($requestParams['description']) ? trim($requestParams['description']) : '';
        $requestParams['info']      = isset($requestParams['info']) ? trim($requestParams['info']) : '';

        // //拼装条件
        $field                        = '*';
        $where                        = [];
        $query = TbLog::find()->where($where);

        if (!empty(trim($requestParams['description']))) {
            $query->andWhere(['like', 'description', trim($requestParams['description'])]);
        }

        if (!empty(trim($requestParams['info']))) {
            $query->andWhere(['like', 'info', trim($requestParams['info'])]);
        }

        if (!empty(trim($requestParams['admin_name']))) {
            if (is_numeric($requestParams['admin_name'])) {
                $query->andWhere(['admin_id'=>trim($requestParams['admin_name'])]);
            } else {
                $query->andWhere(['like', 'admin_name', trim($requestParams['admin_name'])]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => $requestParams['limit'],
            ],
            'sort'       => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        $data = ['params' => ['dataProvider' => $dataProvider, 'requestParams' => $requestParams], 'requestParams' => $requestParams];
        return $this->render('list', $data);
    }

    /**
     * 接口日志
     * @return string
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2021/1/7
     */
    public function actionApiList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;
        $requestParams['create_time'] = (isset($requestParams['create_time']) && !empty($requestParams['create_time'])) ? $requestParams['create_time'] : date('Y-m-d');
        $requestParams['platform'] = (isset($requestParams['platform']) && !empty($requestParams['platform'])) ? $requestParams['platform'] : '';
        $requestParams['request_type'] = (isset($requestParams['request_type']) && !empty($requestParams['request_type'])) ? $requestParams['request_type'] : '';
        $requestParams['index'] = (isset($requestParams['index']) && !empty($requestParams['index'])) ? $requestParams['index'] : '';
        $requestParams['log_id'] = (isset($requestParams['log_id']) && !empty($requestParams['log_id'])) ? $requestParams['log_id'] : '';
        $requestParams['spend_time'] = (isset($requestParams['spend_time']) && !empty($requestParams['spend_time'])) ? $requestParams['spend_time'] : '';
        $requestParams['code'] = isset($requestParams['code']) ? $requestParams['code'] : '';

        $logHospitalModel = new LogHospitalApiLogNew();
        //判断表是否存在 zhangfan 20200415
        if ($logHospitalModel->checkTable($requestParams['create_time'])) {
            $logHospitalModel->resetTable($requestParams['create_time']);
            $query = $logHospitalModel->find();

            if (is_numeric($requestParams['log_id'])) {
                $log = $query->select('platform,log_detail')->where(['id' => $requestParams['log_id']])->one()->toArray();
                $logDetail = json_decode($log['log_detail'], true) ?: $log['log_detail'];
                if (is_array($logDetail) && (in_array($log['platform'], [3, 5]) || ArrayHelper::getValue($logDetail, 'log_detail.type') == 'json')) {
                    if (!empty(ArrayHelper::getValue($logDetail, 'log_detail.result'))) {
                        $logDetail['log_detail']['result'] = json_decode($logDetail['log_detail']['result'], true) ?: $logDetail['log_detail']['result'];
                    }
                }

                echo "<pre>";
                var_export($logDetail);
                echo "<pre>";
                die;
            }

            if (!empty(trim($requestParams['platform']))) {
                $query->andWhere(['platform' => trim($requestParams['platform'])]);
            }
            if (!empty(trim($requestParams['request_type']))) {
                $query->andWhere(['like', 'request_type', trim($requestParams['request_type'])]);
            }
            if (!empty(trim($requestParams['index']))) {
                $query->andWhere(['index' => trim($requestParams['index'])]);
            }
            if (!empty(trim($requestParams['spend_time']))) {
                $query->andWhere(['>=', 'spend_time', trim($requestParams['spend_time'])]);
            }
            if (!empty(trim($requestParams['code'])) || $requestParams['code'] === '0') {
                $query->andWhere(['code' => trim($requestParams['code'])]);
            }

            $totalCountQuery = clone $query;
            $totalCount = $totalCountQuery->count();
            $pageObj = new Pagination([
                'totalCount' => $totalCount,
                'pageSize' => $requestParams['limit']
            ]);
            $pageObj->setPage($requestParams['page'] - 1);
            $query->orderBy(['id' => SORT_DESC])->offset($pageObj->offset)->limit($pageObj->limit);
            $posts = LogHospitalApiLogNew::find()->select(['a.id', 'a.platform', 'a.request_type', 'a.index', 'a.log_detail', 'a.create_time', 'a.spend_time', 'a.code'])->from(['a' => LogHospitalApiLogNew::tableName(), 'b' => $query])->where('a.id=b.id')->asArray()->all();

            $params = ['requestParams' => $requestParams, 'totalCount' => $totalCount, 'dataProvider' => $posts, 'pages' => $pageObj];
        } else {
            $pages = new Pagination(['totalCount' => 0, 'pageSize' => $this->page_size]);
            $params = ['dataProvider' => [], 'requestParams' => $requestParams, 'totalCount' => 0, 'pages' => $pages];
        }

        return $this->render('apilist', $params);
    }

    /**
     * Aes数据解析
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-08-04
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAes()
    {
        $decrypt_data = [];
        $params = [
            'type' => 1
        ];

        if (Yii::$app->getRequest()->isPost) {
            $params = Yii::$app->getRequest()->getBodyParams();
            $type = ArrayHelper::getValue($params, 'type', 1);
            $encrypt_data = ArrayHelper::getValue($params, 'encrypt_data', '');
            if ($type == 1) {
                CryptoTools::setKey(Yii::$app->params['appidcryptokey']['2000000201']['appkey']);
            } else {
                CryptoTools::setKey(Yii::$app->params['appidcryptokey']['2000000202']['appkey']);
            }
            $decrypt_data = CryptoTools::AES256ECBDecrypt(urldecode($encrypt_data));
            if (!$decrypt_data) {
                $decrypt_data = CryptoTools::AES256ECBDecrypt($encrypt_data);
            }
        }
        $data = [
            'params' => $params,
            'decrypt_data' => $decrypt_data
        ];
        return $this->render('aes', $data);
    }
}
