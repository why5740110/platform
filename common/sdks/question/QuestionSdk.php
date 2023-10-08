<?php

namespace common\sdks\question;

use common\sdks\BaseSdk;
use yii\helpers\ArrayHelper;

class QuestionSdk extends BaseSdk
{
    protected $domain = '';
    protected function __construct()
    {
        parent::__construct();
        $this->domain = \Yii::$app->params['api_url']['askapi'];
    }

    //路由配置
    private $maps = [
        'asklist' => '/post/list', //问答列表
        'asklistes' => '/post/listes', //问答列表es
    ];

    public function asklist($params =[]){
        $result = $this->curl($this->maps['asklist'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code')==200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return null;
        }
    }
    public function asklistes($params =[]){
        $result = $this->curl($this->maps['asklistes'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code')==200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return null;
        }
    }


}