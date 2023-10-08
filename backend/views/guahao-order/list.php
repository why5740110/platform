<?php

use common\models\GuahaoCooListModel;
use yii\helpers\Url;
use common\libs\CommonFunc;
use yii\helpers\Html; 
use common\models\GuahaoOrderModel;
use common\components\GoPager;//新分页
use common\libs\HashUrl;

$this->title = '订单管理/订单列表';
$request = \Yii::$app->request;
$platform_list = CommonFunc::getTpPlatformNameList();
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
</style>

<div class="layui-row">
    <form class="layui-form" action="">
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">患者姓名</label>
            <div class="layui-input-block" style="width:100px;margin-left:110px;">
                <input type="text" name="patient_name" <?php if($request->get('patient_name')){echo 'value="'.Html::encode($request->get('patient_name')).'"';}?> placeholder="患者姓名" autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">医生姓名</label>
            <div class="layui-input-block" style="width:100px;margin-left:110px;">
                <input type="text" name="doctor_name" <?php if($request->get('doctor_name')){echo 'value="'.Html::encode($request->get('doctor_name')).'"';}?> placeholder="医生姓名" autocomplete="off" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">医院名称</label>
            <div class="layui-input-block" style="width:180px;margin-left:110px;">
                <input type="text" name="hospital" <?php if($request->get('hospital')){echo 'value="'.Html::encode($request->get('hospital')).'"';}?> placeholder="医院名称" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">预约状态</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <select name="state">
                    <option value="" >全部</option>
                    <option value="5" <?php if(trim($request->get('state', '')) == 5){ echo 'selected="selected"'; }?>><?php echo '下单成功'; ?></option>
                    <option value="1" <?php if(trim($request->get('state', '')) == 1){ echo 'selected="selected"'; }?>><?php echo '取消'; ?></option>
                    <option value="2" <?php if(trim($request->get('state', '')) == 2){ echo 'selected="selected"'; }?>><?php echo '停诊'; ?></option>
                    <option value="3" <?php if(trim($request->get('state', '')) == 3){ echo 'selected="selected"'; }?>><?php echo '已取号'; ?></option>
                    <option value="4" <?php if(trim($request->get('state', '')) == 4){ echo 'selected="selected"'; }?>><?php echo '爽约'; ?></option>
                    <option value="6" <?php if(trim($request->get('state', '')) == 6){ echo 'selected="selected"'; }?>><?php echo '无效'; ?></option>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">对接平台</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <?php $tp_platform_list = ['0'=>'全部'] + CommonFunc::getTpPlatformNameList(1);?>
                <?php echo Html::dropDownList('tp_platform',$request->get('tp_platform') ?? '',$tp_platform_list,array('id'=>'tp_platform_list',"class"=>"form-control input-sm"));?>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">渠道入口</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <?php $tp_device_source = array_merge(['0'=>'全部'],GuahaoOrderModel::$device_source);?>
                <?php echo Html::dropDownList('device_source',$request->get('device_source') ?? '',$tp_device_source,array('id'=>'tp_device_source',"class"=>"form-control input-sm"));?>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">手机号</label>
            <div class="layui-input-block" style="width:130px;margin-left:110px;">
                <input type="text" name="mobile" <?php if($request->get('mobile')){echo 'value="'.Html::encode($request->get('mobile')).'"';}?> placeholder="手机号" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">订单流水号</label>
            <div class="layui-input-block" style="width:150px;margin-left:110px;">
                <input type="text" name="order_sn" <?php if($request->get('order_sn')){echo 'value="'.Html::encode($request->get('order_sn')).'"';}?> placeholder="订单流水号" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">第三方流水号</label>
            <div class="layui-input-block" style="width:150px;margin-left:110px;">
                <input type="text" name="tp_order_id" <?php if($request->get('tp_order_id')){echo 'value="'.Html::encode($request->get('tp_order_id')).'"';}?> placeholder="第三方流水号" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">来源方订单号</label>
            <div class="layui-input-block" style="width:150px;margin-left:110px;">
                <input type="text" name="coo_order_id" <?php if($request->get('coo_order_id')){echo 'value="'.Html::encode($request->get('coo_order_id')).'"';}?> placeholder="来源方订单号" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:90px;">创建时间</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:180px;">
                <input type="text" name="create_time" <?php if($request->get('create_time')){ echo 'value="'.Html::encode($request->get('create_time')).'"'; }?> class="layui-input" id="create_time">
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">就诊科室</label>
            <div class="layui-input-block" style="width:130px;margin-left:110px;">
                <input type="text" name="department_name" <?php if($request->get('department_name')){echo 'value="'.Html::encode($request->get('department_name')).'"';}?> placeholder="就诊科室" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:90px;">就诊时间</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:180px;">
                <input type="text" name="visit_time" <?php if($request->get('visit_time')){ echo 'value="'.Html::encode($request->get('visit_time')).'"'; }?> class="layui-input" id="visit_time">
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:90px;">就诊疾病</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:180px;">
                <input type="text" name="symptom" <?php if($request->get('symptom')){ echo 'value="'.Html::encode($request->get('symptom')).'"'; }?> placeholder="就诊疾病" autocomplete="off" class="layui-input" id="symptom">
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">合作平台</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <?php $tp_coo_platform_list = ['0'=>'全部'] + GuahaoCooListModel::getCooPlatformList();?>
                <?php echo Html::dropDownList('tp_coo_platform',$request->get('tp_coo_platform') ?? '',$tp_coo_platform_list,array('id'=>'tp_coo_platform_list',"class"=>"form-control input-sm"));?>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">是否复诊</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <?php $famark_type_list = ['0'=>'全部'] + CommonFunc::$famark_type_list;?>
                <?php echo Html::dropDownList('famark_type',$request->get('famark_type') ?? '',$famark_type_list,array('id'=>'famark_type',"class"=>"form-control input-sm"));?>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">性别</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <?php $gender_list = ['0'=>'全部'] + CommonFunc::$gender_list;?>
                <?php echo Html::dropDownList('gender',$request->get('gender') ?? '',$gender_list,array('id'=>'gender_list',"class"=>"form-control input-sm"));?>
            </div>
        </div>

        <br/>
        <div class="layui-form-item layui-inline">
            <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="formDemoPane">搜索</button>
            <button type="reset" id="reset9" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
            <button type="button" id="do_export" class="layui-btn layui-btn-sm layui-btn-normal">导出无效订单</button>
            <button type="button" id="do_export_uid" class="layui-btn layui-btn-sm layui-btn-normal">导出用户UID</button>
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
            <th>序号</th>
            <th>对接平台</th>
            <th>合作平台</th>
            <th>渠道入口</th>
            <th>订单流水号</th>
            <th>第三方流水号</th>
            <th>来源方流水号</th>
            <th>患者姓名</th>
            <th>医生姓名</th>
            <th>就诊科室</th>
            <th>医院名称</th>
            <th>医事服务费</th>
            <th>初诊/复诊</th>
            <th>就诊时间</th>
            <th>订单提交时间</th>
            <th>订单状态</th>
            <th>支付状态</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if(!empty($dataProvider)): ?>
            <?php foreach ($dataProvider as $value): ?>
                <tr>
                    <td><?php echo $value['id']; ?></td>
                    <!--<td><?php /*echo $value['tp_platform']; */?></td>-->
                    <td><?=$platform_list[$value['tp_platform']] ?? '';?></td>
                    <td><?php echo $value['coo_platform']; ?></td>
                    <td><?php echo GuahaoOrderModel::$device_source[$value['device_source']] ?? 'H5'; ?></td>
                    <td><?php echo Html::encode($value['order_sn']); ?></td>
                    <td><?php echo Html::encode($value['tp_order_id']); ?></td>
                    <td><?php echo Html::encode($value['coo_order_id']); ?></td>
                    <td><?php echo Html::encode($value['patient_name']); ?></td>
                    <td>
                        <?php if($value['is_disable'] == 1):?>
                            <a style="color:blue;" target="_blank" href=" <?= \Yii::$app->params['domains']['mobile'].'hospital/doctor_'. HashUrl::getIdEncode($value['doctorId']).'.html'; ?> "><?php echo Html::encode($value['doctor_name']); ?></a>
                        <?php else:?>
                            <?php echo Html::encode($value['doctor_name']); ?>
                        <?php endif;?>
                    </td>
                    <td><?php echo Html::encode($value['department_name']); ?></td>
                    <td><?php echo Html::encode($value['hospital_name']); ?></td>
                    <td><?php echo $value['visit_cost']; ?></td>
                    <td><?php echo $value['famark_type']==1?'初诊':'复诊'; ?></td>
                    <td><?php echo $value['visit_time']; ?></td>
                    <td><?php echo $value['create_time']; ?></td>
                    <!--<td><?php /*echo $value['state']; */?></td>-->
                    <td><?php echo $value['state_desc']; ?></td>
                    <td><?php echo $value['pay_status']; ?></td>
                    <td>
                        <a href="<?php echo Url::to(['guahao-order/info','id'=>$value['id']])?>" title='详情'>
                            <button type="button" class="layui-btn layui-btn-xs layui-btn-normal">
                                详情
                            </button>
                        </a>
                        <?php if ($value['state'] == 6 && $value['invalid_status'] == 1) { ?>
                            <a href="javascript:void(0);"
                               class="layui-btn layui-btn-xs layui-btn invalid-remarks"
                               invalid_type="<?php echo $value['invalid_type']; ?>"
                               invalid_reason="<?php echo $value['invalid_reason']; ?>" style="margin-top: 2px;">原因</a>
                        <?php } ?>
                    </td>

                </tr>
            <?php endforeach;?>
        <?php else: ?>
            <tr><td colspan="18" style="text-align: center"><div class="empty">为搜索到任何数据</div></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="page" style="text-align: center;">
    <?= GoPager::widget([
        'pagination' => $pages,
        'goFormActive' => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id . '/order-list'], $requestParams, ['1' => 1])),
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
<script>

    $("#do_export").click(function (event) {
        if (confirm('确定要导出吗?') ? true : false) {
            var data = $('form').serialize();               
            window.open('/guahao-order/down?'+data,"_blank");
        }
    });

    //导出用户中心UID
    $("#do_export_uid").click(function (event) {
        if (confirm('确定要导出吗?') ? true : false) {
            var data = $('form').serialize();
            window.open('/guahao-order/down-uid?'+data,"_blank");
        }
    });


    layui.use(['laydate', 'form', 'table'], function(){
        var laydate = layui.laydate;
        var form = layui.form;
        //开通时间
        laydate.render({
            elem: '#create_time', //指定元素
            range: true
        });

        });
    layui.use(['laydate', 'form', 'table'], function(){
        var laydate = layui.laydate;
        var form = layui.form;
        //开通时间
        laydate.render({
            elem: '#visit_time', //指定元素
            range: true
        });

    });
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

    //无效原因
    $('.invalid-remarks').click(function (e) {
        var invalid_type = $(this).attr('invalid_type');
        var invalid_reason = $(this).attr('invalid_reason');
        var _this = $(this);
        confi_title = '';
        confi_content = '<div id="invalid" class="layui-form-item layui-inline" style="margin-top: 5rem; margin-right: 5rem;"><div class="layui-input-block" style="width: 380px;margin-left:0px;padding-left:30px;text-align: center;">' + invalid_type + '</div><div class="layui-input-block" style="width: 380px;margin-left:0px;padding-left:30px;text-align: center;">' + invalid_reason + '</div></div>';

        layer.open({
            id: "invalid",
            type: 1,
            title: confi_title,
            area: ['450px', '220px'],
            content: confi_content,
            btn: "我知道了",
            btnAlign: 'c',
            yes: function (index, layero) {
                layer.close(index);
            },
            end: function () {
                $('#layui-layer-shade2').hide();
            }
        });
    })
</script>

