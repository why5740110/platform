<?php


namespace common\models;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use Yii;


class TbLog extends \common\models\BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return "{{%tb_log}}";
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id', 'create_time'], 'integer'],
            [['description'], 'string', 'max' => 200],
            [['admin_name'], 'string', 'max' => 50],
            [['info'], 'string', 'max' => 1000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Description',
            'admin_id' => 'Admin ID',
            'admin_name' => 'Admin Name',
            'create_time' => 'Create Time',
            'info' => 'Info',
        ];
    }

    public static function formatLog($log_data = [])
    {
        $result     = '';
        $log_format = "修改 {%s} 由 {%s} 修改为 {%s}，";
        if (!empty($log_data) && is_array($log_data)) {
            foreach ($log_data as $item) {
                $result .= sprintf($log_format, $item[0], $item[1], $item[2]);
            }
            $result = rtrim($result, '，');
        }
        return $result;
    }


    public static function addLog($info,$description = '',$admin_info = [])
    {
        $log = new TbLog();
        if ($admin_info) {
            $admin_id = ArrayHelper::getValue($admin_info,'admin_id',0);
            $admin_name = ArrayHelper::getValue($admin_info,'admin_name','');
        }else{
            $cookie = \Yii::$app->request->cookies;
            $admin_id = $cookie->getValue('uid', 0);
            $admin_name = $cookie->getValue('name', '');
        }
        $log->admin_id = $admin_id;
        $log->admin_name = $admin_name;
        $log->info = $info;
        $log->description = $description;
        $log->create_time = time();
        return $log->save();
    }


    public static function getList($params){
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $pageSize = isset($params['limit']) ? intval($params['limit']) : 10;
        $doctorQuery = self::find()
            ->select('*');
        if (!empty($params['admin'])) {
            if (strlen(intval(trim($params['admin']))) == strlen(trim($params['admin']))) {
                $doctorQuery->andWhere(['admin_id'=>intval(trim($params['admin']))]);
            } else {
                $doctorQuery->andWhere(['like','admin_name',trim($params['admin'])]);
            }
        }

        if (!empty($params['description'])) {
            $doctorQuery->andWhere(['description'=>intval($params['description'])]);
        }

        $totalCountQuery = clone $doctorQuery;
        $totalCount = $totalCountQuery->count();

        $pageObj = new Pagination([
            'totalCount'=>$totalCount,
            'pageSize'=>$pageSize
        ]);
        $pageObj->setPage($page - 1);
        $posts = $doctorQuery->offset($pageObj->offset)->limit($pageObj->limit)->orderBy('create_time desc')->asArray()->all();
        return $posts;
    }

    public static function getCount($params){
        $doctorQuery = self::find()->select('*');
        if (!empty($params['doctor'])) {
            if (strlen(intval(trim($params['doctor']))) == strlen(trim($params['doctor']))) {
                $doctorQuery->andWhere(['admin_id'=>intval(trim($params['doctor']))]);
            } else {
                $doctorQuery->andWhere(['like','admin_name',trim($params['doctor'])]);
            }
        }

        if (!empty($params['description'])) {
            $doctorQuery->andWhere(['description'=>intval($params['description'])]);
        }

        $posts = $doctorQuery->asArray()->count();
        return $posts;
    }
}
