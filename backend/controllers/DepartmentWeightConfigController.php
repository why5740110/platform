<?php
/**
 * 医院科室权重配置（在M端首页展示科室）
 * @file DepartmentWeightConfigController.php
 * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-07-13
 */

namespace backend\controllers;

use common\libs\CommonFunc;
use common\models\minying\DepartmentWeightConfigModel;
use common\models\TbLog;
use Yii;
use yii\data\Pagination;

class DepartmentWeightConfigController extends BaseController
{
    public $page_size = 10;

    /**
     * 配置权重列表
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-13
     */
    public function actionList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $requestParams['page'] = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;
        $departmentModel = new DepartmentWeightConfigModel();
        $list = $departmentModel::getList($requestParams);

        $totalCount = $departmentModel::getCount($requestParams);
        $pages = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
        $fkeshi = CommonFunc::getFkeshiInfos();
        $data =  [
            'dataProvider' => $list,
            'requestParams' => $requestParams,
            'totalCount' => $totalCount,
            'pages' => $pages,
            'fkeshi_list' => $fkeshi,
        ];
        return $this->render('list', $data);
    }

    /** 添加科室配置权重
     * @return string|void
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-13
     */
    public function actionAddDepartment(){
        $weight = \Yii::$app->request->post('weight');
        $fkid = \Yii::$app->request->post('fkid');
        $skid = \Yii::$app->request->post('skid');
        $fkeshi = CommonFunc::getFkeshiInfos();
        $skeshi = CommonFunc::getSkeshiInfos();

        $count = DepartmentWeightConfigModel::find()->Where(['status' => 1])->count();
        if (isset($count) && $count > 7){
            return $this->returnJson('2', '科室配置最多添加8个科室！');
        }

        $exists = DepartmentWeightConfigModel::find()->Where(['first_department_id' => $fkid])->andWhere(['second_department_id'=>$skid])->andWhere(['status' => 1])->exists();
        if ($exists) {
            return $this->returnJson('2', '已添加过了！');
        }
        $fkeshi_arr = array_column($fkeshi, 'name', 'id');
        $skeshi_arr = array_column($skeshi, 'name', 'id');
        $dep_config = new DepartmentWeightConfigModel();
        $dep_config->first_department_id = $fkid;
        $dep_config->first_department_name = $fkeshi_arr[$fkid];
        $dep_config->second_department_id = $skid;
        $dep_config->second_department_name = $skeshi_arr[$skid];
        $dep_config->weight = $weight;
        $dep_config->admin_id = $this->userInfo['id'];
        $dep_config->admin_name = $this->userInfo['realname'];
        $dep_config->create_time = time();
        $dep_config->update_time = time();
        $dep_config->save();

        // 将科室权重配置数据保存到redis中
        $res = DepartmentWeightConfigModel::getALl('second_department_id,second_department_name,weight');
        if ($res){
            $key = Yii::$app->params['cache_key']['department_config_list'];
            CommonFunc::setCodisCache($key, $res);
        }
        return $this->returnJson('1', '添加成功！');
    }

    /**
     * 移除科室配置权重
     * @author zhangshaohua <zhangshaohua@yuanxinjituan.com>
     * @date 2022-07-13
     */
    public function actionChangeStatus(){
        $id = intval($_POST['id']);
        $res = DepartmentWeightConfigModel::find()->Where(['id' => $id])->andWhere(['status' => 1])->one();
        if ($res) {
            $res->status = 2;
            $update_res = $res->save();
            if ($update_res){
                $editContent = $this->userInfo['realname'] . '修改了id为【' . $id . '】的科室展示配置:由【正常使用】改为了【移除】';
                TbLog::addLog($editContent, '科室展示配置修改');
            }

            // 将科室权重配置数据保存到redis中
            $res = DepartmentWeightConfigModel::getALl('second_department_id,second_department_name,weight');
            $key = Yii::$app->params['cache_key']['department_config_list'];
            if ($res){
                CommonFunc::setCodisCache($key, $res);
            } else{
                CommonFunc::setCodisCache($key, '');
            }
            return $this->returnJson('1', '成功');
        } else {
            return $this->returnJson('2', '医生信息不存在！');
        }
    }
}