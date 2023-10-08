<?php

use yii\helpers\Url;
use yii\widgets\LinkPager;
use backend\widget\PageWidget;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use common\libs\CommonFunc;
use yii\helpers\Html;

$request = \Yii::$app->request;
$this->title = '医生信息';
$platform_list = CommonFunc::getTpPlatformNameList();
?>
<style type="text/css">
    #miaoid{
        height: 38px;line-height: 1.3;line-height: 38px;border-width: 1px;border-style: solid;background-color: #fff;border-radius: 2px;padding: 6px;
    }
    .sp_text_input{
        height: 38px;width:300px;line-height: 1.3;line-height: 38px;border-width: 1px; border-style: solid; background-color: #fff; border-radius: 2px;padding: 6px; }
</style>

<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<meta name="referrer" content="never">
<?php if($request->get('tp_doctor_id', '')!=''):?>
<p style="color:red;">医生信息：医院-[ <?php echo Html::encode(ArrayHelper::getValue($relationInfo,'hospital_name', ''));?> ]
    科室：[ <?= $relationInfo['frist_department_name']??'';?> ]-[ <?= $relationInfo['second_department_name']??'';?> ]
    职称：[ <?= 1 . $relationInfo['job_title']??'未知';?> ]
</p>
<?php endif;?>

<?php
$form = ActiveForm::begin(['action' => '/' . Yii::$app->controller->id . '/save', 'method' => 'post', 'options' => ['name' => 'form','class'=>'layui-form','enctype' => 'multipart/form-data'],'id'=>'form_id']);
?>
<?php if($request->get('doctor_id', '')==''):?>
    <div class="layui-form-item hide">
        <label class="layui-form-label">王氏平台</label>
        <div class="layui-input-inline">
            <input type="hidden" value="<?php if(isset($relationInfo['tp_platform']) && $relationInfo['tp_platform'] == 6):?> 
                <?=$relationInfo['tp_doctor_id'] ?? 0;?>
             <?php else:?>
             <?php endif;?>" <?php if(isset($relationInfo['tp_platform']) && $relationInfo['tp_platform'] == 6):?> readonly="readonly" <?php endif;?> placeholder="医生id"  name="miao_doctor_id" id="miaoid" > 
        </div>

        <?php if(isset($relationInfo['tp_platform']) && $relationInfo['tp_platform'] == 6):?> 
        <?php else:?>
            <button class="layui-btn layui-btn-normal layui-btn-sm docinfo" type="button"  data-tp_platform="<?=$relationInfo['tp_platform'] ?? 0;?>" style="margin-left:20px;">获取医生信息</button>
        <?php endif;?>
    </div>
    <?php if($request->get('tp_doctor_id', '')!=''):?>
        <div class="layui-form-item">
            <label class="layui-form-label">第三方平台</label>
            <p class="layui-input-inline">
                <input type="text" class="sp_text_input" readonly="readonly"  value="医生ID: <?=$relationInfo['tp_doctor_id']??0;?> 来源:<?=$relationInfo['tp_platform_name']??''?>" ></p>

        </div>
    <?php endif;?>
<?php endif;?>
  <div class="layui-form-item">
    <label class="layui-form-label">姓名</label>
    <div class="layui-input-inline">
      <input id="realname" class="sp_text_input" type="text" name="realname" value="<?= Html::encode(ArrayHelper::getValue($info,'realname', ''))?:Html::encode(ArrayHelper::getValue($relationInfo,'realname', ''));?>" required  lay-verify="required" placeholder="请输入输入框内容" autocomplete="off">
    </div>
  </div>
<div class="layui-form-item">
    <label class="layui-form-label">权重</label>
    <div class="layui-input-inline">
        <input type="number" class="sp_text_input" name="weight" value="<?= $info['weight']??0;?>" required  lay-verify="required" placeholder="" autocomplete="off">
    </div>
</div>
    <div class="layui-form-item  layui-form-text image-type pic-form-group1" >
        <label class="layui-form-label" style="margin-left:0px;width:100px;">头像:</label>

        <div class="layui-input-block" style="margin-left:0px;width:700px;">
            <div class="col-sm-3">
                <input type="file" name="file" class="pic-file1"  />
                <input type="hidden" name="avatar" id="hash2" value="<?php echo $info['avatar']??'';?>"/>
                <input type="hidden" name="source_avatar" value="<?php echo $relationInfo['source_avatar'] ?? ''; ?>"/>
            </div>
            <div class="col-sm-3" style="margin-left: 60px;">
                <button class="btn btn-info btn-upload-ok1" type="button">确定上传</button>
            </div>
            <div style=""><span class="middle">（图片类型：gif, jpg, jpeg, png ）</span></div>

            <?php if(isset($info['avatar']) && !empty($info['avatar'])):?>
                <div class="col-sm-12 isup" style="width:auto;">
                    <img src="<?php echo $imagePath.$info['avatar'].'?v='.time();?>" width='88px' height='88px' />
                </div>
            <?php elseif(isset($relationInfo['source_avatar']) && !empty($relationInfo['source_avatar'])):?>
                <div class="col-sm-12 isup" style="width:auto;">
                    <img src="<?php echo $relationInfo['source_avatar'].'?v='.time();?>" width='88px' height='88px' />
                </div>
            <?php else:?>
                <div class="col-sm-12 isup" style="display: none;width:auto;">
                    <img src=""  width='88px' height='88px'/>
                </div>
            <?php endif;?>

            <span style="color: #ff0000"  class="help-inline col-xs-12 col-sm-7">
                </span>
        </div>
    </div>
<div class="layui-form-item layui-inline">
    <label class="layui-form-label" style="width:87px;">医院</label>
    <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
        <input type="text"  id="hosname" placeholder="请输入医院名称" autocomplete="off" class="layui-input" value="<?php echo isset($hospital['name'])? Html::encode($hospital['name']) :'';?>">
        <?php if(!empty($hospital)): ?>
        <select name="hospital_id" lay-verify="required" lay-search lay-filter="hos" class="hos" id="hos">
            <option value="<?php echo $info['hospital_id']??'';?>" ><?=(isset($info['hospital_id']) && !empty($info['hospital_id'])) ? (isset($hospital['name']) ? $info['hospital_id'].'-'.Html::encode($hospital['name']) : '') : '';?></option>
        </select>
        <?php else:?>
            <select name="hospital_id" lay-verify="required" lay-search lay-filter="hos" class="hos" id="hos">
            <option value="<?php echo $info['hospital_id']??'';?>" ></option>
            </select>
        <?php endif;?>

    </div>
</div>

    <div class="layui-form-item layui-inline">
        <label class="layui-form-label" style="width:87px;">医生科室</label>
        <div class="layui-input-block layui-inline" style="margin-left:0px;width:120px;">
            <select name="frist_department_id" lay-filter="search_fkid"  class="search_fkid">
                <option value="">一级科室</option>
                <?php if(!empty($fkeshiInfo)): ?>
                <?php foreach($fkeshiInfo as $keshi): ?>
                    <option value="<?php echo $keshi['frist_department_id']; ?>" <?php if($keshi['frist_department_id'] == ($info['frist_department_id']??0)){ echo 'selected="selected"'; } ?>><?php echo Html::encode($keshi['frist_department_name']);?></option>
                <?php endforeach; ?>
                <?php endif;?>
            </select>
        </div>
        <div class="layui-input-block layui-inline" style="margin-left:10px;width:120px;">
            <select name="second_department_id" lay-filter="search_skid"  class="search_skid">
                <option value="">二级科室</option>
                <?php if(!empty($skeshiInfo)): ?>
                    <?php foreach($skeshiInfo['second_arr'] as $keshi): ?>
                        <option value="<?php echo $keshi['second_department_id'];?>" <?php if($keshi['second_department_id'] == ($info['second_department_id']??0)){ echo 'selected="selected"'; }?> ><?php echo Html::encode($keshi['second_department_name']);?></option>
                    <?php endforeach; ?>
                <?php endif;?>
            </select>
        </div>
    </div>
<div class="layui-form-item layui-inline">
    <label class="layui-form-label" style="width:130px;">医生职称</label>
    <div class="layui-input-block" style="width:125px;margin-left:130px;">
        <select name="job_title_id" lay-verify="required" class="job_title">
            <option value="" >全部</option>
            <?php foreach($doctor_titles as $k=>$title): ?>
                <option value="<?php echo $k; ?>" <?php if(($info['job_title_id']??0) == $k){ echo 'selected="selected"'; }?>><?php echo Html::encode($title); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<br/>

<div class="layui-form-item layui-form-text">
    <label class="layui-form-label" style="width:87px;">擅长</label>
    <div class="layui-input-block" style="margin-left: 88px;">
        <textarea name="good_at" required placeholder="请输入内容" class="layui-textarea"><?= Html::encode(ArrayHelper::getValue($info, 'good_at', '')) ?: Html::encode(ArrayHelper::getValue($relationInfo, 'good_at', ''))?></textarea>
    </div>
</div>
<div class="layui-form-item layui-form-text">
    <label class="layui-form-label" style="width:87px;">个人简介</label>
    <div class="layui-input-block" style="margin-left: 88px;">
        <textarea name="profile" required placeholder="请输入内容" class="layui-textarea"><?= Html::encode(ArrayHelper::getValue($info, 'profile', '')) ?: Html::encode(ArrayHelper::getValue($relationInfo, 'profile', ''))?></textarea>
    </div>
</div>

<input type="hidden" name="force_add" value="0" id="force_add">
<?php if(!empty($info)): ?>
<table id="main-table" class="table table-hover" lay-even="true" style="overflow-x:scroll;margin-top: 5px;">
    <thead>
    <caption>关联医生</caption>
    <tr>
        <th style="vert-align: middle;text-align: center">类型</th>
        <th style="vert-align: middle;text-align: center">医生ID</th>
        <th style="vert-align: middle;text-align: center">第三方医院ID</th>
        <th style="vert-align: middle;text-align: center">第三方科室ID</th>
        <th style="vert-align: middle;text-align: center">第三方医生ID</th>
        <th style="vert-align: middle;text-align: center">医生姓名</th>
        <th style="vert-align: middle;text-align: center">医院</th>
        <th style="vert-align: middle;text-align: center">科室</th>
        <th style="vert-align: middle;text-align: center">来源</th>
        <th style="vert-align: middle;text-align: center">时间</th>
        <!-- <th style="vert-align: middle;text-align: center">操作人</th> -->
        <?php if($info['primary_id'] == 0 && !empty($relationInfo)): ?>
        <th style="vert-align: middle;text-align: center">操作</th>
        <?php endif; ?>
    </tr>
    </thead>
    <tbody align="center" valign="center">
        <tr>
            <td style="">当前医生</td>
            <td style=""><?php echo $info['doctor_id']; ?></td>
            <td style=""><?php echo $info['tp_hospital_code']; ?></td>
            <td style=""><?php echo $info['tp_department_id']; ?></td>
            <td style=""><?php echo $info['tp_doctor_id']; ?></td>
            <td style=""><?php echo Html::encode(ArrayHelper::getValue($info,'realname', '')); ?></td>
            <td style=""><?php echo Html::encode(ArrayHelper::getValue($info,'hospital_name', '')); ?></td>
            <td style=""><?=Html::encode($info['frist_department_name'].'-'.$info['second_department_name']);?></td>
            <!--<td style=""><?php /*echo $info['tp_platform_name']; */?></td>-->
            <td style=""><?=$platform_list[$info['tp_platform']] ?? '';?></td>
            <td style=""><?=date('Y-m-d H:i:s',$info['create_time']); ?></td>
            <?php if($info['primary_id'] == 0 && !empty($relationInfo)): ?>
                <td style=""></td>
            <?php endif; ?>
        </tr>
        <?php if(!empty($relationInfo)): ?>
        <?php foreach ($relationInfo as $value): ?>
        <tr>
            <td style=""><?=$info['primary_id'] >0 ? '业务主医生' : '业务子医生';?></td>
            <td style=""><?php echo $value['doctor_id']; ?></td>
            <td style=""><?php echo $value['tp_hospital_code']; ?></td>
            <td style=""><?php echo $value['tp_department_id']; ?></td>
            <td style=""><?php echo $value['tp_doctor_id']; ?></td>
            <td style=""><?php echo Html::encode(ArrayHelper::getValue($value,'realname', '')); ?></td>
            <td style=""><?php echo Html::encode(ArrayHelper::getValue($value,'hospital_name', '')); ?></td>
            <td style=""><?=Html::encode($value['frist_department_name'].'-'.$value['second_department_name']);?></td>
            <!--<td style=""><?php /*echo $value['tp_platform_name']; */?></td>-->
            <td style=""><?=$platform_list[$value['tp_platform']] ?? '';?></td>
            <td style=""><?=date('Y-m-d H:i:s',$value['create_time']); ?></td>

            <?php if($info['primary_id'] == 0): ?>
                <td style="">
                    <button type="button" class="layui-btn layui-btn-xs layui-btn-danger cancle-relation" relation_id="<?=$value['doctor_id'];?>" realname="<?=html::encode($value['realname'] ?? '');?>">
                        取消关联
                    </button>
                </td>
            <?php endif; ?>
        </tr>
        <?php endforeach;?>
        <?php endif; ?>
    </tbody>
</table>
<?php endif; ?>

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
<?php ActiveForm::end(); ?>
<?php
//获取科室信息
$skeshiUrl = Url::to(['doctor/ajax-skeshi']);
$keshiUrl = Url::to(['doctor/ajax-keshi']);
$hosUrl = Url::to(['doctor/ajax-hos']);
//$imgUrl = Url::to(['upload/upload-avatar']);
$imgUrl = Url::to(['upload/upload-avatar-oss']);
//更新医生信息
$docUpdateUrl = Url::to(['doctor/save','tp_doctor_id'=>$relationInfo['tp_doctor_id']??0,'tp_platform'=>$relationInfo['tp_platform']??0,'doctor_id'=>$id??0,'tmp_id'=>$relationInfo['id'] ?? 0]);
$docInfo = Url::to(['doctor/info']);
$cancleUrl = Url::to(['doctor-relation/cancle-relation']);
?>

<script type="text/javascript">
    //日期组件
    layui.use(['laydate', 'form', 'table'], function() {
        var laydate = layui.laydate;
        var form = layui.form;
        //开通时间
        laydate.render({
            elem: '#power_create_time', //指定元素
            range: true
        });
        laydate.render({
            elem: '#power_update_time', //指定元素
            range: true
        });
        //重置表单选择
        $("#reset").click(function() {
            var fid = getValue().fid;
            var page = getValue().page;
            var str = '?';
            if (fid) {
                str += 'fid=' + fid + '&';
            }
            if (page) {
                str += 'page=' + page
            }
            window.location.href = window.location.href.split('?')[0] + str;
        });
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
                'name': name,"_csrf-backend":$('#_csrf-backend').val()
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
                'hosid': hosid,"_csrf-backend":$('#_csrf-backend').val()
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
                'hosid': hosid,
                "_csrf-backend":$('#_csrf-backend').val()
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
                'hosid': hosid,
                "_csrf-backend":$('#_csrf-backend').val()
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
                'name': name,"_csrf-backend":$('#_csrf-backend').val()
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
                'hosid': hosid,"_csrf-backend":$('#_csrf-backend').val()
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
    $(".pic-file1").change(function(e) {
        var file = e.target.files[0] || e.dataTransfer.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function() {
                $(".pic-form-group1 img").parent().show();
                $(".pic-form-group1 img").attr("src", this.result).parent().show();
            }
            reader.readAsDataURL(file);
        }
    });
    $('.btn-upload-ok1').on('click', function() {
        uploadImg1();
    });
    $('.cancle-relation').click(function(e) {
        var _this = $(this);
        var relation_id = _this.attr('relation_id');
        var realname = _this.attr('realname');
        layer.confirm('您确定取消[ ' + realname + ' ]医生关联关系吗<br/>', function(index) {
            var sauthUrl = "<?=$cancleUrl;?>";
            $.get(sauthUrl, {
                'relation_id': relation_id,"_csrf-backend":$('#_csrf-backend').val()
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
        if ($(".isup img").attr('src').indexOf("base64") != -1) {
            layer.msg('请点击 确定上传按钮 上传图片！', {icon: 2,time: 2000});
            return false;
        }
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
                $(".save").attr({disabled: "disabled"});
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
                }else if (res.status == 202) {
                    $("#force_add").val(0);
                    $(".save").removeAttr('disabled');
                    layer.confirm(res.msg, function(index){
                        $("#force_add").val(1);
                        $(".save").click();
                        layer.close(index);
                    });
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

    function uploadImg1() {
        var formData = new FormData();
        formData.append('Filedata', $(".pic-file1")[0].files[0]);
        formData.append('_csrf-backend', $('#_csrf-backend').val());
        var file = $(".pic-file1")[0].files[0];
        if (!file) {
            layer.msg('请选图片！', {icon: 2});
            return false;
        }
        $.ajax({
            url: "<?=$imgUrl;?>",
            type: 'POST',
            cache: false,
            data: formData,
            processData: false,
            contentType: false
        }).done(function(data) {
            var res = eval("(" + data + ")");
            if (res.error == 0) {
                $(".pic-form-group1 .col-sm-3 input[type='hidden']").val(res.img_path);
                $(".pic-form-group1 img").parent().show();
                var imgInfo = "<img src='" + res.img_url + "' width='264px' height='88px'/>" + "<input type='hidden' name='image' value='" + res.img_path + "'/>"
                $(".pic-form-group1 .col-sm-12 img").attr('src', res.img_url);
                $(".pic-form-group1 .col-sm-12 input").val(res.img_path);
                $(".pic-form-group1 .col-sm-12").show();
                $("#img_up_input").val(res.img_path);
                //$(".pic-form-group1 img").attr('src',res.img_url);
                $(".image-type input[name='imagelink']").val(res.img_url);
                layer.msg('上传成功！', {
                    icon: 1
                });
            } else {
                layer.msg(res.message, {
                    icon: 2
                });
            }
        }).fail(function(res) {});
    }
    $(".docinfo").click(function() {
        var miaoid = $("#miaoid").val();
        var tp_platform = $(this).attr("data-tp_platform");
        if (tp_platform == 6) {
            return false;
        }
        if (!miaoid) {
            return layer.msg('请输入王氏id', {icon: 2});
        }
        var docid = $("#docid").val();
        var docUrl = "<?=$docInfo;?>";
        $.get(docUrl, {
            'miaoid': miaoid,
            'doc_id': docid,
            "_csrf-backend":$('#_csrf-backend').val()
        }, function(res_data) {
            if (res_data.status == 1) {
                 res = res_data.data;
                $("textarea[name='good_at']").val(res.good);
                $("textarea[name='profile']").val(res.profile);
                $("#realname").val(res.realname);
                $("#hosname").val(res.hospital);
                $("#miao_doctor_id").val(res.doctor_id);
                getHospital(res.hospital);
                getKeshi(res.hospital_id);
                $(".job_title").find("option[value='" + res.title_id + "']").prop("selected", "selected");
                layui.form.render();
                $(".pic-form-group1 .col-sm-3 input[type='hidden']").val(res.img_path);
                $(".pic-form-group1 img").parent().show();
                var imgInfo = "<img src='" + res.img_url + "' width='264px' height='88px'/>" + "<input type='hidden' name='image' value='" + res.img_path + "'/>"
                $(".pic-form-group1 .col-sm-12 img").attr('src', res.img_url);
                $(".pic-form-group1 .col-sm-12 input").val(res.img_path);
                $(".pic-form-group1 .col-sm-12").show();
                $("#img_up_input").val(res.img_path);
                $(".image-type input[name='imagelink']").val(res.img_url);
            } else {
                layer.msg(res_data.msg, {icon: 2,time: 3000});
            }
        });
        return false;
    });
</script>