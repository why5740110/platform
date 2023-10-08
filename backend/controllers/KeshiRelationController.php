<?php

namespace backend\controllers;

use common\libs\CommonFunc;
use common\models\BaseDoctorHospitals;
use common\models\Department;
use common\models\HospitalDepartmentRelation;
use common\models\TmpDepartmentThirdPartyModel;
use common\models\GuahaoHospitalModel;
use common\models\TbLog;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use common\components\Excel;

class KeshiRelationController extends BaseController
{
    /**
     * @date 2021/10/09
     * @author zhanghongjian
     */
    const MAX_NUM = 5001;//excel上传最大数量
    const UP_NUM = 1; //每次累加数

    public $page_size = 10;

    public function actionList()
    {
        $requestParams = Yii::$app->request->getQueryParams();
        $depModel      = new TmpDepartmentThirdPartyModel();

        $list = $depModel::getList($requestParams);
        foreach ($list as &$item) {
            $keshi               = HospitalDepartmentRelation::find()->select('frist_department_name,second_department_name')->where(['id' => $item['hospital_department_id']])->asArray()->one();
            $item['hospital_id'] = $this->getRelation($item['tp_hospital_code'], $item['tp_platform']);
            $item['keshi']       = ArrayHelper::getValue($keshi, 'frist_department_name') . '-' . ArrayHelper::getValue($keshi, 'second_department_name');

        }
        $totalCount = $depModel::getCount($requestParams);
        //分页
        $requestParams['page']  = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;

        $pages = new Pagination(['totalCount' => $totalCount, 'pageSize' => $requestParams['limit']]);
        $data  = ['hos' => $hos ?? [], 'dataProvider' => $list, 'requestParams' => $requestParams, 'totalCount' => $totalCount, 'pages' => $pages];
        return $this->render('list', $data);
    }

    public function getRelation($tp_hospital_code, $tp_platform)
    {
        $res = GuahaoHospitalModel::find()->where(['tp_hospital_code' => $tp_hospital_code, 'tp_platform' => $tp_platform])->one();
        if ($res) {
            return $res->hospital_id;
        }
        return 0;
    }
    public function actionKeshiList()
    {
        $request                      = Yii::$app->request;
        $requestParams                = Yii::$app->request->getQueryParams();
        $requestParams['hospital_id'] = isset($requestParams['hospital_id']) ? intval($requestParams['hospital_id']) : '';
        $requestParams['fkid']        = isset($requestParams['miao_frist_department_id']) ? intval($requestParams['miao_frist_department_id']) : '';
        $requestParams['skid']        = isset($requestParams['miao_second_department_id']) ? intval($requestParams['miao_second_department_id']) : '';
        $requestParams['fkeshi_id']   = isset($requestParams['frist_department_id']) ? intval($requestParams['frist_department_id']) : '';
        $requestParams['skeshi_id']   = isset($requestParams['second_department_id']) ? intval($requestParams['second_department_id']) : '';
        $id                           = intval($request->get('hospital_id', ''));
        $data                         = [];

        //$hospital = BaseDoctorHospitals::find()->where(['id' => $id])->asArray()->one();
        $hospital = BaseDoctorHospitals::getHospitalDetail($id);
        $requestParams['page']  = (isset($requestParams['page']) && !empty($requestParams['page'])) ? $requestParams['page'] : 1;
        $requestParams['limit'] = (isset($requestParams['limit']) && (!empty($requestParams['limit']))) ? $requestParams['limit'] : $this->page_size;

        // //拼装条件
        $field = '*';
        $where = [];
        if ($requestParams['hospital_id']) {
            $where = [
                'hospital_id' => $requestParams['hospital_id'],
            ];
        }

        $query = HospitalDepartmentRelation::find()->where($where);
        if (!empty(trim($requestParams['fkid']))) {
            $query->andWhere(['miao_frist_department_id' => trim($requestParams['fkid'])]);
        }
        if (!empty(trim($requestParams['skid']))) {
            $query->andWhere(['miao_second_department_id' => trim($requestParams['skid'])]);
        }
        if (!empty(trim($requestParams['fkeshi_id']))) {
            $query->andWhere(['frist_department_id' => trim($requestParams['fkeshi_id'])]);
        }
        if (!empty(trim($requestParams['skeshi_id']))) {
            $query->andWhere(['second_department_id' => trim($requestParams['skeshi_id'])]);
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

        $data                     = ['params' => ['dataProvider' => $dataProvider, 'requestParams' => $requestParams], 'requestParams' => $requestParams];
        $data['hospital']         = $hospital ?? [];
        $fkeshi_list              = Department::department_platform() ?? [];
        $data['fkeshi_list']      = $fkeshi_list;
        //$data['skeshi_list']      = CommonFunc::get_all_skeshi_list($requestParams['fkeshi_id']);
        $data['skeshi_list']      = !empty($requestParams['fkeshi_id']) ? CommonFunc::get_all_skeshi_list($requestParams['fkeshi_id']) : [];
        $data['miao_fkeshi_list'] = CommonFunc::getFkeshiInfos();
        $data['miao_skeshi_list'] = CommonFunc::getSkeshiInfos();
        return $this->render('edit', $data);
    }

    /**
     * 科室对应关系列表
     * @return string
     * @throws \Exception
     * @author xiujianying
     * @date 2021/9/22
     */
    public function actionDepList(){

        $requestParams = \Yii::$app->request->get();
        $pagesize = 20;
        $where = [];

        $model = new Department();
        $query = $model->find()->where(['!=','parent_id',0]);

        //搜索条件
        $status = intval(ArrayHelper::getValue($requestParams,'status'));
        if($status){
            if($status==1){
                $where['status'] = 1;
            }elseif ($status == 2){
                $where['status'] = 0;
            }
        }
        $is_match = intval(ArrayHelper::getValue($requestParams,'is_match'));
        if($is_match){
            if($is_match==1){
                $where['is_match'] = 1;
            }elseif ($is_match == 2){
                $where['is_match'] = 0;
            }
        }
        $miao_first_department_id = intval(ArrayHelper::getValue($requestParams,'miao_first_department_id',0));
        if($miao_first_department_id){
            $where['miao_first_department_id'] = $miao_first_department_id;
        }
        $miao_second_department_id = intval(ArrayHelper::getValue($requestParams,'miao_second_department_id'));
        if($miao_second_department_id){
            $where['miao_second_department_id'] = $miao_second_department_id;
        }

        $department_name = ArrayHelper::getValue($requestParams,'keshi');
        if($department_name){
            $where['department_name'] = $department_name;
        }

        if($where){
            $query = $query->andWhere($where);
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => $pagesize,
            ],
            'sort'       => [
                'defaultOrder' => [
                    'department_id' => SORT_DESC,
                ],
            ],
        ]);
        $data['requestParams'] = $requestParams;
        $data['dataProvider'] = $dataProvider;
        $data['miao_fkeshi_list'] = CommonFunc::getFkeshiInfos();
        $data['miao_skeshi_list'] = $miao_first_department_id?CommonFunc::getSkeshiInfos($miao_first_department_id):[];


        return $this->render('dep-list', $data);
    }

    /**
     * 匹配王氏科室
     * @return string|void
     * @throws \Exception
     * @author xiujianying
     * @date 2021/9/22
     */
    public function actionDepMatch(){

        if(\Yii::$app->request->post('submit')==1){
            $id = \Yii::$app->request->post('id');
            $miao_first_department_id = \Yii::$app->request->post('miao_first_department_id');
            $miao_second_department_id = \Yii::$app->request->post('miao_second_department_id');
            $query= Department::find()->where(['department_id'=>$id])->one();
            $query->miao_first_department_id = $miao_first_department_id;
            $query->miao_second_department_id = $miao_second_department_id;
            $query->is_match = 1;
            if($query->parent_id==-1) {
                $firstName = CommonFunc::getKeshiName($miao_first_department_id);
                $dep_id = Department::find()->where(['parent_id' => 0, 'department_name' => $firstName])->select(['department_id'])->scalar();
                if ($dep_id) {
                    $query->parent_id = $dep_id;
                }else{
                    return $this->returnJson(0,'一级科室匹配异常');
                }
            }
            $query->save();
            return $this->returnJson();
        }

        $dep_id = \Yii::$app->request->get('id');

        $depData = Department::find()->where(['department_id'=>$dep_id])->asArray()->one();

        if($depData && ArrayHelper::getValue($depData,'is_match')==0 ){

        }else{
            //数据不存在或已匹配 返回异常
            $depData = [];
        }

        $data['id'] = $dep_id;
        $data['depData'] = $depData;
        $data['miao_fkeshi_list'] = CommonFunc::getFkeshiInfos();

        return $this->renderPartial('dep-match', $data);
    }

    /**
     * 禁用、恢复禁用科室 ajax
     * @author xiujianying
     * @date 2021/9/22
     */
    public function actionKeshiEdit(){

        if(\Yii::$app->request->post('submit')==1){

            $id = \Yii::$app->request->post('id');
            $disabled = \Yii::$app->request->post('disabled','1');  //1：禁用 2恢复

            $status = $disabled == 1 ? 0: 1;

            $query= Department::find()->where(['department_id'=>$id])->one();
            $query->status = $status;
            //恢复禁用时 改为未关联
            if ($disabled == 2) {
                $query->is_match = 0;
            }
            $query->save();
            return $this->returnJson();
        }

    }


    /**
     * 导出未匹配科室
     * @author zhanghongjian
     * @date 2021/10/21
     * @return array
     */
    public function actionExportOld(){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $heardArr = [
            'ID',
            '医院一级科室',
            '医院二级科室',
            '王氏一级科室ID',
            '王氏一级科室名称',
            '王氏二级科室ID',
            '王氏二级科室名称',
            '数据状态（1禁用，正常为空）',
        ];
        //写入表头
        foreach($heardArr as $key => $heard){
            $sheet->setCellValueByColumnAndRow($key + 1, 1, $heard);
        }
        // 单元格内容写入
        $objModelDepartment = new Department();
        $departmentList = $objModelDepartment->find()->where(['is_match'=>0,'status'=>1])->andWhere(['!=','parent_id',0])->orderBy('department_id desc')->asArray()->all();
        $row = 2;
        foreach($departmentList as $department){
            $dataCol = 'A';
            $saveArr = [
                $department['department_id'],
                '',
                $department['department_name'],
                '',
                '',
                '',
                '',
                '',
            ];
            foreach ($saveArr as $value) {
                $sheet->setCellValue($dataCol . $row, $value);
                $dataCol++;
            }
            $row ++;
        }
        $fileName = date('YmdHi'). '未匹配科室数据.xlsx';
        //设置office格式
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename='.$fileName);
        header('Cache-Control: max-age=0');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Pragma: public');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * 导出未匹配科室
     * @author wanghongying
     * @date 2022/06/13
     * @return array
     */
    public function actionExport()
    {
        ini_set("memory_limit", "-1");
        set_time_limit(0);

        // 单元格内容写入
        $objModelDepartment = new Department();
        $departmentList = $objModelDepartment->find()->where(['is_match'=>0,'status'=>1])->andWhere(['!=','parent_id',0])->orderBy('department_id desc')->asArray()->all();

        foreach($departmentList as &$department) {
            $department['department_first_name'] = '';
            $department['nisiya_department_first_id'] = '';
            $department['nisiya_department_first_name'] = '';
            $department['nisiya_department_second_id'] = '';
            $department['nisiya_department_second_name'] = '';
            $department['data_status'] = '';
        }

        $header = [
            'ID' => 'department_id',
            '医院一级科室' => 'department_first_name',
            '医院二级科室' => 'department_name',
            '王氏一级科室ID' => 'nisiya_department_first_id',
            '王氏一级科室名称' => 'nisiya_department_first_name',
            '王氏二级科室ID' => 'nisiya_department_second_id',
            '王氏二级科室名称' => 'nisiya_department_second_name',
            '数据状态（1禁用，正常为空）' => 'data_status',
        ];
        $excel  = new Excel();
        $fileName = date('YmdHi'). '未匹配科室数据.xlsx';
        $excel->export($departmentList, $header)->downFile($fileName,'Excel5');
        exit;
    }
}