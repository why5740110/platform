<?php

use yii\helpers\Html;    //引入辅助表单类
use backend\models\CollectUserModel;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;  //引入数据小插件类
use common\libs\HashUrl;
use common\libs\CommonFunc;
$platform_list = CommonFunc::getTpPlatformNameList();
$this->title = '医生多点执业';
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
            <?php if(!empty($hospital['kind'] =='公立')): ?>
              <?php $link = \Yii::$app->params['domains']['pc'].'hospital/hospital_'.HashUrl::getIdEncode(ArrayHelper::getValue($hospital,'id')).'.html';?>
                <?php $html = '<a style="color:blue;" target="_blank" href="'.$link.'">
                    '.Html::encode($hospital['name']).'
                </a>';?>
                <?=$html?>
            <?php else:?> 
                <?=Html::encode($hospital['name']);?>
            <?php endif;?> 
          </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">一级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="frist_department_id" lay-verify="required" lay-search="" lay-filter="search_fkid"  class="search_fkid">

                    <option value="<?=Html::encode($relationInfo['frist_department_name'])?>"  selected="selected" ><?=Html::encode($relationInfo['frist_department_name'])?></option>

                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">二级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="second_department_id" lay-verify="required" lay-search="" lay-filter="search_skid"  class="search_skid">
                   <option value="<?=Html::encode($relationInfo['second_department_name'])?>"  selected="selected" ><?=Html::encode($relationInfo['second_department_name'])?></option>
                </select>
            </div>
        </div>

        <input name="frist_department_name" type="hidden" value="" id="fkeshi_name" />
        <input name="second_department_name" type="hidden" value="" id="skeshi_name" />

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">对应王氏一级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="miao_frist_department_id" lay-verify="required" lay-search="" lay-filter="miao_search_fkid"  class="miao_search_fkid">
                    <option value="">请选择王氏一级科室</option>
                    <?php if(!empty($miao_fkeshi_list)): ?>
                        <?php foreach($miao_fkeshi_list as $value):?>
                        <option value="<?=$value['id']?>" <?php if($relationInfo['miao_frist_department_id'] == $value['id']): ?>  selected="selected" <?php endif;?>  ><?=Html::encode($value['name'])?></option>
                        <?php endforeach;?>
                    <?php endif;?> 
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:110px;">对应王氏二级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="miao_second_department_id" lay-verify="required" lay-search="" lay-filter="miao_search_skid"  class="miao_search_skid">
                    <?php if(!empty($relationInfo['miao_second_department_id'])): ?>
                        <option value="<?=$relationInfo['miao_second_department_id']?>"><?= CommonFunc::getKeshiName($relationInfo['miao_second_department_id']);?></option>
                        <?php if(!empty($miao_slist)): ?>
                            <?php foreach($miao_slist as $value):?>
                            <option value="<?=$value['id']?>" <?php if($relationInfo['miao_second_department_id'] == $value['id']): ?>  selected="selected" <?php endif;?>  ><?=Html::encode($value['name'])?></option>
                            <?php endforeach;?>
                        <?php endif;?> 
                    
                    <?php else:?> 
                        <option value="">请选择王氏二级科室</option>
                    <?php endif;?> 
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">科室地址:</label>
            <div class="layui-input-block" style="width:150px;margin-left:100px;">
                <input type="text" name="address" value="<?=Html::encode($relationInfo['address'])?>" autocomplete="off" class="layui-input address">
            </div>
        </div>

        <div class="layui-form-item" style="display: none;">
          <div class="layui-input-block">
            <input type="text" name="id"  value="<?=$id;?>" >
          </div>
        </div>

    <?php ActiveForm::end();?>
    </div>
    <?php if(!empty($keshi_relation)): ?>
        <table id="main-table" class="table table-hover" lay-even="true" style="overflow-x:scroll;margin-top: 5px;">
            <thead>
            <tr>
                <th style="vert-align: middle;text-align: center">第三方科室ID</th>
                <th style="vert-align: middle;text-align: center">来源</th>
            </tr>
            </thead>
            <tbody align="center" valign="center">
            <?php foreach ($keshi_relation as $value): ?>
                <tr>
                    <td style=""><?php echo $value['tp_department_id']; ?></td>
                    <!--<td style=""><?php /*echo $value['tp_platform_name']; */?></td>-->
                    <td style=""><?=$platform_list[$value['tp_platform']] ?? '';?></td>
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>
    <?php else: ?>
    <?php endif; ?>
</div>

<script type="text/javascript">
    $('.cancle-relation').click(function(e){
        var _this = $(this);
        var tp_department_id = _this.attr('tp_department_id');
        var tp_platform = _this.attr('tp_platform');
        layer.confirm('您确定取消吗<br/>', function(index){
            var sauthUrl = "/keshi/cancle-relation";
            $.get(sauthUrl, {'tp_department_id':tp_department_id,tp_platform:tp_platform,"_csrf-backend":$('#_csrf-backend').val()}, function (res){
                if(res.status == 1){
                    layer.msg('操作成功！', {icon: 1});
                    setTimeout(function () {
                        window.location.href = window.location.href;
                    }, 3000);
                }else{
                    layer.msg(res.msg, {icon: 2});
                    setTimeout(function () {
                        window.location.href = window.location.href;
                    }, 3000);
                }
            });
            layer.close(index);
        });
    });
layui.use('form', function(){
    var form = layui.form;
    form.on('select(miao_search_fkid)', function (data){
        var fkeshi_id = data.value;
        if(fkeshi_id == ''){
            $(".miao_search_skid").html('<option value="">请选择王氏二级科室</option>');
            form.render('select');
            return false;
        }
        var keshiUrl = "/keshi/miao-second-department-list";
        $.get(keshiUrl, {'fkeshi_id':fkeshi_id,"_csrf-backend":$('#_csrf-backend').val()}, function (res){
            if(res.status == 1){
                var html = '<option value="">请选择王氏二级科室</option>';
                $.each(res.data, function (i, v){
                    html += '<option value="'+v.id+'">'+v.name+'</option>'; 
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