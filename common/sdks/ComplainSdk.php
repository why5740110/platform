<?php

namespace common\sdks;

use common\sdks\BaseSdk;
use yii\helpers\ArrayHelper;

class ComplainSdk extends BaseSdk
{

    protected $domain = '';
    public function __construct()
    {
        parent::__construct();
        $this->domain = \Yii::$app->params['api_url']['complain'];
    }

    //路由配置
    private $maps = [
        'accept_appeal'=>'/accept/appeal',//申诉发起接口
    ];

    /**
     * 发起申诉接口
     * @author yangquanliang <yangquanliang@yuanxinjituan.com>
     * @date    2021-06-21
     * @version v1.0
     * @param   array      $params [description]
     * @return  [type]             [description]
     */
    public function getAppeal($params = [])
    {
        $result = $this->curl($this->maps['accept_appeal'],$params);
        $res = [
            'errno'=>0,
            'errmsg'=>'',
            'tp_complain_id'=>''
        ];
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            $res['tp_complain_id'] = ArrayHelper::getValue($result,'data.complain_id','');
            return  $res;
        }else{
            $res['errno'] = 102;##0代表成功,101签名验证错误,102其他错误。错误码可协商。
            $res['errmsg'] = ArrayHelper::getValue($result,'msg','');
            return  $res;
        }

    }


}