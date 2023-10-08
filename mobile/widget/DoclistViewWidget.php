<?php
/**
 * @file DoclistViewWidget.php
 * @author lixiaolong
 * @version 1.0
 * @date 2021-01-04
 */


namespace mobile\widget;


use common\libs\CommonFunc;

class DoclistViewWidget extends \yii\base\Widget
{

    public $row;
    public $type=1;
    public $shence_type=1;

    public function run()
    {
        $html = CommonFunc::returnDoclHtml($this->row,$this->type,$this->shence_type);
        return $html;
    }
}