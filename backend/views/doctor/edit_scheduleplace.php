<?php

use common\libs\CommonFunc; //引入辅助表单类
use common\models\BaseDoctorHospitals; //引入数据小插件类
// use yii\timepicker\TimePicker;
use yii\helpers\Url;
use yii\widgets\ActiveForm; //新分页
$this->title = '添加多点执业';
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
        .m_text_l{
            margin-left: 35px;text-align: left;
            font-weight: bold;
        }        
        .m_text_l span{
            width: 100%;display: block;
        }
        .layui-anim{
            -webkit-animation-duration: 0.3s;
            animation-duration: 0.5s;
            -webkit-animation-fill-mode: both;
            animation-fill-mode: both;
        }
    </style>
</head>
<body>
    <div class="layui-form">

            <div class="layui-form-item m_text_l">
                <span>医院线医生ID： <?=$doctor_id;?></span> 
                <span>医院线医生名称: <?=$realname;?></span> 
                <span>医院名称（出诊机构）: <?=$hospital_name;?></span> 
            </div>  

            <div class="layui-form-item m_text_l" style="color: red;">
                如果发现没有科室可以选择，请检查医院是否开通医院线展示或者是否有科室！
            </div>              

            <div class="layui-form-item layui-inline">
                <label class="layui-form-label" style="width:87px;">出诊科室:</label>
                <div class="layui-input-block layui-inline" style="margin-left:0px;width:120px;">
                    <select name="frist_department_id" lay-filter="search_fkid"  class="search_fkid">
                         <option value="">请选择一级科室</option>
                        <?php if(!empty($fkeshi_list)): ?>
                            <?php foreach($fkeshi_list as $value):?>
                                <option value="<?=$value['frist_department_id']?>"><?=$value['frist_department_name']?></option>
                            <?php endforeach;?>
                        <?php endif;?>
                    </select>
                </div>
                <div class="layui-input-block layui-inline" style="margin-left:10px;width:120px;">
                    <select name="second_department_id" lay-filter="search_skid"  class="search_skid">
                        <option value="">二级科室</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="id" value="<?=$id;?>" id="relation_id">

    </div>
</body>
</html>
<script type="text/javascript">

layui.use('form', function(){
    form = layui.form;
});


layui.use('form', function(){
    form = layui.form;
    form.on('select(search_fkid)', function (data){
        var fkeshi_id = data.value;
        if(fkeshi_id == ''){
            $(".search_skid").html('<option value="">请选择二级科室</option>');
            form.render('select');
            return false;
        }
        var keshiUrl = "/keshi/ajax-skeshi";
        var hosid = "<?=$hospital_id;?>";
        $.get(keshiUrl, {'pid':fkeshi_id,'hosid':hosid}, function (res){
            if(res.status == 1){
                var html = '<option value="">请选择二级科室</option>';
                $.each(res.data.second_arr, function (i, v){
                      html += '<option value="'+v.id+'">'+v.second_department_name+'</option>';
                });
                $(".search_skid").html(html);
                //重新渲染select
                form.render('select');
            }else{
                layer.msg('获取科室信息失败，请稍后重试！', {icon: 2});
            }
        });
    });
});

</script>