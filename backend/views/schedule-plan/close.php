<?php

use common\components\GoPager;
use yii\helpers\Html;

$request     = \Yii::$app->request;
$this->title = '停诊计划列表';
?>
<style>
    .layui-table tbody tr:hover{background: none;}
    .layui-form-label {width:130px;font-size:14px;}
    .layui-input-block {margin-left:160px;}
    .layui-textarea{min-height:60px;}
    .layui-layer-shade{display:none;}
    .check_faild_reason_css{
        overflow:hidden; word-wrap:break-word;
    }
    .hos-upload {
        padding: 4px 10px;
        height: 30px;
        line-height: 20px;
        position: relative;
        cursor: pointer;
        #color: #888;
        #background: #fafafa;
        #border: 1px solid #ddd;
        #border-radius: 4px;
        overflow: hidden;
        text-decoration: none;
        display: inline-block;
        *display: inline;
        *zoom: 1
    }
    .hos-upload  input {
        position: absolute;
        font-size: 100px;
        right: 0;
        top: 0;
        opacity: 0;
        filter: alpha(opacity=0);
        cursor: pointer
    }
</style>
<div class="layui-layer-shade" id="layui-layer-shade2" times="2" style="z-index: 19891015; background-color: rgb(0, 0, 0); opacity: 0.3;"></div>
<div class="layui-row">
    <form class="layui-form" action="">
        <!--<div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="min_department_id" lay-search="" lay-filter="search_fkid"  class="search_fkid">
                    <option value="">全部</option>
                    <?php /*if(!empty($department)): */?>
                        <?php /*foreach($department as $key => $value):*/?>
                            <option value="<?/*=$key*/?>" <?php /*if($key == ($requestParams['min_department_id']??0)){ echo 'selected="selected"'; }*/?>><?/*=$value*/?></option>
                        <?php /*endforeach;*/?>
                    <?php /*endif;*/?>
                </select>
            </div>
        </div>-->

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label">停诊对象名称：</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <input type="text" name="keyword"  placeholder="请输入停诊对象名称" autocomplete="off" class="layui-input" value="<?php echo Html::encode($request->get('keyword', '')); ?>">
            </div>
        </div>

        <br/>
        <div class="layui-form-item layui-inline">
            <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="formDemoPane">搜索</button>
        </div>
    </form>
</div>

<hr>

<div class="layui-row">
    <div class="layui-col-md6">
    </div>
    <div class="layui-col-md6 layui-col-md-offset6" style="text-align:right;">
        <p class="tr" style="font-size: 15px;margin-bottom: 10px;">共<?php echo $totalCount; ?>条</p>
    </div>
</div>

<div class="layui-form layui-border-box">

    <table id="main-table" class="table table-hover" lay-even="true" style="overflow-x:scroll;">
        <thead>
        <tr>
            <th >序号</th>
            <th >停诊对象</th>
            <th >科室</th>
            <th >医院</th>
            <th >停诊范围</th>
            <th >停诊类型</th>
            <th >停诊时间</th>
            <th >备注</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($dataProvider)): ?>
            <?php foreach ($dataProvider as $value): ?>
                <tr>
                    <td ><?php echo $value['id']; ?></td>
                    <td ><?php echo Html::encode($value['object_name']); ?></td>
                    <td ><?php echo Html::encode($value['department']); ?></td>
                    <td ><?php echo Html::encode($value['min_hospital_name']); ?></td>
                    <td ><?php echo $value['stop_section_type_desc']; ?></td>
                    <td ><?php echo $value['section_cycle_type_desc']; ?></td>
                    <td ><?php echo $value['cycle_desc']; ?></td>
                    <td ><?php echo Html::encode($value['remark']); ?></td>
                </tr>
            <?php endforeach;?>
        <?php else: ?>
            <tr><td colspan="14" style="text-align: center"><div class="empty">为搜索到任何数据</div></td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>

<div id="page" style="text-align: center;">
    <?=GoPager::widget([
    'pagination'      => $pages,
    'goFormActive'    => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id . '/close-list'], $requestParams, ['1' => 1])),
    'firstPageLabel'  => '首页',
    'prevPageLabel'   => '《',
    'nextPageLabel'   => '》',
    'lastPageLabel'   => '尾页',
    'goPageLabel'     => true,
    'totalPageLable'  => '共x页',
    'totalCountLable' => '共x条',
    'goButtonLable'   => 'GO',
]);?>
</div>






