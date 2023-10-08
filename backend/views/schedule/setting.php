<?php
use common\libs\CommonFunc; //引入辅助表单类
use common\models\GuahaoScheduleModel; //引入数据小插件类
use yii\helpers\ArrayHelper; //引入辅助表单类
use yii\helpers\Html;
use yii\helpers\Url; //引入数据小插件类
use yii\widgets\ActiveForm;

$week_days      = array_keys($week_list);
$visit_nooncode = GuahaoScheduleModel::$visit_nooncode;
$this->title    = $place_info['realname'] . '医生 排班设置' . reset($week_days) . ' / ' . end($week_days);
$visit_cost = $visit_cost ?: 0;
?>
<style type="text/css">
    #grab_form{
        margin-left: 10px;
    }
    #dtable tr{
        line-height: 10px;
        height: 10px;
    }
    #dtable td{
        line-height: 10px;
        height: 10px;
    }

    .layui-form-checkbox i {
        border-left: 1px solid #d2d2d2;
    } 

    .layui-form-checked i{
        color: #3aa558;
    }
    .layui-form-checkbox:hover i {
        color: #c1f1ce;
    }
    .layui-form-checked,.layui-form-checked:hover i {
        color: #3aa558;
    }

</style>

<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="layui-form">
    <?php $form = ActiveForm::begin(['action' => Url::to(['schedule/save']), 'method' => 'post', 'options' => ['name' => 'form', 'class' => 'layui-form55', 'id' => 'grab_form', 'style' => 'width:500px;margin']]);?>

        <div class="layui-form-item" style="margin-left: 50px;">
            <a href="<?=Url::to(['schedule/setting', 'id' => $id, 'page' => ($page - 1)]);?>" class="layui-btn layui-btn-normal">上周</a>
            <a href="<?=Url::to(['schedule/setting', 'id' => $id, 'page' => 0]);?>" class="layui-btn">本周</a>
            <a href="<?=Url::to(['schedule/setting', 'id' => $id, 'page' => ($page + 1)]);?>" class="layui-btn layui-btn-warm">下周</a>
        </div>

        <input type="hidden" name="id" value="<?=$id;?>" >
        <input type="hidden" name="page" value="<?=$page;?>" >

        <div class="layui-form-item">
            <label class="layui-form-label" style="width:100px;">执业地点:</label>
            <div class="layui-input-inline">
                <input type="text" name="" value="<?=$hospital_name;?>" readonly="readonly"  class="layui-input">
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">一级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="tp_frist_department_id" lay-verify="required" lay-search="" lay-filter="search_fkid" id="search_fkid"  class="search_fkid">
                    <option value="">请选择一级科室</option>
                    <?php if (!empty($fkeshi_list)): ?>
                        <?php foreach ($fkeshi_list as $value): ?>
                        <option value="<?=$value['frist_department_id']?>" <?php if ($tp_frist_department_id == $value['frist_department_id']): ?>  selected="selected" <?php endif;?> ><?=$value['frist_department_name']?></option>
                        <?php endforeach;?>
                    <?php endif;?>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">二级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="tp_department_id" lay-verify="required" lay-search="" lay-filter="search_skid" id="search_skid"  class="search_skid">
                <?php if ($tp_department_id): ?>
                    <?php foreach ($skeshi_list as $value): ?>
                        <option value="<?=$value['second_department_id']?>" <?php if ($tp_department_id == $value['second_department_id']): ?>  selected="selected" <?php endif;?> ><?=$value['second_department_name']?></option>
                    <?php endforeach;?>
                <?php else: ?>
                    <option value="">请选择二级科室</option>
                <?php endif;?>
                </select>
            </div>
        </div>

        <input name="tp_frist_department_name" type="hidden" value="" id="fkeshi_name" />
        <input name="department_name" type="hidden" value="" id="skeshi_name" />

        <div class="layui-form-item">
            <label class="layui-form-label" style="width:100px;">医事费用:</label>
            <div class="layui-input-inline">
              <input type="text" name="visit_cost" <?php if (!empty($visit_cost)): ?>  value="<?=$visit_cost?>" <?php endif;?> id="visit_cost" required lay-verify="required" placeholder="请输入医事服务费" autocomplete="off" class="layui-input">
            </div>
          </div>
        <div class="layui-form-item7">
            <table class="layui-table" id="dtable" width="80%" lay-size="sm">
              <colgroup>
                <col width="15%">
                <col width="10%">
                <col width="10%">
                <col width="10%">
              </colgroup>
              <thead>
                <tr>
                  <th width="15%">日期</th>
                  <th width="10%">时间段</th>
                  <th width="10%">费用</th>
                  <th width="10%">操作</th>
                </tr>
              </thead>
              <tbody>
                <!-- <tr style="height: 30px;line-height: 30px;">
                    <td colspan="3"></td>
                    <td class="layui-form"><input type="checkbox" lay-filter="all_check" value="1" <?=$node_count >= 14 ? 'checked=""' :"";?>  class="all_check"></td>
                </tr> -->
                <?php foreach ($week_list as $w_k => $w_item): ?>
                <tr>
                    <td rowspan="2"><?=$w_item['date'] . ' ' . $w_item['week'];?></td>
                    <td>上午</td>
                    <td class="visit_cost"><?=$visit_cost;?></td>
                    <td><?php echo Html::checkbox('nodes[' . $w_item['date'] . '][visit_nooncode][1]', isset($gaohao_list[$w_item['date']]['noon_list'][1]) && $gaohao_list[$w_item['date']]['noon_list'][1] == 1 ? 1 : 0, ['value' => 1],[],['class'=>'checkboxList']); ?>
                        <input name="nodes[<?=$w_item['date'];?>][scheduling_id]" type="hidden" value="<?=$gaohao_list[$w_item['date']]['scheduling_id'] ?? 0;?>">
                    </td>

                </tr>
                <tr>
                    <td>下午</td>
                    <td class="visit_cost"><?=$visit_cost;?></td>
                    <td><?php echo Html::checkbox('nodes[' . $w_item['date'] . '][visit_nooncode][2]', isset($gaohao_list[$w_item['date']]['noon_list'][2]) && $gaohao_list[$w_item['date']]['noon_list'][2] == 2 ? 1 : 0, ['value' => 2]); ?>
                        <input name="nodes[<?=$w_item['date'];?>][scheduling_id]" type="hidden" value="<?=$gaohao_list[$w_item['date']]['scheduling_id'] ?? 0;?>">
                    </td>
                </tr>
                <?php endforeach;?>
              </tbody>
            </table>
        </div>

        <input name="sub_type" type="hidden" value="1" id="sub_type" />
        <div class="layui-form-item">
          <div class="layui-input-block">
            <button type="reset" class="layui-btn layui-btn">重置</button>
            <button class="layui-btn layui-btn-normal submit_form" lay-submit type="button" data-sub_type="1" id="submit_form">添加</button>
            <button class="layui-btn layui-btn-danger submit_form" lay-submit type="button" data-sub_type="0" id="submit_cancel">取消</button>
          </div>
        </div>
    <?php ActiveForm::end();?>
</div>

<script type="text/javascript">
    layui.use(['form','layui'], function(){
        form = layui.form;
        layer = layui.layer;
    });

    layui.use(['form', 'layer' ], function () {
        form = layui.form;
        layer = layui.layer;
        form.on('checkbox(all_check)', function (data) {
            var child = $("input[type='checkbox']");
            child.each(function (index, item) {
                item.checked = data.elem.checked;
            });
            form.render('checkbox');
        });
    });

    

    function showLoad() {
        return layer.msg('拼命执行中...', {icon: 16,shade: [0.5, '#f5f5f5'],scrollbar: false,offset: 'auto', time:20000});
    }

    $(function(){
        var $browsers = $("input[name=nodes]");
        var $cancel = $("#cancel");
        $cancel.click(function(e){
            $browsers.attr("checked",false);
        });
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
            $.get(keshiUrl, {'pid':fkeshi_id,'hosid':hosid,"_csrf-backend":$('#_csrf-backend').val()}, function (res){
                if(res.status == 1){
                    var html = '<option value="">请选择二级科室</option>';
                    $.each(res.data.second_arr, function (i, v){
                          html += '<option value="'+v.second_department_id+'">'+v.second_department_name+'</option>';
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


    $("#visit_cost").on("input",function(e){
        var visit_cost = e.delegateTarget.value;
        if(visit_cost == ''){
            return false;
        }
        if(visit_cost !=''&& visit_cost.substr(0,1) == '.'){  
             visit_cost="";  
         }  
         visit_cost = visit_cost.replace(/^0*(0\.|[1-9])/, '$1');//解决 粘贴不生效  
         visit_cost = visit_cost.replace(/[^\d.]/g,"");  //清除“数字”和“.”以外的字符  
         visit_cost = visit_cost.replace(/\.{2,}/g,"."); //只保留第一个. 清除多余的       
         visit_cost = visit_cost.replace(".","$#$").replace(/\./g,"").replace("$#$",".");      
         visit_cost = visit_cost.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');//只能输入两个小数       
         if(visit_cost.indexOf(".")< 0 && visit_cost !=""){//以上已经过滤，此处控制的是如果没有小数点，首位不能为类似于 01、02的金额  
             if(visit_cost.substr(0,1) == '0' && visit_cost.length == 2){  
                 visit_cost= visit_cost.substr(1,visit_cost.length);      
             }  
         }      

        if (visit_cost > 9999999) {
            visit_cost = visit_cost.substr(0,9);
            $('.visit_cost').html(visit_cost);
            $('#visit_cost').focus();
            $('#visit_cost').val(visit_cost);
            return false;
         }

        var re = /^\d+(?=\.{0,1}\d+$|$)/ 
        if (visit_cost != "") { 
            if (!re.test(visit_cost)) { 
                $('#visit_cost').focus();
                $('.visit_cost').html(0);
                return false;
            }else{
                $('#visit_cost').val(visit_cost);
                $('.visit_cost').html(visit_cost);
            }
        }else{
            $('.visit_cost').html(0);
        }

    });


</script>
<script type="text/javascript">
    $(".submit_form").click(function (event) {
        dovoice();
    if ($(this).attr('disabled') == 'disabled') {
        return false;
    }
    var sub_type = $(this).attr("data-sub_type");

    var sub_type_text = sub_type == 1 ? '提交' : '取消';

    $('#sub_type').val(sub_type);

    var _this = $(this);

    var search_fkid = $('#search_fkid option:selected').val(); // 文本值
    var search_skid = $('#search_skid option:selected').val(); // 文本值

    if (!search_fkid || !search_skid) {
        return  layer.msg('科室不能为空！', {icon: 2});
    }
    var visit_cost = $('#visit_cost').val(); // 文本值
    if (!visit_cost) {
        return  layer.msg('服务费不能为空！', {icon: 2});
    }

    var re = /^\d+(?=\.{0,1}\d+$|$)/ 
    if (visit_cost != "") { 
        if (!re.test(visit_cost)) { 
            $('#visit_cost').focus();
            return layer.msg('请输入正确的数字', {icon: 2});
        } 
    }else{
        return layer.msg('请输入出诊费', {icon: 2});
    }

    var search_fkid_name = $('#search_fkid option:selected').text(); // 文本值
    var search_skid_name = $('#search_skid option:selected').text(); // 文本值

    $('#fkeshi_name').val(search_fkid_name);
    $('#skeshi_name').val(search_skid_name);

    if (confirm('确定要'+sub_type_text+'吗?') ? true : false) {
        var formData = $('#grab_form').serialize();
        var url =  $('#grab_form').attr('action');
         $.ajax({
            url: url,
            data: formData,
            timeout: 20000,//超时时间20秒
            type: 'POST',
            async: true,
            beforeSend: function () {
                // 禁用按钮防止重复提交
                _this.attr({ disabled: "disabled" });
                loading = showLoad();
            },
            success: function (res) {
                layer.close(loading);
                if (res.status == 1) {
                    layer.msg(res.msg, {icon: 1});
                    // window.location.reload();
                    setTimeout(function (){
                        window.location.reload();
                        // window.location.href='/schedule/index';
                    }, 1000);
                } else {
                    layer.msg(res.msg, {icon: 2});
                    setTimeout(function (){
                        window.location.reload();
                    }, 3000);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.close(loading);
                layer.msg('保存失败，请刷新重试', {icon: 2});
            }
        });
    }
});
</script>

