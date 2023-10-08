<?php

use common\components\GoPager;
use common\libs\CommonFunc;
use common\models\GuahaoCooListModel;
use common\models\GuahaoPlatformModel;
use yii\helpers\Html;

//新分页
use yii\helpers\Url;
use yii\helpers\ArrayHelper;


$request = \Yii::$app->request;
$this->title = '开放列表';
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

    .hos-upload input {
        position: absolute;
        font-size: 100px;
        right: 0;
        top: 0;
        opacity: 0;
        filter: alpha(opacity=0);
        cursor: pointer
    }
</style>
<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="layui-layer-shade" id="layui-layer-shade2" times="2"
     style="z-index: 19891015; background-color: rgb(0, 0, 0); opacity: 0.3;"></div>
<div class="layui-row">
    <form class="layui-form" action="">
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:140px;">对接王氏来源</label>
            <div class="layui-input-block layui-inline" id="select" style="margin-left:0px;width:100px;">
                <?php $tp_platform_list = GuahaoCooListModel::getCooPlatformList(); ?>
                <?php echo Html::dropDownList('coo_platform', $request->get('coo_platform') ?? '', $tp_platform_list, array('id' => 'tp_platform_list', "class" => "form-control input-sm")); ?>
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="formDemoPane">搜索</button>
        </div>
    </form>

</div>
<hr>

<div class="layui-row">
    <div class="layui-col-md6 layui-col-md-offset6" style="text-align:right;">
        <p class="tr" style="font-size: 15px;margin-bottom: 10px;">共<?php echo $totalCount; ?>条</p>
    </div>
</div>

<div class="layui-form layui-border-box">

    <table id="main-table" class="table table-hover" lay-even="true" style="overflow-x:scroll;">
        <thead>
        <tr>
            <th>ID</th>
            <th>王氏对接来源名称</th>
            <th>对接王氏来源名称</th>
            <th>状态</th>
            <th>备注</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($dataProvider)): ?>
            <?php foreach ($dataProvider as $value): ?>
                <tr>
                    <td><?php echo $value['id']; ?></td>
                    <td><?php $tp_platform_list = CommonFunc::getTpPlatformNameList(); echo $tp_platform_list[$value['tp_platform']] ?? '' ?></td>
                    <td><?php echo $value['coo_platform_name']; ?></td>
                    <td><?= GuahaoPlatformModel::$status_list[$value['status']] ?? ''; ?></td>
                    <td><?php echo $value['remarks']; ?></td>
                    <td>
                        <?php if ($value['status'] == 1) { ?>
                            <a href="javascript:void(0);" class="layui-btn layui-btn-xs layui-btn-danger coo-open-stop"
                               plat_form_id="<?php echo $value['tp_platform']; ?>"
                               t_id="<?php echo $value['guahao_platform_id']; ?>" dis_type="1">停止开放</a>
                        <?php } elseif ($value['status'] == 0) { ?>
                            <a href="javascript:void(0);" class="layui-btn layui-btn-xs coo-open-stop"
                               plat_form_id="<?php echo $value['tp_platform']; ?>"
                               t_id="<?php echo $value['guahao_platform_id']; ?>" dis_type="0">开放</a>
                        <?php } elseif ($value['status'] == 2) { ?>
                            <a href="javascript:void(0);" class="layui-btn layui-btn-xs layui-btn-warm coo-open-stop"
                               plat_form_id="<?php echo $value['tp_platform']; ?>"
                               t_id="<?php echo $value['guahao_platform_id']; ?>" dis_type="0">再次开放</a>
                        <?php } ?>

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
        'goFormActive' => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id . '/open-list'], $requestParams, ['1' => 1])),
        'firstPageLabel' => '首页',
        'prevPageLabel' => '《',
        'nextPageLabel' => '》',
        'lastPageLabel' => '尾页',
        'goPageLabel' => true,
        'totalPageLable' => '共x页',
        'totalCountLable' => '共x条',
        'goButtonLable' => 'GO',
    ]); ?>
</div>
<?php
$openStatusUrl = Url::to(['open-management/update-status']);
?>
<script type="text/javascript">
    $(".coo-open-stop").click(function (e) {
        var coo_id = $("#select option:selected").val();
        var coo_name = $("#select option:selected").text();
        $(this).attr('disabled', 'disabled');
        var _this = $(this);
        var t_id = $(this).attr('t_id');
        var plat_form_id = $(this).attr('plat_form_id');
        var dis_type = $(this).attr('dis_type');
        var confi_content = '确定要';
        if (dis_type == 1) {
            confi_title = '停止开放给【'+coo_name+'】';
            confi_content = '<div class="layui-form-item layui-inline"><label class="layui-form-label">备注<span style="color:#ff0000">*</span></label><div class="layui-input-block" ><input type="text" name="open_remarks"  value="" id="open_remarks" placeholder="请输入备注信息" autocomplete="off" maxlength="50" class="layui-input"></div></div>';
        } else {
            confi_title = '开放给【'+ coo_name+'】';
            confi_content = '<div class="layui-form-item layui-inline"><h3 style="text-align: center">您确定开放吗</h3></div>';
        }
        var $openStatusUrl = "<?=$openStatusUrl;?>";


        // layer.confirm(confi_text+'<br/>', function(index){
        layer.open({
            type: 1,
            title: confi_title,
            area: ['500px', '320px'],
            content: confi_content,
            btn: ['确定', '取消'],
            yes: function (index, layero) {
                if (dis_type == 1) {
                    var remarks = $('#open_remarks').val().trim().replace('/\s/g','');
                    if (!remarks) {
                        return layer.msg('请填写备注信息', {icon: 2});
                    }
                    if (remarks.length > 50) {
                        return layer.msg('备注信息不能大于50个字', {icon: 2});
                    }
                } else {
                    var remarks = '';
                }
                $.post($openStatusUrl, {'t_id': t_id, 'dis_type': dis_type, 'remarks': remarks,'plat_form_id':plat_form_id,'coo_id':coo_id,"_csrf-backend":$('#_csrf-backend').val()}, function (res) {
                    if (res.status == 1) {
                        layer.msg('操作成功！', {icon: 1});
                        setTimeout(function () {
                            window.location.href = window.location.href;
                        }, 1000);
                    } else {
                        layer.msg(res.msg, {icon: 2});
                    }
                });

            },
            btn2: function (index, layero) {
                _this.removeAttr('disabled');
                layer.close(index);
            },
            end: function () {
                _this.removeAttr('disabled');
                $('#layui-layer-shade2').hide();
            }
        });
        // });
    });

</script>






