<?php
use yii\helpers\Html; //新分页
use common\models\minying\MinHospitalModel;
use common\components\GoPager;//新分页
use yii\helpers\Url;

$request     = \Yii::$app->request;
$this->title = '科室权重配置列表';
?>
<style>
    .layui-table tbody tr:hover{background: none;}
    .layui-form-label {width:100px;font-size:14px;}
    .layui-input-block {margin-left:160px;}
</style>

<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="layui-row">
    <form class="layui-form" action="">
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">科室名称：</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:200px;">
                <input type="text" name="department_name"  placeholder="请输入科室名称" autocomplete="off" class="layui-input" value="<?php echo Html::encode($request->get('department_name', '')); ?>">
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <button class="layui-btn layui-btn-sm"  lay-filter="formDemoPane" style="margin-left: 18px">搜索</button>
            <button type="reset" id="reset9" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
<!--            <button type="button" id="add-department" class="layui-btn layui-btn-sm">添加科室</button>-->
            <button type="button" class="layui-btn layui-btn-sm" data-toggle="modal" data-target="#modal-default">添加科室</button>
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
                <th >权重</th>
                <th >操作</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($dataProvider)): ?>
            <?php foreach ($dataProvider as $value): ?>
                <tr>
                    <td><?php echo $value['id']; ?></td>
                    <td><?php echo $value['second_department_name']; ?></td>
                    <td><?php echo $value['weight']; ?></td>
                    <td>
                        <a href="javascript:void(0);" class="layui-btn layui-btn-xs status_close"  data-id="<?=$value['id'];?>" data-status="0">移除</a>
                    </td>
                </tr>
            <?php endforeach;?>
        <?php else: ?>
            <tr><td colspan="14" style="text-align: center"><div class="empty">为搜索到任何数据</div></td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>
<div class="modal fade" id="modal-default" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">科室权重配置添加</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="modal-form">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">一级科室</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="first_department_id" name="first_department_id" >
                                    <option value="">请选择一级科室</option>
                                    <?php if(!empty($fkeshi_list)): ?>
                                        <?php foreach($fkeshi_list as $value):?>
                                            <option value="<?=$value['id']?>"><?=$value['name']?></option>
                                        <?php endforeach;?>
                                    <?php endif;?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-2 control-label">二级科室</label>

                            <div class="col-sm-10">
                                <select class="form-control" id="second_department_id">
                                    <option value="">请选择二级科室</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="weight" class="col-sm-2 control-label">权重</label>
                            <div class="col-sm-10">
                                <input class="form-control" onkeyup="value=value.replace(/^(0+)|[^\d]+/g,'')" maxlength="4" name="weight" type="text"  id="weight" placeholder="请输入权重值" autocomplete="off" class="layui-input" value="">
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">取消</button>
                <button type="button" style="margin-right: 8px;" id="add_department" class="btn btn-primary">添加</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div id="page" style="text-align: center;">
    <?= GoPager::widget([
        'pagination' => $pages,
        'goFormActive' => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id . '/doc-list'], $requestParams, ['1' => 1])),
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

<?php
$doAuthUrl = Url::to(['department-weight-config/change-status']);
$addDepartment = Url::to(['department-weight-config/add-department']);
?>
<script>
    $("#first_department_id").change(function (){
        var fkeshi_id = $('#first_department_id').val();
        var keshiUrl = "/keshi/miao-second-department-list";
        $.get(keshiUrl, {'fkeshi_id':fkeshi_id}, function (res){
            if(res.status == 1){
                var html = '<option value="">请选择二级科室</option>';
                $.each(res.data, function (i, v){
                    html += '<option value="'+v.id+'">'+v.name+'</option>';
                });
                $("#second_department_id").html(html);
                //重新渲染select
                form.render('select');
            }else{
                layer.msg('获取科室信息失败，请稍后重试！', {icon: 2});
            }
        });
    });

    $("#add_department").click(function (){
        var addDepartment = "<?=$addDepartment?>";
        var weight = $('#weight').val();
        var fkid = $('#first_department_id').val();
        var skid = $('#second_department_id').val();
        if (!weight || !fkid || !skid){
            layer.msg('一级科室、二级科室、权重不能为空！', {icon: 2});
            return false;
        }
        $.post(addDepartment, {'weight':weight,'fkid':fkid,'skid':skid,"_csrf-backend":$('#_csrf-backend').val()}, function (data){
            if(data.status == 1){
                layer.msg(data.msg, {icon: 1});
                setTimeout(function (){
                    window.location.reload();
                }, 2000);
            }else{
                layer.msg(data.msg, {icon: 2});
                return false;
            }
        });
    });

    $('.status_close').click(function(e){
        var _this = $(this);
        var id = _this.attr('data-id');
        layer.confirm('确定移除么<br/>', function(index){
            var sauthUrl = "<?=$doAuthUrl;?>";
            $.post(sauthUrl, {'id':id,"_csrf-backend":$('#_csrf-backend').val()}, function (res){
                if(res.status == 1){
                    layer.msg('操作成功！', {icon: 1});
                    setTimeout(function () {
                        window.location.href = window.location.href;
                    }, 3000);
                }else{
                    console.log(res);
                    layer.msg(res.msg, {icon: 2});
                    setTimeout(function () {
                        window.location.href = window.location.href;
                    }, 3000);
                }
            });
            layer.close(index);
        });
    });
</script>
