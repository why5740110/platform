<?php
use yii\helpers\Url;
use yii\helpers\Html;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>关联医院</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link href="/layui/css/layui.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
    <script type="text/javascript" src="/js/topcommon.js"></script>
    <script src="/js/jquery.js"></script>
    <script src="/layui/layui.js"></script>
    <style>
        .layui-table[lay-size=sm] td, .layui-table[lay-size=sm] th {
            font-size: 12px;
            padding: 2px 10px;
        }
    </style>
</head>
<body>
<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<form class="layui-form" action="">
<div class="layui-form-item layui-inline">
    <label class="layui-form-label" style="width:87px;">医院线平台:</label>
    <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
        <input type="text"  id="hosname" placeholder="请输入医院名称" autocomplete="off" class="layui-input" value="<?php echo isset($hospital['name'])?Html::encode($hospital['name']):'';?>">
        <select name="hospital_id" lay-verify="required" lay-search lay-filter="hos" class="hos" id="hos">
            <option value="<?php echo $info['hospital_id']??'';?>" ><?php echo isset($hospital['name'])?Html::encode($hospital['name']):'';?></option>
        </select>
    </div>
</div>
</form>
<div class="layui-row">

   <!-- <div class="layui-form-item layui-inline">
        <label class="layui-form-label">医院线平台：</label>
        <div class="layui-input-block" style="width:200px;">
            <input type="number" min="0" placeholder="请输入医院id" name="hospital_id" id="hospital_id" />
            <button class="search layui-btn layui-btn-sm">查询</button>
        </div>
    </div>-->
    <div id="info">
        <table class="table table-hover layui-table" lay-even="true" style="overflow-x:scroll;margin-top: 5px;">
            <thead>
            <tr>
                <th style="vert-align: middle;text-align: center">医院ID</th>
                <th style="vert-align: middle;text-align: center">医院名称</th>
            </tr>
            </thead>
            <tbody align="center" valign="center" border="1">
            <tr>
                <td style="" id="hosid"></td>
                <td style="" id="hosnamee"></td>
                </td>
            </tr>
            </tbody>
        </table>
        <div id="msg" style="color:red"></div>
    </div>
</div>
</body>
</html>

<script>

    layui.use(['form'], function(){
        var form = layui.form;

        //监听所有的下拉选框
        form.on('select', function(data){
            var selecter = "option[value='" + data.value + "']";
            $(this).parents('.layui-form-select').siblings('select').find(selecter).attr("selected", "selected").siblings().removeAttr('selected');
        });

        $(".layui-input").on("input",function(e){
            var name = e.delegateTarget.value;

            if(name == ''){
                return false;
            }
            var hosUrl = "<?php echo Url::to(['doctor/ajax-hos']);?>";
            $.get(hosUrl, {'name':name,"_csrf-backend":$('#_csrf-backend').val()}, function (data){
                if(data.code == 200){
                    if(data.hos==null || data.hos.length==0){
                        $('#hosnamee').html('');
                        $('#hosid').html('');
                    }else{
                        var html = '<option value="">请选择医院</option>';
                        $.each(data.hos, function (i, v){
                            html += '<option value="'+v.id+'">'+v.name+'</option>';
                        });

                        $(".hos").html(html);
                        //重新渲染select
                        form.render('select');
                    }

                }else{
                    layer.msg('获取信息失败，请稍后重试！', {icon: 2});
                }
            });
        });
        form.on('select(hos)', function (data){
            var hospital_id = data.value;
            if(hospital_id == ''||isNaN(hospital_id)){
                return false;
            }
            var docUrl = "<?php echo Url::to(['hospital-relation/ajax-get-info']);?>";
            $.get(docUrl, {'hospital_id':hospital_id,"_csrf-backend":$('#_csrf-backend').val()}, function (hos){
                if(hos.status == 1){
                    $('#info').show();
                    $('#hosnamee').html(hos.data.name);
                    $('#hosid').html(hos.data.id);
                    if(hos.data.name != "<?php echo Html::encode($tp_hospital_info['hospital_name']);?>"){
                        $('#msg').html('关联的医院不一致,点击关联按钮会强制关联！！！');
                    }
                }else{
                    $('#info').hide();
                    layer.msg('没有信息', {icon: 2});
                }
            });
        });
    });
</script>

