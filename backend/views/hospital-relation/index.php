<?php

use common\components\GoPager;
use common\libs\CommonFunc;
use common\models\GuahaoHospitalModel;
use yii\helpers\Html; //新分页
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\libs\HashUrl;
use dosamigos\datetimepicker\DateTimePicker;

$platform_list = CommonFunc::getTpPlatformNameList();
$request     = \Yii::$app->request;
$this->title = '关联医院';
?>
<style>
    .layui-table tbody tr:hover{background: none;}
    .layui-form-label {width:100px;font-size:14px;}
    .layui-input-block {margin-left:160px;}
    .layui-textarea{min-height:60px;}
    .layui-layer-shade{display:none;}
    .check_faild_reason_css{
        overflow:hidden; word-wrap:break-word;
    }
    .hos-upload {
        padding: 4px 10px;
        height: 30px;
        line-height: 20px;
        position: relative;
        cursor: pointer;
        #color: #888;
        #background: #fafafa;
        #border: 1px solid #ddd;
        #border-radius: 4px;
        overflow: hidden;
        text-decoration: none;
        display: inline-block;
        *display: inline;
        *zoom: 1
    }
    .hos-upload  input {
        position: absolute;
        font-size: 100px;
        right: 0;
        top: 0;
        opacity: 0;
        filter: alpha(opacity=0);
        cursor: pointer
    }
</style>
<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<!--<div class="layui-layer-shade" id="layui-layer-shade2" times="2" style="z-index: 19891015; background-color: rgb(0, 0, 0); opacity: 0.3;"></div>-->
<div class="layui-row">
    <form class="layui-form" action="">

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">医院来源</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <?php $tp_platform_list = ['0' => '全部'] + CommonFunc::getTpPlatformNameList(1);?>
                <?php echo Html::dropDownList('tp_platform', $request->get('tp_platform') ?? '', $tp_platform_list, array('id' => 'tp_platform_list', "class" => "form-control input-sm")); ?>
            </div>
        </div>


        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:60px;">状态</label>
            <div class="layui-input-block" style="width:100px;margin-left:60px;">
                <?php $tp_platform_list = array_merge(['' => '全部'], GuahaoHospitalModel::$status_list);?>
                <?php echo Html::dropDownList('status', $request->get('status') ?? '', $tp_platform_list, array('id' => 'tp_platform_list', "class" => "form-control input-sm")); ?>

            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:140px;">第三方平台医院</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <input type="text" name="hospital_name"  placeholder="请输入医院名称" autocomplete="off" class="layui-input" value="<?php echo Html::encode($request->get('hospital_name', '')); ?>">

            </div>
        </div>
        <br/>
        <div class="layui-form-item layui-inline">
            <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="formDemoPane">搜索</button>
            <button type="reset" id="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
            <button type="button" id="add-hosp" class="layui-btn layui-btn-sm layui-btn-primary">添加160医院</button>
            <button type="button" id="do_export" class="layui-btn layui-btn-sm">导出未关联医院</button>
            <a href="javascript:;" class="hos-upload layui-btn layui-btn-sm">
                <input type="file" name="" id="file">导入关联医院
            </a>
        </div>
    </form>
</div>

<hr>

<div class="layui-row">
    <div class="layui-col-md6">
    </div>
    <div class="layui-col-md6 layui-col-md-offset6" style="text-align:right;">
        <p class="tr" style="font-size: 15px;margin-bottom: 10px;">共<?php echo $totalCount; ?>条</p>
    </div>
</div>

<div class="layui-form layui-border-box">

    <table id="main-table" class="table table-hover" lay-even="true" style="overflow-x:scroll;">
        <thead>
        <tr>
            <th >ID</th>
            <th >第三方医院ID</th>
            <th >医院名称</th>
            <th >地区</th>
            <th >医院等级</th>
            <th >医院来源</th>
            <th >王氏医院ID</th>
            <th >关联的王氏医院</th>
            <th >状态</th>
            <th style="width:10%;text-align:center;word-break:break-all;white-space:pre-wrap;">备注</th>
            <th style="width:24%;">操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($dataProvider)): ?>
            <?php foreach ($dataProvider as $value): ?>
                <tr>
                    <td ><?php echo $value['id']; ?></td>
                    <td ><?php echo $value['tp_hospital_code']; ?></td>
                    <td ><?php echo Html::encode($value['hospital_name']); ?></td>
                    <td ><?php echo $value['province']; ?></td>
                    <td ><?php echo $value['tp_hospital_level']; ?></td>
                    <td >
                        <?php /*echo $value['tp_platform_name']; */?>
                        <?=$platform_list[$value['tp_platform']] ?? '';?>
                    </td>
                    <td ><?php echo $value['hospital_id']; ?></td>
                    <td >
                        <a target="_blank" href=" <?= \Yii::$app->params['domains']['mobile'].'hospital/hospital_'. HashUrl::getIdEncode(ArrayHelper::getValue($value,'hospital_id')).'.html'; ?> ">
                            <?php echo Html::encode($value['re_hospital_name']); ?>
                        </a>
                    </td>
                    <td><?=GuahaoHospitalModel::$status_list[$value['status']] ?? '';?></td>
                    <td><?php echo Html::encode($value['remarks']); ?></td>
                    <td>
                        <a class="layui-btn layui-btn-xs" href="<?php echo Url::to(['hospital-relation/detail','id'=>$value['id']])?>" title='查看'>查看</a>
                        <?php if ($value['tp_platform'] == 13) {?>
                            <a class="layui-btn layui-btn-xs layui-btn-normal" data-toggle="modal" onclick='setTime("<?php echo $value['tp_hospital_code']; ?>","<?=Html::encode($value['hospital_name']); ?>", "<?= $value['deadline_start']?>", "<?= $value['deadline_end']?>")' data-target="#myModal" t_id="<?php echo $value['id']; ?>">设置</a>
                        <?php } ?>
                        <?php if ($value['status'] == 0) {?>
                            <a href="javascript:void(0);" class="layui-btn layui-btn-xs relation" tp_platform="<?php echo $value['tp_platform']; ?>" tp_hospital_code="<?php echo $value['tp_hospital_code']; ?>" t_id="<?php echo $value['id']; ?>">关联医院</a>
                        <?php } elseif ($value['status'] == 2) {?>
                            <a href="javascript:void(0);" class="layui-btn layui-btn-xs layui-btn-warm relation_disabled" t_id="<?php echo $value['id']; ?>" dis_type="1" >启用</a>
                        <?php } elseif ($value['status'] == 1) {?>
                            <a href="javascript:void(0);" class="layui-btn layui-btn-xs layui-btn-normal">
                                已关联
                            </a>
                        <?php }?>
                         <?php if (intval($value['status']) !== 2) {?>
                             <a href="javascript:void(0);" class="layui-btn layui-btn-xs layui-btn-danger relation_disabled" t_id="<?php echo $value['id']; ?>" dis_type="0" dis_remarks="<?=Html::encode(htmlentities($value['remarks']));?>">禁用</a>
                         <?php }?>
                        <a href="javascript:void(0);" class="layui-btn layui-btn-xs layui-btn hospital_remarks"  remark_txt="<?php echo Html::encode(htmlentities($value['remarks'])); ?>"t_id="<?php echo $value['id']; ?>">备注</a>
                    </td>
                </tr>
            <?php endforeach;?>
        <?php else: ?>
            <tr><td colspan="14" style="text-align: center"><div class="empty">为搜索到任何数据</div></td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>
<div id="page" style="text-align: center;">
    <?= GoPager::widget([
        'pagination'      => $pages,
        'goFormActive'    => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id . '/list'], $requestParams, ['1' => 1])),
        'firstPageLabel'  => '首页',
        'prevPageLabel'   => '《',
        'nextPageLabel'   => '》',
        'lastPageLabel'   => '尾页',
        'goPageLabel'     => true,
        'totalPageLable'  => '共x页',
        'totalCountLable' => '共x条',
        'goButtonLable'   => 'GO',
    ]); ?>
</div>


<div class="modal fade" id="myModal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">设置合作时间</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="modal-form">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">医院名称</label>
                            <div class="col-sm-10">
                                <input class="form-control" type="text" id="hospital_name" autocomplete="off" readonly value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="weight" class="col-sm-2 control-label">开始时间</label>
                            <div class="col-sm-10">
                                <?php
                                echo DateTimePicker::widget([
                                    'language' => 'zh-CN',
                                    'id' => 'begin_time',
                                    'name' => 'begin_time',
                                    'value' => '',
                                    'clientOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                        'todayBtn' => true,
                                        'minView' => 2,
                                        'startView' => 2
                                    ],
                                ]);
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="weight" class="col-sm-2 control-label">结束时间</label>
                            <div class="col-sm-10">
                                    <?php
                                    echo DateTimePicker::widget([
                                        'language' => 'zh-CN',
                                        'id' => 'end_time',
                                        'name' => 'end_time',
                                        'value' => '',
                                        'clientOptions' => [
                                            'autoclose' => true,
                                            'format' => 'yyyy-mm-dd',
                                            'todayBtn' => true,
                                            'minView' => 2,
                                            'startView' => 2
                                        ],
                                    ]);
                                    ?>
                            </div>
                        </div>
                    </div>
            </div>
            <input class="form-control" style="display: none" type="text" id="tp_hospital_code" autocomplete="off" value="">
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">取消</button>
                <button type="button" style="margin-right: 8px;" id="set_time" class="btn btn-primary">添加</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!--审核失败原因-->
<div id="reason" style="display:none;"></div>
<?php
$updateDocidUrl      = Url::to(['hospital-relation/update-relation']);
$relationDocidUrl    = Url::to(['hospital-relation/relation']);
$relationDisabledUrl = Url::to(['hospital-relation/disabled-relation']);

$add160Url = Url::to(['hospital-relation/add160']);
$updateRemarksUrl = Url::to(['hospital-relation/update-remarks']);

$setTime = Url::to(['hospital-relation/set-time']);

?>
<script type="text/javascript">
function setTime(tp_hospital_code,hospital_name,deadline_start,deadline_end){
    $('#tp_hospital_code').val(tp_hospital_code);
    $('#hospital_name').val(hospital_name);
    $('#begin_time').val(deadline_start);
    $('#end_time').val(deadline_end);
}
$("#set_time").click(function (){
    var setTime = "<?=$setTime?>";
    var begin_time = $('#begin_time').val();
    var end_time = $('#end_time').val();
    var tp_hospital_code = $('#tp_hospital_code').val();
    if (!begin_time || !end_time){
        layer.msg('开始时间、结束时间不能为空！', {icon: 2});
        return false;
    }
    $.get(setTime, {'begin_time':begin_time,'end_time':end_time,'tp_hospital_code':tp_hospital_code,"_csrf-backend":$('#_csrf-backend').val()}, function (data){
        if(data.status == 1){
            layer.msg(data.msg, {icon: 1});
            setTimeout(function (){
                window.location.reload();
            }, 2000);
        }else{
            layer.msg(data.msg, {icon: 2});
            return false;
        }
    });
});
//导入excel
$(".hos-upload").on("change","#file",function(){
    importExcel(this);
 });

//读取导入excel内容
function importExcel(obj) {
    var hospitalKey = ['序号ID', '第三方医院ID', '医院名称', '来源', '王氏医院ID'];
    var wb;//读取完成的数据
    var rABS = false; //是否将文件读取为二进制字符串
    if(obj.files.length==0) {
         return;
    }
    const IMPORTFILE_MAXSIZE = 10*1024;//这里可以自定义控制导入文件大小
    var suffix = obj.files[0].name.split(".")[1]
    $('#_file_path').val(obj.files[0].name);
    if(suffix != 'xls' && suffix !='xlsx'){
        layer.msg("请上传xls|xlsx文件", {icon: 2});
        return;
    }
    if(obj.files[0].size/1024 > IMPORTFILE_MAXSIZE){
        layer.msg("文件不超过10MB", {icon: 2});
        return;
    }
    var f = obj.files[0];
    var reader = new FileReader();
    reader.onload = function(e) {
        data = e.target.result;
        if(rABS) {
            wb = XLSX.read(btoa(fixdata(data)), {//手动转化
                type: 'base64'
            });
        } else {
            wb = XLSX.read(data, {
                type: 'binary'
            });
        }
        //wb.SheetNames[0]是获取Sheets中第一个Sheet的名字
        //wb.Sheets[Sheet名]获取第一个Sheet的数据
        var a=wb.SheetNames[0];
        var b=wb.Sheets[a];//内容为方式2
        data=XLSX.utils.sheet_to_json(b, {defval: null});//内容为方式1
        if(!data||data==""){
            layer.closeAll('loading');
            layer.msg("文件中没有内容,请重新选择文件!", {icon: 2});
            return;
        }

        var headers_key = new Array();
        //data后面的数字代表表头是从第几行开始的，自己根据情况修改
        for (var key in data[0]) {
            headers_key.push(key); //获取表头key
        }
        if (headers_key.length > hospitalKey.length) {
            layer.msg("上传文件表头不符合,请先导出未关联医院excel文件进行参考！", {icon: 2});
            return;
        }

        var arr = getArrDifference(hospitalKey, headers_key);
        if (arr.length > 0) {
            layer.closeAll('loading');
            layer.msg("上传文件表头不符合,请先导出未关联医院excel文件进行参考！", {icon: 2});
            return;
        }

        var jsonData = new FormData();
        jsonData.append('excel_data', JSON.stringify(data));　　　
        jsonData.append('_csrf-backend', $('#_csrf-backend').val());　　　
        //在这里执行对解析后数据的处理
        $.ajax({
            type: 'POST',
            url: "import",
            data: jsonData,
            cache: false,
            processData: false,
            contentType: false,
            success: function (res) {
                if(res.status == 1) {
                    layer.msg(res.msg, {icon: 1}, function () {
                        window.location.reload();
                    });
                } else {
                    layer.msg(res.msg, {icon: 2});
                }
            }
        });
    };
    if(rABS) {
        reader.readAsArrayBuffer(f);
    } else {
        reader.readAsBinaryString(f);
    }
    function fixdata(data) { //文件流转BinaryString
        var o = "",
            l = 0,
            w = 10240;
        for(; l < data.byteLength / w; ++l) o += String.fromCharCode.apply(null, new Uint8Array(data.slice(l * w, l * w + w)));
        o += String.fromCharCode.apply(null, new Uint8Array(data.slice(l * w)));
        return o;
    }
}

//验证两个数组不同的内容
function getArrDifference(arr1, arr2) {
    return arr1.concat(arr2).filter(function(v, i, arr) {
        return arr.indexOf(v) === arr.lastIndexOf(v);
    });
}

//导出未关联医院数据
$("#do_export").click(function (event) {
var tp_platform = $('#tp_platform_list option:selected').val();
if (confirm('确定要导出吗?') ? true : false) {
    window.open('/hospital-relation/export?tp_platform='+tp_platform,"_blank");
}
});

$(".relation_disabled").click(function (e){
    $(this).attr('disabled', 'disabled');
    var _this = $(this);
    var t_id = $(this).attr('t_id');
    var dis_type = $(this).attr('dis_type');
    var dis_remarks = $(this).attr('dis_remarks');
    var confi_text = '';
    var confi_title = '';
    var confi_content = '确定要';
    if (dis_type == 0) {
        confi_text = '您确定禁用吗';
        confi_title = '禁用关联医院';
        confi_content = '<div class="layui-form-item layui-inline"><label class="layui-form-label">备注<span style="color: red">*</span></label><div class="layui-input-block" ><input type="text" name="hospital_remarks" value="'+dis_remarks+'" required="required" id="hospital_remarks" placeholder="请输入备注信息" autocomplete="off" maxlength="50" class="layui-input"></div></div>';
    }else{
        confi_text = '您确定启用吗';
        confi_title = '启用关联医院';
         confi_content = '<div class="layui-form-item layui-inline"><h3>您确定启用吗</h3></div>';
    }
     var relationDisabledUrl = "<?=$relationDisabledUrl;?>";
    

        layer.open({
            type:1,
            title:confi_title,
            area:['500px', '320px'],
            content: confi_content,
            btn: ['确定', '取消'],
            yes: function(index, layero){
                if (dis_type == 0) {
                    var remarks = $('#hospital_remarks').val().trim().replace('/\s/g','');
                    if(!remarks){
                       return layer.msg('请填写备注信息', {icon: 2});
                    }
                    if (remarks.length >50) {
                        return layer.msg('备注信息不能大于50个字', {icon: 2});
                    }
                }else{
                    var remarks = '';
                }
                $.post(relationDisabledUrl, {'t_id':t_id,'dis_type':dis_type,'remarks':remarks,"_csrf-backend":$('#_csrf-backend').val()}, function (res){
                    if(res.status == 1){
                        layer.msg('操作成功！', {icon: 1});
                        setTimeout(function () {
                            window.location.href = window.location.href;
                        }, 1000);
                    }else{
                        layer.msg(res.msg, {icon: 2});
                        // setTimeout(function () {
                        //     window.location.href = window.location.href;
                        // }, 3000);
                    }
                });

            },
            btn2: function (index, layero){
                 _this.removeAttr('disabled');
                layer.close(index);
            },
            end: function (){
                _this.removeAttr('disabled');
                $('#layui-layer-shade2').hide();
            }
        });
    // });
});
$(".relation").click(function (e){
     $(this).attr('disabled', 'disabled');
     var tp_hospital_code = $(this).attr('tp_hospital_code');
     var tp_platform = $(this).attr('tp_platform');
     var t_id = $(this).attr('t_id');
     var updateDocidUrl = "<?=$updateDocidUrl;?>";
     var relationDocidUrl = "<?=$relationDocidUrl;?>";

     $('#layui-layer-shade2').show();
    layer.open({
        type:2,
        title:'关联医院',
        area:['500px', '320px'],
        content: relationDocidUrl+"?tp_hospital_code="+tp_hospital_code+'&t_id='+t_id,
        btn: ['关联', '取消'],
        yes: function(index, layero){
          var doctorObj = layer.getChildFrame("#hospital_id", index);

            var hospital_id = doctorObj.val();
            var hosid = layer.getChildFrame("#hosid", index);
            if(!hosid.text()){
                layer.msg('请先查询医院信息', {icon: 2});
                $('#layui-layer-shade2').show();
                return false;
            }
            $.post(updateDocidUrl, {tp_hospital_code:tp_hospital_code,t_id:t_id,hospital_id:hosid.text(),tp_platform:tp_platform,"_csrf-backend":$('#_csrf-backend').val()}, function (data){
                if(data.status == 1){
                    layer.close(index);
                    layer.msg(data.msg, {icon: 1});
                    window.location.reload();
                }else{
                    layer.msg(data.msg, {icon: 2});
                    window.location.reload();
                    return false;
                }
            });

        },
        btn2: function (index, layero){
            layer.close(index);

        },

         end: function (){
            $(".relation").attr('disabled',false);
            $('#layui-layer-shade2').hide();
        }
    });

});

$("#add-hosp").click(function (){

    var add160 = "<?=$add160Url?>";

    layer.open({
        type:2,
        title:'添加160医院',
        area:['700px', '320px'],
        content: add160,
        btn: ['添加', '取消'],
        yes: function(index, layero){

            var code = layer.getChildFrame("#code", index).val();

            $.get(add160, {'code':code,'add':1,"_csrf-backend":$('#_csrf-backend').val()}, function (data){
                if(data.code == 1){
                    layer.close(index);
                    layer.msg(data.msg, {icon: 1});
                    window.location.reload();
                }else{
                    layer.msg(data.msg, {icon: 2});
                    return false;
                }
            });

        },
        btn2: function (index, layero){
            layer.close(index);
        }
    });
})
$('.hospital_remarks').click(function (e) {
    var hosp_id = $(this).attr('t_id');
    var remark_txt = $(this).attr('remark_txt');

    var _this = $(this);
    confi_title = '备注';
    confi_content = '<div class="layui-form-item layui-inline" style="margin-top: 5rem"><label class="layui-form-label">备注</label><div class="layui-input-block" ><input style="width:30rem"  type="text" name="update_hospital_remarks" value="'+remark_txt+'" id="update_hospital_remarks" placeholder="请输入备注信息(50字以内)" autocomplete="off" maxlength="50" class="layui-input"></div></div>';

    var openStartStopUrl = "<?=$updateRemarksUrl;?>";
    idStr = hosp_id.toString();
    layer.open({
        type: 1,
        title: confi_title,
        area: ['500px', '320px'],
        content: confi_content,
        btn: ['确定', '取消'],
        yes: function (index, layero) {
            var hospital_remarks = $('#update_hospital_remarks').val();
            if (hospital_remarks.length > 50) {
                return layer.msg('备注信息不能大于50个字', {icon: 2});
            }

            $.post(openStartStopUrl, {
                'id': hosp_id,
                'remarks': hospital_remarks,
                "_csrf-backend":$('#_csrf-backend').val()
                }, function (res) {
                if (res.status == 1) {
                    layer.msg(res.msg, {icon: 1, time: 5000});
                    setTimeout(function () {
                        parent.location.reload();
                    }, 2000);
                } else {
                    layer.msg(res.msg, {icon: 2});
                    setTimeout(function () {
                        parent.location.reload();
                    }, 2000);
                }
            });
        },
    });
})

</script>






