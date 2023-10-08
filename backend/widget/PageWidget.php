<?php
/**
 * 分页扩展
 * @file PageWidget.php
 */

namespace backend\widget;


use yii\base\Widget;

class PageWidget extends Widget
{
    public $page;
    public $count;

    public function run()
    {
        $limit = $this->page->getLimit();
        $count = $this->count;
        $currpage = $this->page->getPage() + 1;
        $return = <<<content
    <script>
    layui.use(['laypage', 'layer'], function(){
        var laypage = layui.laypage
        laypage.render({
            elem: 'page'
            ,limit:"$limit"
            ,count:"$count"
            ,layout: ['count', 'prev', 'page', 'next', 'skip']
            //,limits: [10, 20, 30, 40, 50, 100]
            ,theme: '#1E9FFF'
            ,curr:"$currpage"
            ,jump: function(obj, first){
                if(!first){
                    window.location.href=changeUrlArg(window.location.href,'page',obj.curr);
                    function changeUrlArg(url, arg, val){
                        var pattern = arg+'=([^&]*)';
                        var replaceText = arg+'='+val;
                        return url.match(pattern) ? url.replace(eval('/('+ arg+'=)([^&]*)/gi'), replaceText) : (url.match('[\?]') ? url+'&'+replaceText : url+'?'+replaceText);
                    }
                }
            }
        })
    });
</script>
content;

        return $return;
    }

}