<?php
use yii\helpers\Html; //新分页
use common\models\minying\MinHospitalModel;
use common\components\GoPager;//新分页
use yii\helpers\Url;

$request     = \Yii::$app->request;
$this->title = '科室审核列表';
$check_list = MinHospitalModel::$checklist;
?>
<style>
    .layui-table tbody tr:hover{background: none;}
    .layui-form-label {width:100px;font-size:14px;}
    .layui-input-block {margin-left:160px;}
</style>
<div class="layui-row">
    <form class="layui-form" action="">
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">更新时间：</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:200px;">
                <input type="text" name="create_time" <?php if($request->get('create_time')){ echo 'value="'.Html::encode($request->get('create_time')).'"'; }?> class="layui-input" id="create_time">
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">审核状态：</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <?php $tp_platform_list = array_merge(['0' => '全部'], $check_list);?>
                <?php echo Html::dropDownList('check_status', $request->get('check_status') ?? '', $tp_platform_list, array('id' => 'tp_platform_list', "class" => "form-control input-sm")); ?>
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;"></label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:200px;">
                <input type="text" name="department_name"  placeholder="请输入科室名称" autocomplete="off" class="layui-input" value="<?php echo Html::encode($request->get('department_name', '')); ?>">
            </div>
        </div>
        <br/>
        <div class="layui-form-item layui-inline">
            <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="formDemoPane" style="margin-left: 18px">搜索</button>
            <button type="reset" id="reset9" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
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
            <th >科室名称</th>
            <!--<th >王氏科室</th>-->
            <th >医院名称</th>
            <th >更新时间</th>
            <th >初审操作人</th>
            <th >初审时间</th>
            <th >二审操作人</th>
            <th >二审时间</th>
            <th >审核状态</th>
            <th >操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($dataProvider)): ?>
            <?php foreach ($dataProvider as $value): ?>
                <tr>
                    <td ><?php echo $value['id']; ?></td>
                    <td ><?php echo Html::encode($value['min_minying_fkname'].'>>'.$value['min_minying_skname']); ?></td>
                    <!--<td ><?php /*echo $value['miao_first_department_name'].'>>'.$value['miao_second_department_name']; */?></td>-->
                    <td ><?php echo Html::encode($value['min_hospital_name']); ?></td>
                    <td ><?php echo $value['create_time']; ?></td>
                    <td ><?php echo Html::encode($value['first_check_name']); ?></td>
                    <td ><?php echo $value['first_check_time']; ?></td>
                    <td ><?php echo Html::encode($value['second_check_name']); ?></td>
                    <td ><?php echo $value['second_check_time']; ?></td>
                    <td ><?php echo $check_list[$value['check_status']]; ?></td>
                    <td >
                        <a href="<?php echo Url::to(['keshi-minying-check/info','id'=>$value['id']])?>" title='详情'>
                            <button type="button" class="layui-btn layui-btn-xs layui-btn-normal">
                                详情
                            </button>
                        </a>
                    </td>
                </tr>
            <?php endforeach;?>
        <?php else: ?>
            <tr><td colspan="14" style="text-align: center"><div class="empty">为搜索到任何数据</div></td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>

<div id="page" style="text-align: center;">
    <?= GoPager::widget([
        'pagination' => $pages,
        'goFormActive' => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id . '/list'], $requestParams, ['1' => 1])),
        'firstPageLabel' => '首页',
        'prevPageLabel' => '《',
        'nextPageLabel' => '》',
        'lastPageLabel' => '尾页',
        'goPageLabel' => true,
        'totalPageLable' => '共x页',
        'totalCountLable' => '共x条',
        'goButtonLable' => 'GO'
    ]); ?>
</div>

<script>
    layui.use(['laydate', 'form', 'table'], function(){
        var laydate = layui.laydate;
        var form = layui.form;
        //开通时间
        laydate.render({
            elem: '#create_time', //指定元素
            range: true
        });

    });
</script>