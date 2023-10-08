<?php
use yii\helpers\Url;
use yii\helpers\Html; 
use yii\widgets\LinkPager;
use common\libs\Url as CommonUrl;
use common\libs\CommonFunc;
use \yii\helpers\ArrayHelper;
use common\components\GoPager;//新分页
use backend\models\DoctorInfoModel;
use common\libs\HashUrl;
use common\libs\DoctorUrl;
$request = \Yii::$app->request;
$platform_list = CommonFunc::getTpPlatformNameList();
$this->title = '医生列表';
?>

<style>
    .layui-table tbody tr:hover{background: none;}
    .layui-form-label {width:100px;font-size:14px;}
    .layui-input-block {margin-left:160px;}
    .layui-textarea{min-height:60px;}
    .layui-layer-shade{display:none;}
    /*.layui-form-checkbox{display:none;}*/
    .check_faild_reason_css{
        overflow:hidden; word-wrap:break-word;
    }
    #doctor_type_box,.flex_div{
        display: flex;
        display: -webkit-flex; 
    }

</style>

<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="layui-row">
    <form class="layui-form" action="">
        <div class="layui-form-item layui-inline" >
            <label class="layui-form-label" style="width:100px;">医院医生</label>
            <div class="layui-input-block" id="doctor_type_box" style="width:200px;margin-left:10px;">
                <?php $doc_type_list = ['doctor_id'=>'ID','doctor_name'=>'姓名','doctor_hash_id'=>'加密id'];?>
                <?php echo Html::dropDownList('doc_type',$request->get('doc_type') ?? '',$doc_type_list,array('id'=>'doc_type_list',"class"=>"docbox_input_l form-control",'style'=>'width:40px;'));?>
                <input type="text" name="doctor" <?php if($request->get('doctor')){echo 'value="'.Html::encode($request->get('doctor')).'"';}?> placeholder="ID|加密id|姓名" autocomplete="off" style="width: 120px;" class="docbox_input_r layui-input form-control">
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">王氏医生ID</label>
            <div class="layui-input-block" style="width:100px;margin-left:102px;">
                <input type="text" name="miao_doctor_id" <?php if($request->get('miao_doctor_id')){echo 'value="'.Html::encode($request->get('miao_doctor_id')).'"';}?> placeholder="王氏医生ID" autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">对接平台</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;" >
                <?php $tp_platform_list = [''=>'全部']+CommonFunc::getTpPlatformNameList(1)+['0'=>'其他'];?>
                <?php echo Html::dropDownList('tp_platform',$request->get('tp_platform') ?? '',$tp_platform_list,array('id'=>'tp_platform_list',"class"=>"form-control input-sm "));?>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:87px;">医生科室</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:120px;">
                <select name="fkid" lay-filter="search_fkid"  class="search_fkid" lay-search="">
                    <option value="">一级科室</option>
                    <?php if(!empty($fkeshiInfo)): ?>
                    <?php foreach($fkeshiInfo as $keshi): ?>
                        <option value="<?php echo Html::encode($keshi['department_id']); ?>" <?php if($keshi['department_id'] == $request->get('fkid')){ echo 'selected="selected"'; } ?>><?php echo Html::encode($keshi['department_name']);?></option>
                    <?php endforeach; ?>
                    <?php endif;?>
                </select>
            </div>
            <div class="layui-input-block layui-inline" style="margin-left:10px;width:120px;">
                <select name="skid" lay-filter="search_skid"  class="search_skid" lay-search="">
                    <option value="">二级科室</option>
                    <?php if(isset($skeshiInfo)&&!empty($skeshiInfo)): ?>
                    <?php foreach($skeshiInfo as $keshi): ?>
                        <option value="<?php echo Html::encode($keshi['department_id']);?>" <?php if($keshi['department_id'] == $request->get('skid')){ echo 'selected="selected"'; }?> ><?php echo Html::encode($keshi['department_name']);?></option>
                    <?php endforeach; ?>
                    <?php endif;?>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:87px;">医生职称</label>
            <div class="layui-input-block" style="width:120px;margin-left:90px;">
                <select name="title_id" lay-search="">
                    <option value="" >全部</option>
                    <?php foreach($doctor_titles as $k=>$title): ?>
                        <option value="<?php echo Html::encode($k); ?>" <?php if(trim($request->get('title_id', '')) == $k){ echo 'selected="selected"'; }?>><?php echo Html::encode($title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:90px;">开通时间</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:180px;">
                <input type="text" name="power_create_time" <?php if($request->get('power_create_time')){ echo 'value="'.Html::encode($request->get('power_create_time')).'"'; }?> class="layui-input" id="power_create_time">
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:130px;">是否关联王氏ID</label>
            <div class="layui-input-block" style="width:100px;margin-left:130px;">
                <select name="is_nisiya">
                    <option value="" >全部</option>
                    <option value="1" <?php if(trim($request->get('is_nisiya', '')) == 1){ echo 'selected="selected"'; }?>><?php echo '是'; ?></option>
                    <option value="2" <?php if(trim($request->get('is_nisiya', '')) == 2){ echo 'selected="selected"'; }?>><?php echo '否'; ?></option>
                </select>
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">是否禁用</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <select name="status">
                    <option value="" >全部</option>
                    <option value="2" <?php if(trim($request->get('status', '')) == 2){ echo 'selected="selected"'; }?>><?php echo '是'; ?></option>
                    <option value="1" <?php if(trim($request->get('status', '')) == 1){ echo 'selected="selected"'; }?>><?php echo '否'; ?></option>
                </select>
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:87px;">医院</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <input type="text"   placeholder="请输入医院名" autocomplete="off" class="layui-input" value="<?php echo isset($hos['name']) ? Html::encode($hos['name']) : '';?>">
                <select name="hospital_id" lay-search lay-filter="hos" class="hos" id="hos">
                    <option value="" >全部</option>
                    <option value="<?php echo $hos['id']??'';?>" <?php if($hos['id']??'' == $request->get('hospital_id')){ echo 'selected="selected"'; }?> ><?=isset($hos['id']) ? $hos['id'].'-'. Html::encode($hos['name']) : '';?></option>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">主医生</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <?php $doc_primary = [0=>'查看全部',1=>'只看主医生',2=>'不看主医生'];?>
                <?php echo Html::dropDownList('doc_primary',$request->get('doc_primary') ?? 0,$doc_primary,array('id'=>'doc_primary',"class"=>"docbox_input_l form-control",'style'=>'width:40px;'));?>
            </div>
        </div>

         <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">主医生ID</label>
            <div class="layui-input-block" style="width:100px;margin-left:102px;">
                <input type="text" name="primary_id" <?php if($request->get('primary_id')){echo 'value="'.Html::encode($request->get('primary_id')).'"';}?> placeholder="主医生ID" autocomplete="off" class="layui-input">
            </div>
        </div>

        <br/>
        <div class="layui-form-item layui-inline">
            <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="formDemoPane">搜索</button>
            <button type="reset" type="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
            <button class="layui-btn layui-btn-sm" style="padding: 0px;"><a style="color:white;width: 70px;display: block" href="<?php echo Url::to(['doctor/add'])?>" >新添医生</a></button>

        </div>

        <div class="layui-form-item layui-inline" style="padding-left: 50px;display: none;" id="primary_div">
            <div class="layui-input-block input-sm flex_div" style="width:200px;height: 30px;line-height: 30px;">
                <select name="primary_doclist" id="primary_doc_select"></select>
                <button type="button" class="layui-btn layui-btn-sm layui-btn-warm" id="merge_relation" data-relation="0" style="margin-left:15px;">合并关联</button>
            </div>
        </div>

        
    </form>

</div>

<hr>

<!-- <p style="color: red;text-align:left;word-break:break-all;white-space:pre-wrap;">注意事项:1.已设置排班，后关联王氏医生ID进入医生主页可立即看到排班效果。2.先关联医生后设置排班需在第二天看到排班效果。若想立即查看需重新关联一次该医生ID;</p> -->
<div class="layui-row">
    <div class="layui-col-md6">
    </div>
    <div class="layui-col-md6 layui-col-md-offset6" style="text-align:right;">
        <p class="tr" style="font-size: 15px;margin-bottom: 10px;">共<?php echo $totalCount; ?>条</p>
    </div>
</div>

<div class="layui-border-box">

    <table id="main-table" class="table table-hover" lay-even="true" style="overflow-x:scroll;">
        <thead>
        <tr>
            <th><input type="checkbox" id="checxAll"></th>
            <th>医生ID</th>
            <th style="width:7%;text-align:center;word-break:break-all;white-space:pre-wrap;">医生姓名</th>
            <th>主ID</th>
            <th>来源</th>
            <th style="width:8%;text-align:center;word-break:break-all;white-space:pre-wrap;">第三方医生</th>
            <th>职称</th>
            <th style="width:8%;text-align:center;word-break:break-all;white-space:pre-wrap;">科室</th>
            <th style="width:8%;text-align:center;word-break:break-all;white-space:pre-wrap;">出诊类型</th>
            <th>医院ID</th>
            <th style="width:8%;text-align:center;word-break:break-all;white-space:pre-wrap;">医院名称</th>
            <!-- <th>王氏医生ID</th> -->
            <th>关联王氏医生ID</th>
            <!-- <th>权重</th> -->
            <th>开通时间</th>
            <th>状态</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if(!empty($dataProvider)): ?>
            <?php foreach ($dataProvider as $value): ?>
                <tr>
                    <td><input type="checkbox" name="doctor_id[]" class="doc_checks" value="<?=Html::encode($value['doctor_id']) ?? 0;?>"></td>
                    <td>
                        <?php if($value['primary_id'] == 0):?>
                        <a style="color:blue;" target="_blank" href=" <?= \Yii::$app->params['domains']['mobile'].'hospital/doctor_'. HashUrl::getIdEncode(ArrayHelper::getValue($value,'doctor_id')).'.html'; ?> "><?php echo $value['doctor_id']; ?></a>
                        <?php else:?>
                            <?php echo $value['doctor_id']; ?>
                        <?php endif;?>
                    </td>
                    <!--<td onclick="alert('<?php /*echo HashUrl::getIdEncode($value['doctor_id']); */?>');"><?php /*echo $value['realname']; */?></td>-->
                    <td><?php echo Html::encode($value['realname'] ?? ''); ?></td>
                    <td>
                        <?php if($value['primary_id'] == 0):?>
                            <?=$value['primary_id'] ?? 0;?>
                        <?php else:?>
                            <a style="color:blue;" href="/doctor/add?doctor_id=<?=$value['primary_id'];?>"><?=$value['primary_id'];?></a>
                        <?php endif;?>
                    </td>
                    <td><?=$platform_list[$value['tp_platform']] ?? '';?></td>
                    <td><?=$value['tp_doctor_id'] ?? '';?></td>
                    <td><?php echo Html::encode($value['job_title']); ?></td>
                    <td><?php echo Html::encode($value['frist_department_name'].'-'.$value['second_department_name']);?></td>
                    <td>
                        <?php
                        $visit_type = ArrayHelper::getValue($minDoctorList, "{$value['tp_doctor_id']}.visit_type", '');
                        echo \common\models\minying\MinDoctorModel::$visitType[$visit_type] ?? '';?>
                    </td>
                    <td><?php echo $value['hospital_id'];?></td>
                    <td>
                        <a style="color:blue;" target="_blank" href=" <?= \Yii::$app->params['domains']['mobile'].'hospital/hospital_'. HashUrl::getIdEncode(ArrayHelper::getValue($value,'hospital_id')).'.html'; ?> ">
                            <?php echo html::encode($value['hospital_name'] ?? ''); ?>
                        </a>
                    </td>
                    <!-- <td>
                        <?php if(ArrayHelper::getValue($value,'miao_doctor_id')){ ?>
                            <a style="color:blue;" target="_blank" href=" <?= \Yii::$app->params['domains']['mobile'].'doctor/'. ArrayHelper::getValue($value,'miao_doctor_id').'.html'; ?> ">
                                <?php echo $value['miao_doctor_id']; ?>
                            </a>
                        <?php }else{ ?>
                            暂无关联ID
                        <?php  } ?>
                    </td> -->
                    <td class="edit" doctor_id="<?php echo $value['doctor_id'];?>" primary_id="<?=$value['primary_id'];?>">
                        <?php echo $value['miao_doctor_id'] ;?>
                    </td>
                    <td style="display: none;" id="edit_input<?php echo $value['doctor_id']; ?>" class="do_guanlian">
                        <input style="display: inline-block;width:80px;line-height: 30px;height: 30px;" type="text" value="<?php echo $value['miao_doctor_id']; ?>" class="form-control input-sm">
                        <a href="javascript:void(0);" style="display: inline-block;line-height: 30px;height: 30px;" doctor_id="<?php echo $value['doctor_id'];?>" miao_doctor_id="<?php echo $value['miao_doctor_id']; ?>" class="edit-filter layui-btn layui-btn-xs layui-btn-normal">关联</a>
                    </td>

                    <!-- <td class="weight" doctor_id="<?php echo $value['doctor_id'];?>"><?php echo $value['weight'] ;?></td> -->
                    <td style="display: none;" id="weight_input<?php echo $value['doctor_id']; ?>" class="do_weight">
                        <input style="display: inline-block;width:50px;line-height: 30px;height: 30px;"  type="text" value="<?php echo $value['weight'] ?? 0;?>" class="form-control input-sm">
                        <a href="javascript:void(0);" style="display: inline-block;line-height: 30px;height: 30px;" doctor_id="<?php echo $value['doctor_id'];?>" miao_doctor_id="<?php echo $value['weight']; ?>" class="weight-filter layui-btn layui-btn-xs layui-btn-normal">保存</a>
                    </td>
                    <td><?=date("Y-m-d H:i:s",$value['create_time']);?></td>
                    <td><?=$value['status'] == 1 ? '启用' : '禁用';?></td>
                    <td>
                        <a class="layui-btn layui-btn-xs layui-btn-normal" href="<?php echo Url::to(['doctor/detail','doctor_id'=>$value['doctor_id']])?>" title='查看'>查看</a>
                        <?php if($value['status'] == 0){?>
                            <a href="javascript:void(0);"  class="layui-btn layui-btn-xs layui-btn-danger status_open" data-id="<?=$value['doctor_id'];?>"  data-status="1">禁用中</a>
                        <?php }else{ ?>
                            <a href="javascript:void(0);" class="layui-btn layui-btn-xs status_close"  data-id="<?=$value['doctor_id'];?>" data-status="0">启用中</a>
                        <?php }?>
                        <a class="layui-btn layui-btn-xs layui-btn-normal" href="<?php echo Url::to(['doctor/add','doctor_id'=>$value['doctor_id']])?>" title='编辑'>编辑</a>
                    </td>
                </tr>
            <?php endforeach;?>
        <?php else: ?>
            <tr><td colspan="14" style="text-align: center"><div class="empty">为搜索到任何数据</div></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="page" style="text-align: center;">
    <?= GoPager::widget([
        'pagination' => $pages,
        'goFormActive' => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id . '/doc-list'], $requestParams, ['1' => 1])),
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
<!--审核失败原因-->
<div id="reason" style="display:none;"></div>
<?php
//处理导出数据
$export = Url::to(['doctor/export']);
//下载excel
$download = Url::to(['doctor/download-file']);
//获取科室信息
$skeshiUrl = Url::to(['doctor/ajax-skeshi-list']);
$hosUrl = Url::to(['doctor/ajax-hos']);

$docUpdateUrl = Url::to(['doctor/edit-mid']);
$docUpdateWeight = Url::to(['doctor/edit-weight']);
$doAuthUrl = Url::to(['doctor/change-status']);
?>
<script type="text/javascript">
        // 全选
        $("#checxAll").change(function(e){
            var flag = $(this).is(':checked');
            $("input[name='doctor_id[]']").prop("checked",flag);
            doc_option_html();
        });

        $(".doc_checks").change(function(e){
            doc_option_html();
        });

        function doc_option_html() {
            layui.use(['form'], function() {
                var form = layui.form;
                var option_html = '';
                var ids = get_check_ids();
                ids.sort(); 
                $('#primary_doc_select').html(option_html);
                if (ids.length > 0) {
                    $.each(ids, function (i, v){
                        if (i == 0) {
                            option_html += '<option selected="selected" value="'+v+'">'+v+'</option>'; 
                        }else{
                            option_html += '<option value="'+v+'">'+v+'</option>'; 
                        }
                    });
                    $('#primary_doc_select').html(option_html);
                    $('#primary_div').show();
                }else{
                     $("#primary_doc_select").html('');
                     $('#primary_div').hide();
                }
                 form.render('select');
            });
            
        }            

        function get_check_ids() {
            var ids = [];
            $(".doc_checks:checked").each(function(){
                ids.push($(this).val());
            });
            if (ids.length > 20) {
                $("#checxAll").prop("checked",false);
                $("input[name='doctor_id[]']").prop("checked",false);
                return  layer.msg('最多只能操作20个医生！', {icon: 1});
            }
            return ids;
        }

        $("#merge_relation").click(function(e){
            var relation = $(this).attr('data-relation');
            var _this = $(this);
            if (relation == 1) {
                return  layer.msg('你操作的太快了稍等一下吧！', {icon: 1});
            }
            var primary_doctor_id = $("#primary_doc_select option:selected").val();  
            if (!primary_doctor_id) {
                return  layer.msg('请选择主医生ID！', {icon: 1});
            }

            var ids = get_check_ids();
             if (!ids) {
                return  layer.msg('请选择操作医生！', {icon: 1});
            }
            layer.confirm('确定要合并关联医生吗', function(index){
                _this.attr('data-relation',1);
                //异步提交
                $.ajax({
                    url: "/doctor/merge-doctor", //提交地址
                    data: {doc_ids:ids,primary_doctor_id:primary_doctor_id,"_csrf-backend":$('#_csrf-backend').val()}, //将表单数据序列化
                    type: "post",
                    timeout: 30000, //超时时间20秒
                    dataType: "json",
                    async: true,
                    beforeSend: function() { // 禁用按钮防止重复提交
                        loading = showLoad();
                    },
                    complete: function() {
                        layer.close(loading);
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        layer.msg('操作失败，请刷新重试', {icon: 2,time: 3000});
                    },
                    success: function(res) {
                        if (res.status == 1) {
                            layer.msg('操作成功！', {icon: 1,time: 2000});
                        }else{
                            layer.msg(res.msg, {icon: 2,time: 3000});
                        }
                        setTimeout(function () {
                            window.location.href = window.location.href;
                        }, 2000);
                    }
                });
                
                layer.close(index);
            });

        });


        $(".edit").click(function(e){
            var id = $(this).attr('doctor_id');
            var t_primary_id = $(this).attr('primary_id');
            if (t_primary_id > 0) {
                return false;
            }
            $(this).hide();
            $("#edit_input"+id).show();
            //关闭其他变更
            $(this).parents("tr").siblings().find('.do_guanlian').hide();
            $(this).parents("tr").siblings().find('.edit').show();
            return false;
        });

        $(".edit-filter").click(function(e){
            var miao_doctor_id = $(this).prev().val();
            var doctor_id = $(this).attr('doctor_id');

            $.ajax({
                url:"<?=$docUpdateUrl;?>",//提交地址
                data:{'doctor_id':doctor_id,"miao_doctor_id":miao_doctor_id,"_csrf-backend":$('#_csrf-backend').val()},
                type:"GET",
                dataType:"json",
                timeout: 20000,//超时时间20秒
                async: true,
                beforeSend: function () {
                    loading = showLoad();
                },
                complete:function() {
                    layer.close(loading);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    layer.msg('操作失败，请刷新重试', {icon: 2});
                },
                success: function(res){
                    console.log(res);
                    layer.closeAll('loading'); //关闭loading
                    if (res.status==1){
                       layer.msg('操作成功！', {icon: 1});
                        setTimeout(function () {
                           window.location.reload();
                        }, 3000);
                    }else{
                        layer.msg(res.msg, {icon: 2});
                        setTimeout(function () {
                           window.location.reload();
                        }, 3000);
                    }
                },
            });

        });

         $(".weight").click(function(e){
            var id = $(this).attr('doctor_id');
            $(this).hide();
            $("#weight_input"+id).show();

            $(this).parents("tr").siblings().find('.do_weight').hide();
            $(this).parents("tr").siblings().find('.weight').show();
            return false;
        });

        $(".weight-filter").click(function(e){
            var weight = $(this).prev().val();
            var doctor_id = $(this).attr('doctor_id');
            var reg=/^[1-9]+$/gi;
            if(!reg.test(weight)){
                layer.msg('请填写数字！', {icon:2});
                return false;
            }
            $.ajax({
                url:"<?=$docUpdateWeight;?>",//提交地址
                data:{'doctor_id':doctor_id,"weight":weight,"_csrf-backend":$('#_csrf-backend').val()},
                type:"GET",
                dataType:"json",
                async: true,
                beforeSend: function () {
                    layer.load(); //上传loading
                },
                complete:function() {
                    layer.close(loading);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    layer.msg('操作失败，请刷新重试', {icon: 2});
                },
                success: function(res){
                    layer.closeAll('loading'); //关闭loading
                    layer.msg(res.msg, {
                        time: 3000, //1500ms后自动关闭
                    });
                },
            });

            //刷新当前页面
            window.location.reload();
        });
        
        $(".show_content").hover(function(){
            $(this).parent().find(".show_info").show()
        },function(){
             $(this).parent().find(".show_info").hide()
        });
    //监听导出按钮事件
    $("#export").click(function (){
        var data = $(this).parents('form').serialize();
        
        $.get("<?=$export;?>", data, function (data){
            if(data.status == 1){
                layer.open({
                     title: '下载excel',
                     content: '生成excel文件成功！',
                     btn:['下载'],
                     yes:function(index, layero){
                          window.location.href="<?=$download;?>?filename="+data.filename;
                          layer.close(index); 
                     }
                });
            }else{
                layer.msg(data.msg, {icon: 2});
            }
        });
        layer.msg('正在生成excel，请稍等！');
        return false;
    });

    //日期组件
    layui.use(['laydate', 'form', 'table'], function(){
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
        
        
        //获取URL指定参数
        function getValue(url){
			//首先获取地址
			var url = url || window.location.href;
			//获取传值
			var arr = url.split("?");
			//判断是否有传值
			if(arr.length == 1){
				return null;
			}
			//获取get传值的个数
			var value_arr = arr[1].split("&");
			//循环生成返回的对象
			var obj = {};
			for(var i = 0; i < value_arr.length; i++){
				var key_val = value_arr[i].split("=");
				obj[key_val[0]]=key_val[1];
			}
			return obj;
		}
        
        //重置表单选择
        $("#reset").click(function (){
            var fid = getValue().fid;
            var page = getValue().page;
            var str = '?';
            if(fid){
                str += 'fid='+fid+'&';
            }
            if(page){
                str += 'page='+page
            }
            window.location.href = window.location.href.split('?')[0]+str;
        });
        
        //监听所有的下拉选框
        form.on('select', function(data){
            var selecter = "option[value='" + data.value + "']";
            $(this).parents('.layui-form-select').siblings('select').find(selecter).attr("selected", "selected").siblings().removeAttr('selected');
        });
        
        $(".layui-input").on("input",function(e){
            var name = e.delegateTarget.value;
            console.log(name);
            if(name == ''){
                return false;
            }
            var hosUrl = "<?=$hosUrl;?>";
            $.get(hosUrl, {'name':name,"_csrf-backend":$('#_csrf-backend').val()}, function (data){
                if(data.code == 200){
                    var html = '<option value="">请选择医院</option>';
                    $.each(data.hos, function (i, v){
                        html += '<option value="'+v.id+'">'+v.name+'</option>'; 
                    });
                    $(".hos").html(html);
                    //重新渲染select
                    form.render('select');
                }else{
                    layer.msg('获取信息失败，请稍后重试！', {icon: 2});
                }
            });
        });
        
        //搜索框科室联动
        form.on('select(search_fkid)', function (data){
            var pid = data.value;
            if(data.value == ''){
                return false;
            }
            // var skeshiUrl = "<?=$skeshiUrl;?>";
            var skeshiUrl = "/keshi/skeshi-list";
            $.get(skeshiUrl, {'fkeshi_id':pid,"_csrf-backend":$('#_csrf-backend').val()}, function (data){
                console.log(data);
                if(data.status == 1 && data.data){
                    var html = '<option value="">二级科室</option>';
                    $.each(data.data, function (i, v){
                        html += '<option value="'+v.department_id+'">'+v.department_name+'</option>'; 
                    });
                    
                    $(".search_skid").html(html);
                    //重新渲染select
                    form.render('select');
                }else{
                    $(".search_skid").html('');
                    form.render('select');
                }
            });
        });
        
        //监听二级科室
        form.on('select(skid)', function (data){
           
        });
        });
    
    $('.updatedoc').click(function(e){
            var _this = $(this);
            var doctor_id = _this.attr('doctorId');
            var docUpdateUrl = "<?=$docUpdateUrl;?>";
             $.get(docUpdateUrl, {'doctor_id':doctor_id,'is_up':1,"_csrf-backend":$('#_csrf-backend').val()}, function (data){
                    if(data.status){
                        layer.msg('更新信息成功', {icon: 1});
                        window.location.href = window.location.href +'';
                    }
                });
        });
         $('.status_close').click(function(e){
             var _this = $(this);
              var id = _this.attr('data-id');
             layer.confirm('真的禁用么<br/>', function(index){
                var sauthUrl = "<?=$doAuthUrl;?>";
                var status = _this.attr('data-status');
                $.get(sauthUrl, {'id':id,'status':status,"_csrf-backend":$('#_csrf-backend').val()}, function (res){
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
            $('.status_open').click(function(e){
             var _this = $(this);
              var id = _this.attr('data-id');
             layer.confirm('真的启用么<br/>', function(index){
                var sauthUrl = "<?=$doAuthUrl;?>";
                var status = _this.attr('data-status');
                $.get(sauthUrl, {'id':id,'status':status,"_csrf-backend":$('#_csrf-backend').val()}, function (res){
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
</script>
