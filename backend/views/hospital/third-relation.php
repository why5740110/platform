<?php

use yii\helpers\Html;    //引入辅助表单类
use backend\models\CollectUserModel;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;  //引入数据小插件类
use common\libs\HashUrl;
use common\libs\CommonFunc;
$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@bower/bootstrap/dist');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>编辑科室</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link href="/layui/css/layui.css" rel="stylesheet">
    <script type="text/javascript" src="/js/topcommon.js"></script>
    <script src="/js/jquery.js"></script>
    <script src="/layui/layui.js"></script>
    <link href="<?=$directoryAsset?>/css/bootstrap.css" rel="stylesheet">
    <link href="/assets/36fb2002/css/bootstrap.css" rel="stylesheet">
    <link href="/css/site.css" rel="stylesheet">
    <link href="/layui/css/layui.css" rel="stylesheet">
    <link href="/assets/e2b7e35/css/font-awesome.min.css" rel="stylesheet">
    <link href="/assets/9dc2f1b0/css/AdminLTE.min.css" rel="stylesheet">
    <link href="/assets/9dc2f1b0/css/skins/_all-skins.min.css" rel="stylesheet">
    <script src="/assets/406dcb49/jquery.js"></script>
    <script src="/assets/ef4fdd71/yii.js"></script>
    <script src="/layui/layui.js"></script>
    <script src="/js/jquery.js"></script>
    <script src="/js/jquery.form.js"></script>
    <script src="/js/common.js"></script>
    <script src="/wangEditor/wangEditor.min.js"></script>        
    <style>
        /*layui关闭选项样式修改*/
        .layui-table-tips-c:before {position: relative; right: 1px; top: -3px; }
    </style>

</head>
<body>

<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="layui-row">
    <div class="form-group--4">
    <?php $form = ActiveForm::begin(['action' => Url::to(['record/grab']), 'method' => 'post', 'options' => ['name' => 'form', 'id'=>'grab_form', 'class' => 'layui-form']]);?>
        <div class="layui-form-item">
          <label class="layui-form-label" style="width: 110px;">医院名称:</label>
          <div class="layui-input-block">
              <?=$hospital['name'] ?? '';?>
          </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">对应医院线一级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="miao_frist_department_id" lay-verify="required" lay-search="" lay-filter="miao_search_fkid"  class="miao_search_fkid">
                    <option value="">对应医院线一级科室</option>
                    <?php if(!empty($hospital_keshi)): ?>
                        <?php foreach($hospital_keshi as $value):?>
                            <option value="<?=$value['frist_department_id']?>"  ><?=$value['frist_department_name']?></option>
                        <?php endforeach;?>
                    <?php endif;?>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">对应医院线二级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="miao_second_department_id" lay-verify="required" lay-search="" lay-filter="miao_search_skid"  class="miao_search_skid">

                </select>
            </div>
        </div>

        <input name="frist_department_name" type="hidden" value="" id="fkeshi_name" />
        <input name="second_department_name" type="hidden" value="" id="skeshi_name" />


        <div class="layui-form-item" style="display: none;">
          <div class="layui-input-block">
            <input type="text" name="hospital_id"  value="<?=$hospital_id;?>" >
          </div>
        </div>

    <?php ActiveForm::end();?>
    </div>

</div>

<script type="text/javascript">
layui.use('form', function(){
    var form = layui.form;
    form.on('select(miao_search_fkid)', function (data){
        var fkeshi_id = data.value;
        if(fkeshi_id == ''){
            $(".miao_search_skid").html('<option value="">请选择二级科室</option>');
            form.render('select');
            return false;
        }
        var keshiUrl = "/keshi/second-department-list";
        $.get(keshiUrl, {'fkeshi_id':fkeshi_id,'hospital_id':<?=$hospital_id;?>,"_csrf-backend":$('#_csrf-backend').val()}, function (res){
            if(res.status == 1){
                var html = '<option value="">请选择二级科室</option>';
                $.each(res.data, function (i, v){
                    html += '<option value="'+v.id+'">'+v.second_department_name+'</option>';
                });
                $(".miao_search_skid").html(html);
                //重新渲染select
                form.render('select');
            }else{
                layer.msg('获取科室信息失败，请稍后重试！', {icon: 2});
            }
        });
    });
});



</script>
</body>
</html>