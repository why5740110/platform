<?php

namespace pc\controllers;

use yii\web\Controller;
use yii;
use api\controllers\DoctorController as Doc;
use yii\helpers\ArrayHelper;
use common\models\DoctorModel;
use common\libs\HashUrl;
use yii\web\NotFoundHttpException;
use common\libs\CommonFunc;

class DoctorController extends CommonController
{
    public $infos;
    public $doctor_id;
    public $doctor_realname;
    public $hospital_name;

    /**
     * 初始化医生详情信息
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/8/19
     */
    public function init(){
        $this->doctor_id = \Yii::$app->request->get('doctor_id', '');

        $this->doctor_id = HashUrl::getIdDecode($this->doctor_id);

        $this->infos = DoctorModel::getInfo($this->doctor_id);

        //只展示主医生
        $primary_id = ArrayHelper::getValue($this->infos,'primary_id');
        if(!$this->infos || $primary_id ){
            throw new NotFoundHttpException();
        }
        $this->doctor_realname = ArrayHelper::getValue($this->infos,'doctor_realname');
        $this->hospital_name = ArrayHelper::getValue($this->infos,'doctor_hospital_data.name');
        parent::init();
    }
    /**
     * 概览
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/7/27
     */
    public function actionHome()
    {
        $infos = $this->infos;

        $this->seoTitle = $this->doctor_realname.",医生预约挂号,出诊时间,".$this->hospital_name.",王氏医生";
        $this->seoKeywords = $this->doctor_realname.','.$this->doctor_realname.'评价,'.$this->doctor_realname.'怎么样'.','.$this->doctor_realname.'预约挂号';
        $this->seoDescription = "王氏医生为您提供".$this->doctor_realname."的介绍、出诊时间、评价、预约挂号等，患者真实评价及就医分享，介绍".$this->doctor_realname."擅长治疗疾病，助您找到合适自己的医生在线咨询、预约挂号等。";
        $data = [
            'doctor_info' => $infos,
        ];

        return $this->render('home',$data);

    }

    /**
     * 详细介绍
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/7/27
     */
    public function actionIntro()
    {
        $infos = $this->infos;
        $infos['doctor_profile'] = str_replace('　　','', $infos['doctor_profile']);
        $this->seoTitle = $this->doctor_realname."详细介绍,王氏医生";
        $this->seoKeywords = $this->doctor_realname."医生简介,".$this->doctor_realname."医生";
        $this->seoDescription = mb_substr(CommonFunc::filterContent($infos['doctor_profile']),0,200);
        $data = [
            'doctor_info' => $infos,
        ];
        return $this->render('intro',$data);

    }

    /**
     * 网友咨询
     * @author liushaokai<liushakai@yuanxin-inc.com>
     * @date 2020/7/27
     */
    public function actionConsult()
    {
        $infos = $this->infos;
        $this->seoTitle = $this->doctor_realname."服务,图文咨询,电话咨询,王氏医生";
        $this->seoKeywords = $this->doctor_realname."图文咨询,".$this->doctor_realname."电话咨询";
        $this->seoDescription = "王氏医生为您提供".$this->hospital_name.$this->doctor_realname."在线咨询、图文问诊、预约挂号等，百万患者真实评价及就医分享，了解".$this->hospital_name.$this->doctor_realname."具体服务信息，为您的就医治病提供方便信息。";
        $data = [
            'doctor_info' => $infos,
        ];
        return $this->render('consult',$data);

    }

    public function actionComment()
    {
        $infos = $this->infos;
        $this->seoTitle = $this->hospital_name.$this->doctor_realname."怎么样,口碑好不好-王氏医生";
        $this->seoKeywords = $this->hospital_name.$this->doctor_realname."怎么样,".$this->hospital_name."怎么样,".$this->doctor_realname."口碑";
        $this->seoDescription = $this->hospital_name.$this->doctor_realname."怎么样/口碑好不好？患者真实口碑评价及就医分享帮助您了解".$this->hospital_name.$this->doctor_realname."擅长、医术及治疗效果，帮助您在线咨询及了解医生本人。";
        $data = [
            'doctor_info' => $infos,
        ];

        return $this->render('comment',$data);

    }
}

?>
