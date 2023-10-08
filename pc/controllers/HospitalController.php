<?php
/**
 * @file HospitalController.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/7/27
 */


namespace pc\controllers;


use common\libs\CommonFunc;
use common\libs\HashUrl;
use common\models\BaseDoctorHospitals;
use common\models\Department;
use common\models\DiseaseEsModel;
use common\models\HospitalDepartmentRelation;
use common\sdks\snisiya\SnisiyaSdk;
use common\sdks\snisiya\sRpcSdk;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class HospitalController extends CommonController
{

    public $pageSize = 10;

    public $hospital_id;
    public $data;

    //不展示详情医院ID
    public $hospitalMap = [4034];

    public function init()
    {
        $this->hospital_id = \Yii::$app->request->get('hospital_id');

        if($this->hospital_id){
            $this->hospital_id = HashUrl::getIdDecode($this->hospital_id);
        }

        if (in_array($this->hospital_id, $this->hospitalMap)) {
            //禁用医院展示
            $this->data = [];
        } else {
            $this->data = BaseDoctorHospitals::HospitalDetail($this->hospital_id);
        }

        if(!$this->hospital_id || !$this->data){
            throw new NotFoundHttpException();
        }
        parent::init();
    }

    /**
     * 医院概况页
     * @return string
     * @author xiujianying
     * @date 2020/7/29
     */
    public function actionIndex()
    {

        $hospital_id = $this->hospital_id;
        //详情
        $data = $this->data;

        //科室
        $sub = HospitalDepartmentRelation::hospitalDepartment($hospital_id);
        //获取推荐医生
        $doctor_list = [];
        if (is_array($sub)) {
            $i = 0;
            foreach ($sub as $k => $v) {
                $doctor_list[$v['frist_department_id']] = [];
                sRpcSdk::getInstance()->doctorList($doctor_list[$v['frist_department_id']],['hospital_id'=>$hospital_id,'frist_department_id'=>$v['frist_department_id'],'pagesize'=>5]);

                $i++;
                if ($i > 9) {
                    break;
                }
            }
        }
        sRpcSdk::getInstance()->startAsync();
        //$hospital_doc = ArrayHelper::getValue($hospital_doc,'list.doctor_list');
        $hospital_doc = [];
        $hosp_name = ArrayHelper::getValue($data,'name');
        $desc = mb_substr(trim(strip_tags(ArrayHelper::getValue($data,'description'))),0,50,'UTF8');
        $this->seoTitle = $hosp_name."网上预约挂号_怎么样_挂号平台-王氏医生";
        $this->seoKeywords = "$hosp_name,{$hosp_name}网上挂号,{$hosp_name}预约挂号,{$hosp_name}挂号平台,{$hosp_name}怎么样";
        $this->seoDescription = "{$hosp_name},".$desc;

        return $this->render('index', ['data' => $data,'doctor_list'=>$doctor_list,'hospital_doc'=>$hospital_doc , 'sub' => $sub, 'hospital_id' => $hospital_id]);
    }

    /**
     * 医院介绍页
     * @return string
     * @author xiujianying
     * @date 2020/7/29
     */
    public function actionDetail()
    {
        $hospital_id = $this->hospital_id;
        //详情
        $data = $this->data;

        $hosp_name = ArrayHelper::getValue($data,'name');

        $this->seoTitle = "{$hosp_name}官网是什么_电话_地址-王氏医生";
        $this->seoKeywords = "{$hosp_name}官网是什么,{$hosp_name}电话,{$hosp_name}地址";
        $this->seoDescription = "王氏医生为您提供{$hosp_name}的官网地址、详细介绍、电话、地址、乘车路线等，方便患者寻找{$hosp_name}，方便就医。";

        return $this->render('detail', ['data' => $data, 'hospital_id' => $hospital_id]);
    }

    /**
     * 医院科室页
     * @return string
     * @author xiujianying
     * @date 2020/7/29
     */
    public function actionDepartments()
    {
        $hospital_id = $this->hospital_id;
        //详情
        $data = $this->data;
        //科室
        $sub = HospitalDepartmentRelation::hospitalDepartment($hospital_id);

        $hosp_name = ArrayHelper::getValue($data,'name');

        $this->seoTitle = "{$hosp_name}特色科室列表-重点擅长科室简介服务-王氏医生";
        $this->seoKeywords = "{$hosp_name}特色科室,{$hosp_name}科室简介,{$hosp_name}重点科室,{$hosp_name}擅长科室";
        $this->seoDescription = "王氏医生为您提供{$hosp_name}特色科室列表,重点科室,擅长科室,科室简介等。百分百患者真实点评，打造科室医生真实信息，助您找到合适科室挂号就诊。";

        return $this->render('departments', ['data' => $data, 'sub' => $sub, 'hospital_id' => $hospital_id]);
    }

    /**
     * 医院医生列表页
     * @return string
     * @throws \Exception
     * @author xiujianying
     * @date 2020/7/29
     */
    public function actionDoclist()
    {
        $hospital_id = $this->hospital_id;
        $frist_department_id = \Yii::$app->request->get('frist_department_id');
        $second_department_id = \Yii::$app->request->get('second_department_id');
        $page = \Yii::$app->request->get('page',1);

        //医院下科室
        $sub = HospitalDepartmentRelation::hospitalDepartment($hospital_id);
        //二级科室
        $second_sub = ArrayHelper::getValue($sub, $frist_department_id . '.second_arr', []);

        //详情
        $data = $this->data;


        //医生列表接口
        $doclistSdk = SnisiyaSdk::getInstance();
        $params = ['hospital_id'=>$hospital_id,'page'=>$page,'pagesize'=>$this->pageSize];
        if($frist_department_id){
            $params['frist_department_id'] = $frist_department_id;
        }
        if($second_department_id){
            $params['second_department_id'] = $second_department_id;
        }
        $docRes = $doclistSdk->getDoctorList($params);

        $doc_list = ArrayHelper::getValue($docRes,'doctor_list',[]);
        $totalCount = ArrayHelper::getValue($docRes,'totalCount',[]);

        $pages = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => $this->pageSize]);

        $hosp_name = ArrayHelper::getValue($data,'name');
        $this->seoTitle = "{$hosp_name}专家介绍-知名专家列表-王氏医生";
        $this->seoKeywords = "{$hosp_name}专家介绍，{$hosp_name}专家列表，{$hosp_name}知名专家";
        $this->seoDescription = "王氏医生为您提供{$hosp_name}专家介绍、擅长疾病、门诊时间。通过{$hosp_name}专家排行榜助您择优就诊网上预约挂号。";


        return $this->render('doclist', ['data' => $data,'doc_list'=>$doc_list,'pages' => $pages, 'sub' => $sub, 'second_sub' => $second_sub, 'hospital_id' => $hospital_id]);
    }

    /**
     * 医院疾病页
     * @return string
     * @throws \Exception
     * @author xiujianying
     * @date 2020/7/29
     */
    public function actionDiseases()
    {
        $limit = 10;
        $hospital_id = $this->hospital_id;
        $second_department_id = \Yii::$app->request->get('second_department_id');
        $frist_department_id = \Yii::$app->request->get('frist_department_id');

        //基础科室
        $sub = Department::department();
        //二级科室
        $second_sub = ArrayHelper::getValue($sub, $frist_department_id . '.second_arr', []);

        //详情
        $data = $this->data;

        //科室找疾病 DiseaseEsModel
        /*$esQuery = DiseaseEsModel::find();
        if($frist_department_id){
            $where['frist_department_id'] = $frist_department_id;
            if($second_department_id){
                $where['second_department_id'] = $second_department_id;
            }
            $esQuery = $esQuery->where($where);
        }
        $count = $esQuery->count();*/
        //$pageOptions = ['totalCount'=>$count, 'pageSize'=>$limit];
        $pages = new Pagination(['totalCount' => 100, 'defaultPageSize' => $this->pageSize]);

        //$diseasesList =  $esQuery->limit($pages->limit)->offset($pages->offset)->asArray()->all();
        $diseasesList = [];

        $hosp_name = ArrayHelper::getValue($data,'name');
        $this->seoTitle = "{$hosp_name}擅长什么疾病-看什么最好-王氏医生";
        $this->seoKeywords = "{$hosp_name}最擅长什么，{$hosp_name}看什么最好，{$hosp_name}主治什么";
        $this->seoDescription = "{$hosp_name}擅长治疗什么疾病?让王氏医生来帮您，百分百患者真实评价及分享，快速了解{$hosp_name}看什么最好，助您选择最合适的{$hosp_name}专家";

        return $this->render('diseases', ['data' => $data, 'pages' => $pages, 'diseasesList' => $diseasesList, 'sub' => $sub, 'second_sub' => $second_sub, 'hospital_id' => $hospital_id]);
    }

}