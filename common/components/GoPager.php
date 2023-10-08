<?php
namespace common\components;

use yii\widgets\LinkPager;
use yii\helpers\Html;

class GoPager extends LinkPager
{
    /**
     * @var boole|string. weather show limitPage
     *You can do not set the property by using defult,so the totalCount button not show in the html.
     *if you set it as string,eg: $limitPageLable => '每页x条'.note that the 'x' will be replaced by $pageCount.so the 'x' must be seted.
     */
    public $limitPageLable = true;

    /**
     * @var array.options about the limitPageLable(input)
     * limitPageLableOptions => [
     *        'class' =>
     *        'data-limit-num' =>[]
     *        'style' =>
     *    ]
     *
     */
    public $limitPageLableOptions = [];

    /**
     * @var boole|string. weather show totalCount
     *You can do not set the property by using defult,so the totalCount button not show in the html.
     *if you set it as string,eg: $totalCountLable => '共x条'.note that the 'x' will be replaced by $pageCount.so the 'x' must be seted.
     */
    public $totalCountLable = false;

    /**
     * @var boole|string. weather show totalPage
     *You can do not set the property by using defult,so the totalPage button not show in the html.
     *if you set it as string,eg: totalPageLable => '共x页'.note that the 'x' will be replaced by $pageCount.so the 'x' must be seted.
     */
    public $totalPageLable = false;

    /**
     * @var boole if is seted true,the goPageLabel can be show in the html.
     *
     */
    public $goPageLabel = false;

    /**
     * @var array.options about the goPageLabel(input)
     *goPageLabelOptions => [
     *        'class' =>
     *        'data-num' =>
     *        'style' =>
     *    ]
     *
     */
    public $goPageLabelOptions = [];

    /**
     * @var boole | string. weather show in go page button
     *
     */
    public $goButtonLable = false;

    /**
     * @var array.options about the goButton
     *
     */
    public $goButtonLableOptions = [];

    /**
     * @var boole | string. weather show in form active 属性
     *
     */
    public $goFormActive = false;
    /**
     * @var array.options about the goButton
     *
     */
    public $goFormOptions = [];

    /**
     *
     **/
    public function init()
    {
        parent::init();

    }

    public function run()
    {
        if ($this->registerLinkTags) {
            $this->registerLinkTags();
        }
        echo $this->renderPageButtons();
    }

    protected function renderPageButtons()
    {

        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }

        $buttons = [];

        $currentPage = $this->pagination->getPage();
        // first page
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        if ($firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }

        // internal pages
        list($beginPage, $endPage) = $this->getPageRange();
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $buttons[] = $this->renderPageButton($i + 1, $i, null, false, $i == $currentPage);
        }

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // last page
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;
        if ($lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // total page
        if ($this->totalPageLable) {
            $buttons[] = Html::tag('li', Html::a(str_replace('x', $pageCount, $this->totalPageLable), 'javascript:return false;', []), []);
        }

        // total count
        $totalCount = $this->pagination->totalCount;
        if ($this->totalCountLable) {
            $buttons[] = Html::tag('li', Html::a(str_replace('x', $totalCount, $this->totalCountLable), 'javascript:return false;', []), []);
        }

        //2018-11-30 lizhanghu 修改Html::dropDownList样式form-control | style
        //limit page
        if ($this->limitPageLable) {

            $select = Html::dropDownList('limit', $this->pagination->pageSize, [10 => 10, 20 => 20, 30 => 30, 40 => 40, 50 => 50], ['class' => 'form-control', 'style' => 'height:31px;width:80px;display:inline-block;margin:0 3px 0 3px']);

            $buttons[] = Html::tag('li', $select);
        }

        //gopage
        if ($this->goPageLabel) {
            $input = Html::input('number', $this->pagination->pageParam, $currentPage + 1, array_merge([
                'min' => 1,
                'max' => $pageCount,
                'style' => 'height:31px;width:80px;display:inline-block;margin:0 3px 0 3px',
                'class' => 'form-control',
            ], $this->goPageLabelOptions));

            $buttons[] = Html::tag('li', $input, []);
        }

        // gobuttonlink
        if ($this->goPageLabel) {
            $gobuttonlink = Html::button($this->goButtonLable ? $this->goButtonLable : '跳转', array_merge([
                'style' => 'height:31px;display:inline-block;',
                'class' => 'btn btn-primary go-page'
            ], $this->goButtonLableOptions));
            $buttons[] = Html::tag('li', $gobuttonlink);
        }
        $ul = Html::tag('ul', implode("\n", $buttons), $this->options);
        $scriptStr = "<script>$(document).on('click','.go-page',function(){var pageNumber = $('input[name=page]').val();var pageLimit = $('select[name=limit]').val();var formAction=$('form[name=goPage]').attr('action');var url=formAction+'&page='+pageNumber+'&limit='+pageLimit;window.location.href = url;});</script>";

        return Html::tag('form', $ul, array_merge([
            'method' => 'get',
            'name' => 'goPage',
            'action' => $this->goFormActive ? preg_replace('/(page=[\d]*.?)|(limit=[\d]*.?)/i', '', $this->goFormActive) : ''
        ], $this->goFormOptions)) . $scriptStr;
    }
}
