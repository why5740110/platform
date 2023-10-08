<?php
/**
 * @file add160.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/2/24
 */

use common\helpers\Url;

?>

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
        <label class="layui-form-label" style="width:87px;">160医院code:</label>
        <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
            <input type="text"  id="code" placeholder="请输入医院code" autocomplete="off" class="layui-input" value="">
        </div>
        <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
            <button type="button" id="add-hosp" class="layui-btn layui-btn-sm layui-btn-primary">查看</button>
        </div>
    </div>
</form>
<div class="layui-row">
    <div id="info">
        <table class="table table-hover layui-table" lay-even="true" style="overflow-x:scroll;margin-top: 5px;">
            <thead>
            <tr>
                <th style="vert-align: middle;text-align: center">医院名称</th>
                <th style="vert-align: middle;text-align: center">级别</th>
<!--                <th style="vert-align: middle;text-align: center">地区</th>-->
            </tr>
            </thead>
            <tbody align="center" valign="center" border="1">
            <tr>

                <td style="" id="hosnamee"></td>
                <td style="" id="levle"></td>
<!--                <td style="" id="province"></td>-->
                </td>
            </tr>
            </tbody>
        </table>
        <div id="msg" style="color:red"></div>
    </div>
</div>

<script type="text/javascript">

    $("#add-hosp").click(function (){
        var code = $("#code").val();
        var hosUrl = "<?=Url::to(['hospital-relation/add160','select'=>1])?>";
        $.get(hosUrl, {'code':code,"_csrf-backend":$('#_csrf-backend').val()}, function (data){
            if(data.code==1){
                $("#msg").html('');
                $("#hosnamee").html(data.data.hospital_name);
                $("#levle").html(data.data.tp_hospital_level);
                //$("#province").html(data.data.province);

            }else{
                $("#hosnamee").html('');
                $("#levle").html('');
                //$("#province").html('');
                $("#msg").html(data.msg);

            }
        })
    })


</script>

</body>
</html>
