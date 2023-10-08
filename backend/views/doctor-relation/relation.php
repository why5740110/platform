<?php
use yii\helpers\Url;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
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
        .red{
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="layui-row">
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label" style="width:auto">医院线平台：</label>
        <div class="layui-input-block" style="width:200px;">
            <input type="number" min="0" name="doctor_id" id="doctor_id" placeholder="请输入主医生ID"  class="layui-input" value="" />
            <input type="hidden" name="tp_doctor_id" id="tp_doctor_id"  value="<?php echo $tp_doctor_info['tp_doctor_id']; ?>"/>
            <button class="search layui-btn layui-btn-sm">查询</button>
        </div>
    </div>
    <div id="info_item">
        <table class="table table-hover layui-table" lay-even="true" style="overflow-x:scroll;margin-top: 5px;">
            <label class="layui-form-label" style="width:auto">第三方医生信息</label>
            <thead>
            <tr>
                <th style="vert-align: middle;text-align: center">第三方医生ID</th>
                <th style="vert-align: middle;text-align: center">医生姓名</th>
                <th style="vert-align: middle;text-align: center">医院</th>
                <th style="vert-align: middle;text-align: center">科室</th>
            </tr>
            </thead>
            <tbody align="center" valign="center" border="1">
                <tr>
                    <td style=""><?=$tp_doctor_info['tp_doctor_id'];?></td>
                    <td style="" ><?=$tp_doctor_info['realname'];?></td>
                    <td style="" ><?=$tp_doctor_info['hospital_name'];?></td>
                    <td style=""><?=$tp_doctor_info['frist_department_name'].'-'.$tp_doctor_info['second_department_name'];?></td>
                </tr>
            </tbody>
        </table>
        <div id="msg" class="red" style="color:red"></div>
    </div>
    <div id="info">
        <label class="layui-form-label" style="width:auto">医院线医生信息</label>
        <table class="table table-hover layui-table" lay-even="true" style="overflow-x:scroll;margin-top: 5px;">
            <thead>
            <tr>
                <th style="vert-align: middle;text-align: center">医院医生ID</th>
                <th style="vert-align: middle;text-align: center">医生姓名</th>
                <th style="vert-align: middle;text-align: center">医院</th>
                <th style="vert-align: middle;text-align: center">科室</th>
            </tr>
            </thead>
            <tbody align="center" valign="center" border="1">
                <tr>
                    <td style="" id="doctorid"></td>
                    <td style="" id="realname"></td>
                    <td style="" id="hospital"></td>
                    <td style="" id="keshi"></td>
                </tr>
            </tbody>
        </table>
        <div id="msg" class="red"></div>
    </div>

    <div class="red">对应医院信息</div>

    <div id="hospital_item" class="layui-form">
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:87px;">医院</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <input type="text"  id="hosname" placeholder="请输入医院名称" autocomplete="off" class="layui-input new_doctor_hospital" value="">
                <select name="hospital_id" lay-verify="required" lay-search lay-filter="hos" class="hos" id="hos">
                    <option value=""></option>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:87px;">医生科室</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:120px;">
                <select name="frist_department_id" lay-verify="required"  lay-filter="search_fkid"  class="search_fkid">
                    <option value="">一级科室</option>
                </select>
            </div>
            <div class="layui-input-block layui-inline" style="margin-left:10px;width:120px;">
                <select name="second_department_id" lay-verify="required"  lay-filter="search_skid"  class="search_skid">
                    <option value="">二级科室</option>
                </select>
            </div>
        </div>
    </div>
</div>
</body>
</html>


<?php 
//获取科室信息
$hosUrl = Url::to(['keshi/ajax-hos']);
$keshiUrl = Url::to(['keshi/ajax-keshi']);
$skeshiUrl = Url::to(['keshi/ajax-skeshi']);

?>
<script>

    layui.use(['laydate', 'form', 'table'], function(){
        var form = layui.form;
        $(".search").on("click",function(e){
            //var doctor_id = e.delegateTarget.value;
            var doctor_id = $('#doctor_id').val();
            if(doctor_id == ''||isNaN(doctor_id)){
                return false;
            }
            var docUrl = "<?php echo Url::to(['doctor-relation/ajax-get-info']);?>";
            $.get(docUrl, {'doctor_id':doctor_id,'is_primary':1,"_csrf-backend":$('#_csrf-backend').val()}, function (data){
                if(data.status == 1){
                    $('#info').show();
                    $('#doctorid').html(doctor_id);
                    if (data.data.realname != "<?php echo $tp_doctor_info['realname'];?>") {
                        $('#realname').addClass('red');
                    }
                    $('#realname').html(data.data.realname);
                    if (data.data.hospital != "<?php echo $tp_doctor_info['hospital_name'];?>") {
                        $('#hospital').addClass('red');
                    }
                    $('#hospital').html(data.data.hospital);
                    if (data.data.fkname+'-'+data.data.skname != "<?php echo $tp_doctor_info['frist_department_name'].'-'.$tp_doctor_info['second_department_name'];?>") {
                        $('#keshi').addClass('red');
                    }
                    $('#keshi').html(data.data.fkname+'-'+data.data.skname);
                    if((data.data.realname != "<?php echo $tp_doctor_info['realname'];?>") || (data.data.hospital != "<?php echo $tp_doctor_info['hospital_name'];?>")){
                        $('#msg').html('关联的医生名称或者医院不一致,点击关联按钮会强制关联！！！');
                    }
                }else{
                    $('#info').hide();
                    layer.msg('没有医生信息,请确认是否为主医生！', {icon: 2});
                }
            });
        });

        $(".new_doctor_hospital").on("input", function(e) {
            var name = e.delegateTarget.value;
            if (name == '') {
                return false;
            }
            var hosUrl = "<?=$hosUrl;?>";
            $.get(hosUrl, {
                'name': name,"_csrf-backend":$('#_csrf-backend').val()
            }, function(data) {
                if (data.status == 1) {
                    var html = '<option value="">请选择医院</option>';
                    $.each(data.data, function(i, v) {
                        html += '<option value="' + v.id + '">' + v.name + '</option>';
                    });
                    $(".hos").html(html);
                    //重新渲染select
                    form.render('select');
                } else {
                    layer.msg('获取信息失败，请稍后重试！', {icon: 2,time: 2000});
                }
            });
        });
        //搜索框科室联动
        form.on('select(hos)', function(data) {
            var hosid = data.value;
            if (data.value == '') {
                return false;
            }
            var keshiUrl = "<?=$keshiUrl;?>";
            $.get(keshiUrl, {
                'hosid': hosid,"_csrf-backend":$('#_csrf-backend').val()
            }, function(data) {
                if (data.status == 1) {
                    var html = '<option value="">一级科室</option>';
                    $.each(data.data, function(i, v) {
                        html += '<option value="' + v.frist_department_id + '">' + v.frist_department_name + '</option>';
                    });
                    $(".search_fkid").html(html);
                    $(".search_skid").html('');
                    //重新渲染select
                    form.render('select');
                } else {
                    layer.msg('获取科室信息失败，请稍后重试！', {icon: 2,time: 2000});
                }
            });
        });
        //搜索框科室联动
        form.on('select(search_fkid)', function(data) {
            var pid = data.value;
            var hosid = $('#hos').val();
            if (data.value == '') {
                return false;
            }
            var skeshiUrl = "<?=$skeshiUrl;?>";
            $.get(skeshiUrl, {
                'pid': pid,
                'hosid': hosid,
                "_csrf-backend":$('#_csrf-backend').val()
            }, function(data) {
                if (data.status == 1) {
                    var html = '<option value="">二级科室</option>';
                    $.each(data.data.second_arr, function(i, v) {
                        html += '<option value="' + v.second_department_id + '">' + v.second_department_name + '</option>';
                    });
                    $(".search_skid").html(html);
                    //重新渲染select
                    form.render('select');
                } else {
                    layer.msg('获取二级科室信息失败，请稍后重试！', {icon: 2,time: 2000});
                }
            });
        });
    });
</script>

