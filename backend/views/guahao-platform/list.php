<?php

use common\components\GoPager;
use common\libs\CommonFunc;
use common\models\GuahaoPlatformModel;
use common\models\GuahaoPlatformListModel;
use yii\helpers\Html;

//新分页
use yii\helpers\Url;
use yii\helpers\ArrayHelper;


$request = \Yii::$app->request;
$this->title = '第三方来源列表';
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
            <label class="layui-form-label" style="width:60px;">状态</label>
            <div class="layui-input-block" style="width:100px;margin-left:60px;">
                <?php $tp_platform_list = array_merge(['' => '全部'], GuahaoPlatformListModel::$status_list);?>
                <?php echo Html::dropDownList('status', $request->get('status') ?? '', $tp_platform_list, array('id' => 'tp_platform_list', "class" => "form-control input-sm")); ?>

            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:140px;">平台名称</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <input type="text" name="platform_name"  placeholder="请输入平台名称" autocomplete="off" class="layui-input" value="<?php echo $request->get('platform_name', ''); ?>">

            </div>
        </div>
        <br/>
        <div class="layui-form-item layui-inline">
            <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="formDemoPane">搜索</button>
            <button type="reset" id="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
            <button class="layui-btn layui-btn-sm" style="padding: 0px;"><a style="color:white;width: 100px;display: block" href="<?php echo Url::to(['guahao-platform/save'])?>" >新增第三方来源</a></button>

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
            <th>平台名称</th>
            <th>平台类型Type</th>
            <th>平台类型</th>
            <th>平台SDK名称</th>
            <th>拉取排班脚本维度</th>
            <th>号源类型</th>
            <th>状态</th>
            <th>开放时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($dataProvider)): ?>
            <?php foreach ($dataProvider as $value): ?>
                <tr>
                    <td><?php echo $value['id']; ?></td>
                    <td><?php echo $value['platform_name']; ?></td>
                    <td><?php echo $value['tp_platform']; ?></td>
                    <td><?php echo $value['tp_type']; ?></td>
                    <td><?php echo $value['sdk']; ?></td>
                    <td><?php echo $value['get_paiban_type']; ?></td>
                    <td><?php echo $value['schedule_type_title']; ?></td>
                    <td><?php echo $value['status_title']; ?></td>
                    <td><?php echo $value['open_time']; ?></td>
                    <td>
                        <a class="layui-btn layui-btn-xs layui-btn-normal" href="<?php echo Url::to(['guahao-platform/save','id'=>$value['id']])?>" title='修改'>修改</a>
                        <?php if ($value['status'] == 1) { ?>
                            <a href="javascript:void(0);"
                               class="layui-btn layui-btn-xs layui-btn-danger update-status"
                               data_id="<?php echo $value['id']; ?>"
                               data_title="禁用"
                               data_status="0">禁用</a>
                        <?php } else { ?>
                            <a href="javascript:void(0);"
                               class="layui-btn layui-btn-xs layui-btn update-status"
                               data_title="开启"
                               data_id="<?php echo $value['id']; ?>"
                               data_status="1">开启</a>
                        <?php } ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="14" style="text-align: center">
                    <div class="empty">为搜索到任何数据</div>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="page" style="text-align: center;">
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
</div>
<?php
$platformUpdateStatusUrl = Url::to(['guahao-platform/update-status']);

?>
<script type="text/javascript">
    $(".update-status").click(function (e) {
        var _this = $(this);
        var data_id = $(this).attr('data_id');
        var data_title = $(this).attr('data_title');
        var data_status = $(this).attr('data_status');
        var $openStatusUrl = "<?=$platformUpdateStatusUrl;?>";

        layer.open({
           type: 1,
           title: "确认框",
           area: ['300px', '220px'],
           content: "确定要"+data_title+"吗？",
           btn: ['确定', '取消'],
           yes: function (index, layero) {
               $.post($openStatusUrl, {'id': data_id,'status':data_status,"_csrf-backend":$('#_csrf-backend').val()}, function (res) {
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
    });
    //重置表单选择
    $("#reset").click(function () {
        var fid = getValue().coo_id;
        var page = getValue().page;
        var str = '?';
        if (fid) {
            if (page) {
                str += 'id=' + fid + '&';
            } else {
                str += 'id=' + fid;
            }
        }
        if (page) {
            str += 'page=' + page
        }

        window.location.href = window.location.href.split('?')[0] + str;
    });

    //获取URL指定参数
    function getValue(url) {
        //首先获取地址
        var url = url || window.location.href;
        //获取传值
        var arr = url.split("?");
        //判断是否有传值
        if (arr.length == 1) {
            return null;
        }
        //获取get传值的个数
        var value_arr = arr[1].split("&");
        //循环生成返回的对象
        var obj = {};
        for (var i = 0; i < value_arr.length; i++) {
            var key_val = value_arr[i].split("=");
            obj[key_val[0]] = key_val[1];
        }
        return obj;
    }
</script>






