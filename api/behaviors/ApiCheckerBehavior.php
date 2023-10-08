<?php

namespace api\behaviors;
use common\libs\CryptoTools;
use api\validators\BaseParamValidator;

class ApiCheckerBehavior extends \yii\base\Behavior
{
    private $encryption='';
    private $checker;
    
    public function init()
    {
        $this->encryption=\Yii::$app->request->get('encryption');
        parent::init();
        if($this->encryption==='false'){
            return false;
        }
        $validator = new BaseParamValidator();
        $getData = \Yii::$app->request->get();
        $postData = \Yii::$app->request->post();
        $validator->load(array_merge($getData, $postData), '');
        if (!$validator->validate()) {
            $error = array_values($validator->getFirstErrors());
            header('Content-Type: application/json; charset=UTF-8');
            exit(json_encode([
                'code'=>400,
                'msg'=>$error[0],
                'data'=>[]
            ]));
            return false;
        }
        $this->checker=$validator->checker;
    }

    public function events()
    {
        return [
            \yii\web\Controller::EVENT_BEFORE_ACTION=>'beforeAction',
            \yii\web\Controller::EVENT_AFTER_ACTION=>'afterAction'
        ];
    }

    public function afterAction($action)
    {
        if($this->encryption==='false'){
            return false;
        }
        $this->checker->afterAction($action);

    }

    public function beforeAction()
    {
        if($this->encryption==='false'){
            return false;
        }
        $this->checker->beforeAction();
    }



}