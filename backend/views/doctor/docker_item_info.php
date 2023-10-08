<?php

use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\libs\CommonFunc;

$request     = \Yii::$app->request;
$platform_list = CommonFunc::getTpPlatformNameList(1);
$this->title = '医生信息';
?>
<style type="text/css">
    #miaoid{
        height: 38px;line-height: 1.3;line-height: 38px;border-width: 1px;border-style: solid;background-color: #fff;border-radius: 2px;padding: 6px;
    }
    .sp_text_input{
        height: 38px;width:300px;line-height: 1.3;line-height: 38px;border-width: 1px; border-style: solid; background-color: #fff; border-radius: 2px;padding: 6px; }
</style>

<meta name="referrer" content="never">

<?php
$form = ActiveForm::begin(['action' => '/' . Yii::$app->controller->id . '/save', 'method' => 'post', 'options' => ['name' => 'form', 'class' => 'layui-form'], 'id' => 'form_id']);
?>

  <div class="layui-form-item">
    <label class="layui-form-label">姓名</label>
    <div class="layui-input-inline">
      <input id="realname" class="sp_text_input" type="text" name="realname" value="<?=$info['realname'] ?? $relationInfo['realname'] ?? '';?>" required  lay-verify="required" readonly placeholder="请输入输入框内容" autocomplete="off">
    </div>
  </div>  

  <div class="layui-form-item">
    <label class="layui-form-label">合作平台</label>
    <div class="layui-input-inline">
      <input class="sp_text_input" type="text" value="<?=$platform_list[$info['tp_platform']] ?? '';?>" readonly >
    </div>
  </div>


  <div class="layui-form-item">
    <label class="layui-form-label">第三方医生ID</label>
    <div class="layui-input-inline">
      <input class="sp_text_input" type="text" value="<?=$info['tp_doctor_id'] ?? '';?>" readonly >
    </div>
  </div> 

  <div class="layui-form-item">
    <label class="layui-form-label">第三方科室ID</label>
    <div class="layui-input-inline">
      <input class="sp_text_input" type="text" value="<?=$info['tp_department_id'] ?? '';?>" readonly >
    </div>
  </div>


<div class="layui-form-item layui-inline">
    <label class="layui-form-label" style="width:87px;">医院</label>
    <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
        <input type="text"  id="hosname" placeholder="请输入医院名称" autocomplete="off" class="layui-input" value="<?php echo $hospital['name'] ?? ''; ?>">
        <?php if (!empty($hospital)): ?>
        <select name="hospital_id" lay-verify="required" lay-search lay-filter="hos" class="hos" id="hos">
            <option value="<?php echo $info['hospital_id'] ?? ''; ?>" ><?=(isset($info['hospital_id']) && !empty($info['hospital_id'])) ? $info['hospital_id'] . '-' . $hospital['name'] ?? '' : '';?></option>
        </select>
        <?php else: ?>
            <select name="hospital_id" lay-verify="required" lay-search lay-filter="hos" class="hos" id="hos">
            <option value="<?php echo $info['hospital_id'] ?? ''; ?>" ></option>
            </select>
        <?php endif;?>

    </div>
</div>

    <div class="layui-form-item layui-inline">
        <label class="layui-form-label" style="width:87px;">医生科室</label>
        <div class="layui-input-block layui-inline" style="margin-left:0px;width:120px;">
            <select name="frist_department_id" lay-filter="search_fkid"  class="search_fkid">
                <option value="">一级科室</option>
                <?php if (!empty($fkeshiInfo)): ?>
                <?php foreach ($fkeshiInfo as $keshi): ?>
                    <option value="<?php echo $keshi['frist_department_id']; ?>" <?php if ($keshi['frist_department_id'] == ($info['frist_department_id'] ?? 0)) {echo 'selected="selected"';}?>><?php echo $keshi['frist_department_name']; ?></option>
                <?php endforeach;?>
                <?php endif;?>
            </select>
        </div>
        <div class="layui-input-block layui-inline" style="margin-left:10px;width:120px;">
            <select name="second_department_id" lay-filter="search_skid"  class="search_skid">
                <option value="">二级科室</option>
                <?php if (!empty($skeshiInfo)): ?>
                    <?php foreach ($skeshiInfo['second_arr'] as $keshi): ?>
                        <option value="<?php echo $keshi['second_department_id']; ?>" <?php if ($keshi['second_department_id'] == ($info['second_department_id'] ?? 0)) {echo 'selected="selected"';}?> ><?php echo $keshi['second_department_name']; ?></option>
                    <?php endforeach;?>
                <?php endif;?>
            </select>
        </div>
    </div>
<div class="layui-form-item layui-inline">
    <label class="layui-form-label" style="width:130px;">医生职称</label>
    <div class="layui-input-block" style="width:125px;margin-left:130px;">
        <select name="job_title_id" lay-verify="required" class="job_title">
            <option value="" >全部</option>
            <?php foreach ($doctor_titles as $k => $title): ?>
                <option value="<?php echo $k; ?>" <?php if (($info['job_title_id'] ?? 0) == $k) {echo 'selected="selected"';}?>><?php echo $title; ?></option>
            <?php endforeach;?>
        </select>
    </div>
</div>
<br/>



<?php if (!empty($relation_info)): ?>
<table id="main-table" class="table table-hover" lay-even="true" style="overflow-x:scroll;margin-top: 5px;">
    <thead>
    <caption>关联医生</caption>
    <tr>
        <th style="vert-align: middle;text-align: center">类型</th>
        <th style="vert-align: middle;text-align: center">医生ID</th>
        <th style="vert-align: middle;text-align: center">第三方医生ID</th>
        <th style="vert-align: middle;text-align: center">医生姓名</th>
        <th style="vert-align: middle;text-align: center">来源</th>
        <th style="vert-align: middle;text-align: center">时间</th>
        <?php if ($info['primary_id'] == 0): ?>
        <th style="vert-align: middle;text-align: center">备注</th>
        <?php endif;?>
    </tr>
    </thead>
    <tbody align="center" valign="center">
        <?php foreach ($relation_info as $value): ?>
        <tr>
        <td style=""><?=$info['primary_id'] > 0 ? '业务主医生' : '业务子医生';?></td>
        <td style=""><?php echo $value['doctor_id']; ?></td>
        <td style=""><?php echo $value['tp_doctor_id']; ?></td>
        <td style=""><?php echo $value['realname']; ?></td>
        <td style=""><?php echo $value['tp_platform_name']; ?></td>
        <td style=""><?=date('Y-m-d H:i:s', $value['create_time']);?></td>

        <?php if ($info['primary_id'] == 0): ?>
            <td style="">
            <button type="button" class="layui-btn layui-btn-xs layui-btn-danger cancle-relation" relation_id="<?=$value['doctor_id'];?>" realname="<?=$value['realname'];?>">
                取消关联
            </button>
        </td>
        <?php endif;?>
        </tr>
            <?php endforeach;?>
    </tbody>
</table>
        <?php else: ?>
    <?php endif;?>


<ul style="padding-top: 10px;">
    <li style="float: left;padding-left: 5px;">
        <table style="text-align: center;height: 40px;">
            <tr>
                <td><button class="layui-btn layui-btn-normal layui-btn-sm save" type="button" style="margin-left:20px;">保存</button></td>
            </tr>
        </table>
    </li>
</ul>
</br>
</br>
</br>
<?php ActiveForm::end();?>
<?php
//获取科室信息
$skeshiUrl = Url::to(['doctor/ajax-skeshi']);
$keshiUrl  = Url::to(['doctor/ajax-keshi']);
$hosUrl    = Url::to(['doctor/ajax-hos']);
$imgUrl    = Url::to(['upload/upload-avatar']);
//更新医生信息
$docUpdateUrl = Url::to(['doctor/save', 'tp_doctor_id' => $relationInfo['tp_doctor_id'] ?? 0, 'tp_platform' => $relationInfo['tp_platform'] ?? 0, 'doctor_id' => $id ?? 0, 'tmp_id' => $relationInfo['id'] ?? 0]);
$docInfo      = Url::to(['doctor/info']);
$cancleUrl    = Url::to(['doctor-relation/cancle-relation']);
?>

<script type="text/javascript">
    //日期组件
    layui.use(['form'], function() {
        var form = layui.form;
        //监听所有的下拉选框
        form.on('select', function(data) {
            var selecter = "option[value='" + data.value + "']";
            $(this).parents('.layui-form-select').siblings('select').find(selecter).attr("selected", "selected").siblings().removeAttr('selected');
        });
        $(".layui-input").on("input", function(e) {
            var name = e.delegateTarget.value;
            if (name == '') {
                return false;
            }
            var hosUrl = "<?=$hosUrl;?>";
            $.get(hosUrl, {
                'name': name
            }, function(data) {
                if (data.code == 200) {
                    var html = '<option value="">请选择医院</option>';
                    $.each(data.hos, function(i, v) {
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
                'hosid': hosid
            }, function(data) {
                if (data.code == 200) {
                    var html = '<option value="">一级科室</option>';
                    $.each(data.keshi, function(i, v) {
                        html += '<option value="' + v.frist_department_id + '">' + v.frist_department_name + '</option>';
                    });
                    $(".search_fkid").html(html);
                    $(".search_skid").html('');
                    $(".skid").html('');
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
                'hosid': hosid
            }, function(data) {
                if (data.code == 200) {
                    var html = '<option value="">二级科室</option>';
                    $.each(data.skeshi.second_arr, function(i, v) {
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
        //一级科室改变请求二级科室信息
        form.on('select(fkid)', function(data) {
            var pid = data.value;
            var hosid = $('#hos').val();
            var skeshiUrl = "<?=$skeshiUrl;?>";
            //监听修改一级科室
            editCallback($(data.elem));
            $.get(skeshiUrl, {
                'pid': pid,
                'hosid': hosid
            }, function(data) {
                if (data.code == 200) {
                    var html = '<option value="">二级科室</option>';
                    $.each(data.skeshi.second_arr, function(i, v) {
                        html += '<option value="' + v.second_department_id + '">' + v.second_department_name + '</option>';
                    });
                    $(".skid").html(html);
                    //重新渲染select
                    form.render('select');
                } else {
                    layer.msg('获取二级科室信息失败，请稍后重试！', {icon: 2,time: 2000});
                }
            });
        });
        //监听二级科室
        form.on('select(skid)', function(data) {});
    });

    function getHospital(name) {
        layui.use(['laydate', 'form', 'table'], function() {
            var form = layui.form;
            var hosUrl = "<?=$hosUrl;?>";
            $.get(hosUrl, {
                'name': name
            }, function(data) {
                if (data.code == 200) {
                    if (data.hos.length == 1) {
                        var che_hosinfo = data.hos[0];
                        console.log(che_hosinfo);
                        var html = '<option selected="selected" value="' + che_hosinfo.id + '">' + che_hosinfo.name + '</option>';
                        $(".hos").html(html);
                    } else {
                        var html = '<option value="">请选择医院</option>';
                        $.each(data.hos, function(i, v) {
                            html += '<option value="' + v.id + '">' + v.name + '</option>';
                        });
                        $(".hos").html(html);
                    }
                    //重新渲染select
                    form.render('select');
                } else {
                    layer.msg('获取信息失败，请稍后重试！', {icon: 2,time: 3000});
                }
            });
        })
    }

    function getKeshi(hosid) {
        layui.use(['laydate', 'form', 'table'], function() {
            var form = layui.form;
            var keshiUrl = "<?=$keshiUrl;?>";
            $.get(keshiUrl, {
                'hosid': hosid
            }, function(data) {
                if (data.code == 200) {
                    var html = '<option value="">一级科室</option>';
                    $.each(data.keshi, function(i, v) {
                        html += '<option value="' + v.frist_department_id + '">' + v.frist_department_name + '</option>';
                    });
                    $(".search_fkid").html(html);
                    $(".search_skid").html('');
                    $(".skid").html('');
                    //重新渲染select
                    form.render('select');
                } else {
                    layer.msg('获取科室信息失败，请稍后重试！', {icon: 2,time: 3000});
                }
            });
        })
    }

    $('.cancle-relation').click(function(e) {
        var _this = $(this);
        var relation_id = _this.attr('relation_id');
        var realname = _this.attr('realname');
        layer.confirm('您确定取消[ ' + realname + ' ]医生关联关系吗<br/>', function(index) {
            var sauthUrl = "<?=$cancleUrl;?>";
            $.get(sauthUrl, {
                'relation_id': relation_id
            }, function(res) {
                if (res.status == 1) {
                    layer.msg('操作成功！', {icon: 1});
                    setTimeout(function() {
                        window.location.href = window.location.href;
                    }, 3000);
                } else {
                    layer.msg(res.msg, {icon: 2});
                    setTimeout(function() {
                        window.location.href = window.location.href;
                    }, 3000);
                }
            });
            layer.close(index);
        });
    });
    //监听保存按钮
    $(".save").click(function() {
        //验证选择科室
        if ($(".hos").val() == '') {
            layer.msg('请选择医院！', {icon: 2});
            return false;
        }
        //验证选择科室
        if ($(".search_fkid").val() == '') {
            layer.msg('请选择一级科室信息！', {icon: 2});
            return false;
        }
        if ($(".job_title").val() == '') {
            layer.msg('请选择职称！', {icon: 2});
            return false;
        }
        //异步提交
        $.ajax({
            url: "<?=$docUpdateUrl;?>", //提交地址
            data: $("#form_id").serializeArray(), //将表单数据序列化
            type: "POST",
            timeout: 20000, //超时时间20秒
            dataType: "json",
            async: true,
            beforeSend: function() { // 禁用按钮防止重复提交
                $(".save").attr({
                    disabled: "disabled"
                });
                loading = showLoad();
            },
            complete: function() {
                layer.close(loading);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                layer.msg('保存失败，请刷新重试', {icon: 2,time: 3000});
                $(".save").removeAttr('disabled');
            },
            success: function(res) {
                if (res.status == 1) {
                    layer.msg('操作成功！', {icon: 1});
                    setTimeout(function() {
                        window.location.href = '/doctor/doc-list';
                    }, 3000);
                    return false;
                } else if (res.status == 201) {
                    $(".save").removeAttr('disabled');
                    layer.msg(res.msg, {icon: 2});
                    return false;
                }else{
                    layer.msg(res.msg, {icon: 2});
                    setTimeout(function() {
                        window.location.href = '/doctor/doc-list';
                    }, 3000);
                }
            }
        });
    });

</script>