<?php
/**
 * PC端公共控制器
 * @file CommonController.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-07-10
 */

namespace pc\controllers;

use common\widgets\BaiduStatisticsWidget;
use yii\web\Controller;

class CommonController extends Controller
{

    public $seoTitle = '';
    public $seoKeywords = '';
    public $seoDescription = '';
    public $pageSize = 20; //列表每页数据量
    public $nav = ''; //面包屑导航
    public $enableCsrfValidation = false;
    public function init()
    {
        $this->nav = $this->getRoute();
        $staticPath = rtrim(\Yii::$app->params['domains']['cdn'], '/') . '/pc/hospital/static/';
        \Yii::setAlias('@static', $staticPath);

        $staticPath = rtrim(\Yii::$app->params['domains']['cdn'], '/') . '/pc/';
        \Yii::setAlias('@commonStatic', $staticPath);
        BaiduStatisticsWidget::widget([
            'controller'=>$this
        ]);
    }

    /**
     * 过滤seo信息中出现的特殊字符
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-07-10
     * @param $content
     * @return mixed|string
     */
    public function filterSeoContent($content)
    {
        //过滤html标签
        $content = strip_tags($content);
        $content = str_replace('>', '', $content);
        $content = str_replace('<', '', $content);

        $content = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "", $content);
        //过滤html特殊字符
        $content = htmlspecialchars($content);
        //过滤空白字符
        preg_filter("/\s/",'', $content);
        //过滤双引号
        $content=str_replace('"',"'", $content);
        return mb_substr($content, 0, 200);
    }

    /**
     * 过滤内容
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-07-10
     * @param $content
     * @return mixed|string
     */
    public function filterContent($content)
    {
        //过滤html标签
        $content = strip_tags($content);
        $content = str_replace('>', '', $content);
        $content = str_replace('<', '', $content);
        $content = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "", $content);
        //过滤html特殊字符
        $content = htmlspecialchars($content);
        //过滤空白字符
        preg_filter("/\s/",'', $content);
        //过滤双引号
        $content=str_replace('"',"'", $content);
        return mb_substr($content, 0, 100);
    }

}