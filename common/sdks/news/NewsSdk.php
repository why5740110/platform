<?php
/**
 * @file ToutiaoSdk.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/9/7
 */
namespace common\sdks\news;

use common\sdks\BaseSdk;
use yii\helpers\ArrayHelper;

class NewsSdk extends BaseSdk
{
    protected $domain = '';

    protected function __construct()
    {
        parent::__construct();
        $this->domain = ArrayHelper::getValue(\Yii::$app->params,'api_url.news');
    }

    //路由配置
    private $maps = [
        'news_list' => '/news/get-latest-new-release', //
    ];

    /**
     * 首页咨询
     * @param array $params
     * @return mixed|null
     * @throws \Exception
     * @author xiujianying
     * @date 2020/9/7
     */
    public function news_list($params = [])
    {
        $result = $this->curl($this->maps['news_list'] . '?' . http_build_query($params));
        if (is_array($result) && ArrayHelper::getValue($result, 'code') == 200) {
            return ArrayHelper::getValue($result, 'data.list');
        } else {
            return null;
        }
    }




}