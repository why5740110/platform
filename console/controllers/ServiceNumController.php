<?php

namespace console\controllers;

use common\libs\HashUrl;
use common\models\DoctorModel;
use common\models\GuahaoOrderModel;
use Yii;
use common\libs\CommonFunc;

class ServiceNumController extends \yii\console\Controller
{

    protected $params = [];
    protected $result = [];

    /**
     * 更新医生订单数到服务人次 service-num/run (all = 0默认每天,1更新所有数据)
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-12-30
     * @version 1.0
     * @param   integer    $all [description]
     * @return  [type]          [description]
     */
    public function actionRun($all = 0)
    {
        die;//废弃, 使用CommonFunc::getDoctorRegisterNum();
    }

    /**
     * 更新王氏id对应的挂号医生id
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2020-12-10
     * @version 1.0
     * @return  [type]     [description]
     */
//    public function actionMiaoid($start_id = 0, $end_id = 0)
//    {
//        $page  = 1;
//        $limit = 500;
//        $where = [];
//        $query = DoctorModel::find()->select('doctor_id,miao_doctor_id')->where($where);
//        if ($start_id && $end_id) {
//            $query->andWhere(['>=', 'doctor_id', trim($start_time)]);
//            $query->andWhere(['<=', 'doctor_id', trim($end_time)]);
//        }
//        $query->andWhere(['>', 'miao_doctor_id', 0]);
//        $total        = $query->count();
//        $maxPage      = ceil($total / $limit);
//        do {
//            if ($page > $maxPage) {
//                break;
//            }
//            $offset = max(0, ($page - 1)) * $limit;
//            $list   = $query->offset($offset)->limit($limit)->asArray()->all();
//            if (!$list) {
//                echo ('结束：' . date('Y-m-d H:i:s', time())) . '没有了！' . PHP_EOL;
//                break;
//            }
//            foreach ($list as $key => $value) {
//                if ($value['miao_doctor_id']) {
//                    CommonFunc::setMiaoid2HospitalDoctorID($value['miao_doctor_id'],$value['doctor_id']);
//                    echo ('nisiya_id：' . $value['miao_doctor_id'] . '-- doctor_id :' . $value['doctor_id'] .'--'. date('Y-m-d H:i:s', time())) . '-更新成功！' . PHP_EOL;
//                }
//            }
//            $num = count($list);
//            unset($list);
//            $page++;
//        } while ($num > 0);
//
//        echo "任务" . date('Y-m-d H:i:s') . "完成！\n";
//    }

}
