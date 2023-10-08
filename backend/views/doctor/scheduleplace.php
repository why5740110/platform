<?php

use yii\helpers\Url;
use yii\helpers\Html;
use common\libs\HashUrl;
use yii\helpers\ArrayHelper;
use common\libs\CommonFunc;
use common\models\GuahaoScheduleplaceRelation;
use common\components\GoPager;

$request = \Yii::$app->request;
$this->title = '医生管理/出诊机构';
?>

<style>
    .layui-table tbody tr:hover {
        background: none;
    }

    .layui-form-label {
        width: 100px;
        font-size: 14px;
    }

    .layui-input-block {
        margin-left: 160px;
    }

    .layui-textarea {
        min-height: 60px;
    }

    .layui-layer-shade {
        display: none;
    }

    .check_faild_reason_css {
        overflow: hidden;
        word-wrap: break-word;
    }
</style>



<div class="layui-layer-shade" id="layui-layer-shade2" times="2"
     style="z-index: 19891015; background-color: rgb(0, 0, 0); opacity: 0.3;"></div>

<div class="layui-row">
    <form class="layui-form" action="">

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">对接平台</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <?php $tp_platform_list = array_merge(['0'=>'全部'],CommonFunc::getTpPlatformNameList(1));?>
                <?php echo Html::dropDownList('tp_platform',$request->get('tp_platform') ?? '',$tp_platform_list,array('id'=>'tp_platform_list',"class"=>"form-control input-sm"));?>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">医生ID</label>
            <div class="layui-input-block" style="width:200px;margin-left:110px;">
                <input type="text" name="doctor_id" <?php if ($request->get('doctor_id')) {
                    echo 'value="' . $request->get('doctor_id') . '"';
                } ?> placeholder="请输入医生ID" autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">审核状态</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <?php $status = [''=>'全部']+GuahaoScheduleplaceRelation::$status;?>
                <?php echo Html::dropDownList('status',$request->get('status') ?? '',$status,array('id'=>'tp_status',"class"=>"form-control input-sm"));?>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">医生姓名</label>
            <div class="layui-input-block" style="width:200px;margin-left:110px;">
                <input type="text" name="realname" <?php if ($request->get('realname')) {
                    echo 'value="' . $request->get('realname') . '"';
                } ?> placeholder="请输入医生姓名" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">医院名称</label>
            <div class="layui-input-block" style="width:200px;margin-left:110px;">
                <input type="text" name="scheduleplace_name" <?php if ($request->get('scheduleplace_name')) {
                    echo 'value="' . $request->get('scheduleplace_name') . '"';
                } ?> placeholder="请输入执业地点" autocomplete="off" class="layui-input">
            </div>
        </div>
        <br/>
        <div class="layui-form-item layui-inline">
            <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="formDemoPane">搜索</button>
            <button class="layui-btn layui-btn-sm layui-btn-primary" type="reset"  lay-submit="" lay-filter="formDemoPane">重置</button>
            <!-- <button class="layui-btn layui-btn-sm layui-btn layui-btn-normal" style="padding: 0px;"><a style="color:white;width: 70px;display: block" href="<?php echo Url::to(['doctor/add-scheduleplace'])?>" >新增</a></button> -->
        </div>
    </form>

</div>

<hr>
<p style="color: red;text-align:left;word-break:break-all;white-space:pre-wrap;">审核状态只针对王氏加号有效，其他平台无意义</p>
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
            <th>ID</th>
            <th>医院线医生ID</th>
            <th>对接平台</th>
            <th>医生姓名</th>
            <th>第三方医生ID</th>
            <th>科室（出诊机构）</th>
            <th>医院名称（出诊机构）</th>
            <th>对应医院线医院ID</th>
            <th>对应医院线医院名称</th>
            <th>操作人</th>
            <th>申请日期</th>
            <th>审核状态</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($dataProvider)): ?>
            <?php foreach ($dataProvider as $value): ?>
                <tr>
                    <td><?php echo $value['id']; ?></td>
                    <td>
                        <?php if($value['primary_id'] == 0):?>
                        <a style="color:blue;" target="_blank" href=" <?= \Yii::$app->params['domains']['mobile'].'hospital/doctor_'. HashUrl::getIdEncode(ArrayHelper::getValue($value,'doctor_id')).'.html'; ?> ">
                        <?php echo $value['doctor_id']; ?>
                        </a>
                        <?php else:?>
                            <?php echo $value['doctor_id']; ?>
                        <?php endif;?>
                    </td>
                    <td><?php $tp_platform_list = CommonFunc::getTpPlatformNameList(1); echo $tp_platform_list[$value['tp_platform']] ?? '';?></td>
                    <td><?php echo $value['realname']; ?></td>
                    <td><?php echo $value['tp_doctor_id']; ?></td>
                    <td><?php if (!empty($value['hospital_department_id'])): ?><?=$value['frist_department_name'].'-'.$value['second_department_name'] ?? '';?> <?php endif; ?></td> 
                    <td><?php echo $value['scheduleplace_name']; ?></td>
                    <td><?=$value['hospital_id'] ? : '';?></td>
                    <td><?php echo $value['hospital_name']; ?></td>
                    <td><?php echo $value['admin_name']; ?></td>
                    <td><?=date('Y-m-d H:i:s',$value['create_time']);?></td>
                    <td>
                        <?php if ($value['tp_platform'] == 6):?>
                        <?php echo GuahaoScheduleplaceRelation::$status[$value['status']] ?? ''; ?>
                        <?php else:?>
                            
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($value['status'] == 1 && $value['hospital_department_id'] == 0 && $value['tp_platform'] == 6): ?>
                            <a href="javascript:void(0);" title='编辑'>
                            <button data-id="<?php echo $value['id']; ?>"
                                    type="button" class="edit_scheduleplace layui-btn layui-btn-xs layui-btn-normal">编辑
                            </button>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="14" style="text-align: center">
                    <div class="empty">未搜索到任何数据</div>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="page" style="text-align: center;">
    <?= GoPager::widget([
        'pagination' => $pages,
        'goFormActive' => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id . '/doc-scheduleplace'], $requestParams, ['1' => 1])),
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
$showScheduleplaceUrl = Url::to(['doctor/doc-scheduleplace-relation']);
$editScheduleplaceUrl = Url::to(['doctor/edit-scheduleplace']);
$upScheduleplaceUrl = Url::to(['doctor/up-scheduleplace']);
?>
<script type="text/javascript">
$(".show_scheduleplace").click(function (e){
    var id = $(this).attr('data-id');
    $('#layui-layer-shade2').show();
    var showScheduleplaceUrl = "<?=$showScheduleplaceUrl;?>";
    layer.open({
        shade: false,
        type:2,
        title:'执业地点',
        area:['500px', '300px'],
        // area:['50%', '50%'],
        content: showScheduleplaceUrl+"?scheduleplace_id="+id,
        btn: ['确定'],
        yes: function(index, layero){
            layer.close(index);
            $('#layui-layer-shade2').hide();
        },
        end:function(){
            $('#layui-layer-shade2').hide();
        }
    });
});

$(".edit_scheduleplace").click(function (e){
    var id = $(this).attr('data-id');
    $('#layui-layer-shade2').show();
    var editScheduleplaceUrl = "<?=$editScheduleplaceUrl;?>";
    var upScheduleplaceUrl = "<?=$upScheduleplaceUrl;?>";
    var lock = false;
    if (lock) {
        return false;
    }
    layer.open({
        shade: false,
        type:2,
        title:'编辑出诊机构科室',
        area:['50%', '50%'],
        content: editScheduleplaceUrl+"?id="+id,
        btn: ['确定', '取消'],
        yes: function(index, layero){
            //防止重复提交
            var search_fkid = $(layer.getChildFrame(".search_fkid option:selected", index)).val(); // 文本值
            var search_skid = $(layer.getChildFrame(".search_skid option:selected", index)).val(); // 文本值
            var relation_id = $(layer.getChildFrame("#relation_id", index)).val(); // 文本值

            if (!search_fkid || !search_skid) {
                layer.msg('科室不能为空！', {icon: 2});
                return false;
            }

            $.ajax({
                url: upScheduleplaceUrl,
                data: {
                    'id': relation_id,
                    'hospital_department_id': search_skid,
                },
                timeout: 5000,
                type: 'POST',
                dataType: "json",
                async: true,
                beforeSend: function() { // 禁用按钮防止重复提交\
                    if (lock) {
                        return false;
                    }
                    // lock = true;
                    loading = showLoad();
                },
                complete: function() {
                    layer.close(loading);
                },
                success: function (res) {
                    if (res.status == 1) {
                        layer.msg(res.msg, {icon: 1});
                        setTimeout(function (){
                            window.location.href = window.location.href;
                        }, 1000);
                    } else {
                        layer.msg(res.msg, {icon: 2});
                        setTimeout(function (){
                            window.location.href = window.location.href;
                        }, 2000);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    layer.msg('获取失败，请刷新重试', {icon: 2});
                }
            });
        },
        btn2: function (index, layero) {
            layer.close(index);
            $('#layui-layer-shade2').hide();
        },
        //关闭窗口时回调
        end: function () {
            //解除提交锁定
            lock = false;
            $('#layui-layer-shade2').hide();
        }
    });
});

</script>
  



