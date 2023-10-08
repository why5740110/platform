<?php
/**
 * bapi广告相关接口模型
 * @file BapiAdSdkModel.php.
 * @author zhangfan <zhangfan01@yuanxin-inc.com>
 * @version 1.0
 * @date 2019/9/18
 */

namespace common\sdks;

use common\sdks\BaseSdk;
use yii\helpers\ArrayHelper;

class BapiAdSdkModel extends BaseSdk
{

    protected $domain = '';
    public function __construct()
    {
        parent::__construct();
        $this->domain = \Yii::$app->params['api_url']['bapi'];
    }

    //路由配置
    private $maps = [
        'lunbolist' =>'/ad/index',//首页广告轮播
        'uploadOss' =>'/upload/upload-img',// oss 图片上传
    ];


    public function getPcLunBo($limit=10,$target_id = '124',$location='健康直播首页',$location_text='轮播图')
    {
        $params = [
            'limit' => $limit,
            'target_id' => $target_id,
            'location' => $location,
            'location_text' => $location_text
        ];
        $result = $this->curl($this->maps['lunbolist'].'?'.http_build_query($params));
        if(is_array($result) && ArrayHelper::getValue($result,'code') == 200){
            return ArrayHelper::getValue($result,'data');
        }else{
            return  [];
        }

    }

    /**
     * @param array $params
     * @return array|false|string
     * @throws \Exception
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-08-25
     */
    public function uploadOss($params = [])
    {
        $result     = $this->curl($this->maps['uploadOss'], $params);

        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return $result;
        }
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 400) {
            return $result;
        }
        return false;
    }




}