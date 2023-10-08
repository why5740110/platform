<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\LinkPager;
use common\libs\Url as CommonUrl;
use common\libs\CommonFunc;
use \yii\helpers\ArrayHelper;
use common\components\GoPager;
use common\models\GuahaoPlatformRelationHospitalModel;
use common\models\GuahaoPlatformListModel;

//新分页
use common\libs\HashUrl;
use common\libs\DoctorUrl;

$request = \Yii::$app->request;
$platform_list = CommonFunc::getTpPlatformNameList(1);
$this->title = Html::encode($this->title);
?>

<style>
    .layui-table tbody tr:hover {
        background: none;
    }

    .layui-form-label {
        width: 100px;
        font-size: 14px;
    }

    .layui-input-block {
        margin-left: 160px;
    }

    .layui-textarea {
        min-height: 60px;
    }

    .layui-layer-shade {
        display: none;
    }

    /*.layui-form-checkbox{display:none;}*/
    .check_faild_reason_css {
        overflow: hidden;
        word-wrap: break-word;
    }

    #doctor_type_box, .flex_div {
        display: flex;
        display: -webkit-flex;
    }

</style>

<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="layui-row">
    <form class="layui-form" action="">
        <input type="hidden" name="coo_id" value="<?php echo $coo_id; ?>">
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">医院来源</label>
            <div class="layui-input-block" style="width:150px;margin-left:100px;">
                <?php $tp_platform_list = ['' => '全部'] + GuahaoPlatformListModel::getOpenCooTpPlatformIdListByCooId($coo_id,1); ?>
                <?php echo Html::dropDownList('tp_platform', $request->get('tp_platform') ?? '', $tp_platform_list, array('id' => 'tp_platform_list', "class" => "form-control input-sm")); ?>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:140px;">开放状态</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:100px;">
                <?php $tp_platform_list = array_merge(['0' => '全部'], GuahaoPlatformRelationHospitalModel::$view_status_list); ?>
                <?php echo Html::dropDownList('status', $request->get('status') ?? '', $tp_platform_list, array('id' => 'tp_platform_list', "class" => "form-control input-sm")); ?>
            </div>
        </div>
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:140px;">第三方平台医院</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <input type="text" name="hospital_name" placeholder="请输入医院名称" autocomplete="off" class="layui-input"
                       value="<?php echo Html::encode($request->get('hospital_name', '')); ?>">

            </div>
        </div>
        <br/>
        <div class="layui-form-item layui-inline">
            <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="formDemoPane">搜索</button>
            <button type="reset" id="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
            <button type="button" id="start-open" class="layui-btn layui-btn-sm layui-btn-primary">开始开放</button>
            <button type="button" id="stop-open" class="layui-btn layui-btn-sm layui-btn-primary">停止开放</button>
        </div>
    </form>

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
                <th>ID</th>
                <th>医院名称</th>
                <th>地区</th>
                <th>医院等级</th>
                <th>医院来源</th>
                <th>开放状态</th>
                <th style="width:13%;text-align:center;word-break:break-all;white-space:pre-wrap;">备注</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($dataProvider)): ?>
                <?php foreach ($dataProvider as $value): ?>
                    <tr>
                        <td><input statu="<?php echo $value['rel_hosp_status']; ?>" type="checkbox" name="hosp_id[]"
                                   class="hosp_checks"
                                   value="<?= $value['tp_hospital_code'] . '||||' . $value['tp_platform'] . '__' . $value['rel_hosp_status']; ?>">
                        </td>
                        <td><?php echo $value['hosp_id']; ?></td>
                        <td><?php echo Html::encode($value['hospital_name']); ?></td>
                        <td><?php echo Html::encode($value['province']); ?></td>
                        <td><?php echo $value['tp_hospital_level']; ?></td>
                        <!--<td><?php /*echo $value['tp_platform_name']; */?></td>-->
                        <td><?=$platform_list[$value['tp_platform']] ?? '';?></td>
                        <td><?php echo $value['status_title']; ?></td>
                        <td><?php echo Html::encode($value['rel_hosp_remarks']);?></td>
                        <td>
                            <?php if ($value['rel_hosp_status'] == 1) { ?>
                                <a href="javascript:void(0);"
                                   class="layui-btn layui-btn-xs layui-btn-danger one-start-stop-open"
                                   open_start_id="<?php echo $value['tp_hospital_code'] . '||||' . $value['tp_platform']; ?>"
                                   data_type="2"
                                   stop_remarks="<?= Html::encode(htmlentities($value['rel_hosp_remarks'])); ?>">停止开放</a>
                            <?php } else { ?>
                                <a href="javascript:void(0);"
                                   class="layui-btn layui-btn-xs layui-btn one-start-stop-open"
                                   open_start_id="<?php echo $value['tp_hospital_code'] . '||||' . $value['tp_platform']; ?>"
                                   data_type="1">开始开放</a>
                            <?php } ?>
                                <a href="javascript:void(0);"
                                   class="layui-btn layui-btn-xs layui-btn hosp-remarks"
                                   data_status="<?php echo $value['rel_hosp_status']; ?>"
                                   remark_txt="<?php echo Html::encode(htmlentities($value['rel_hosp_remarks'])); ?>"
                                   hosp_name_txt="<?php echo "来源【" . $value['tp_platform_name'] . "】医院：【" . $value['hospital_name'] . "】"; ?>"
                                   real_hosp_id="<?php echo $value['tp_hospital_code'] . '||||' . $value['tp_platform']; ?>">备注</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="14" style="text-align: center">
                        <div class="empty">为搜索到任何数据</div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="page" style="text-align: center;">
        <?= GoPager::widget([
            'pagination' => $pages,
            'goFormActive' => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id . '/cooperation-detail'], $requestParams, ['1' => 1])),
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
    $openStartStopUrl = Url::to(['cooperation-platform/cooperation-start-stop']);
    $updateRemarksUrl = Url::to(['cooperation-platform/cooperation-update-remarks']);

    ?>
    <script type="text/javascript">
        // 全选
        $("#checxAll").change(function (e) {
            var flag = $(this).is(':checked');
            $("input[name='hosp_id[]']").prop("checked", flag);
            hosp__option_html();
        });

        $(".hosp_checks").change(function (e) {
            hosp__option_html();
        });

        function hosp__option_html() {
            layui.use(['form'], function () {
                var form = layui.form;
                var option_html = '';
                var ids = get_check_ids();
                ids.sort();
                $('#primary_doc_select').html(option_html);
                if (ids.length > 0) {
                    $.each(ids, function (i, v) {
                        if (i == 0) {
                            option_html += '<option selected="selected" value="' + v + '">' + v + '</option>';
                        } else {
                            option_html += '<option value="' + v + '">' + v + '</option>';
                        }
                    });
                    $('#primary_doc_select').html(option_html);
                    $('#primary_div').show();
                } else {
                    $("#primary_doc_select").html('');
                    $('#primary_div').hide();
                }
                form.render('select');
            });

        }

        function get_check_ids() {
            var ids = [];
            $(".hosp_checks:checked").each(function () {
                ids.push($(this).val());
            });
            if (ids.length > 50) {
                $("#checxAll").prop("checked", false);
                $("input[name='hosp_id[]']").prop("checked", false);
                return layer.msg('最多只能操作50家医院！', {icon: 1});
            }
            return ids;
        }

        //重置表单选择
        $("#reset").click(function () {
            var fid = getValue().coo_id;
            var page = getValue().page;
            var str = '?';
            if (fid) {
                if (page) {
                    str += 'coo_id=' + fid + '&';
                } else {
                    str += 'coo_id=' + fid;
                }
            }
            if (page) {
                str += 'page=' + page
            }

            window.location.href = window.location.href.split('?')[0] + str;
        });

        //获取URL指定参数
        function getValue(url) {
            //首先获取地址
            var url = url || window.location.href;
            //获取传值
            var arr = url.split("?");
            //判断是否有传值
            if (arr.length == 1) {
                return null;
            }
            //获取get传值的个数
            var value_arr = arr[1].split("&");
            //循环生成返回的对象
            var obj = {};
            for (var i = 0; i < value_arr.length; i++) {
                var key_val = value_arr[i].split("=");
                obj[key_val[0]] = key_val[1];
            }
            return obj;
        }

        function get_check_ids_open(t) {
            var ids = [];
            var checkIds = [];
            $(".hosp_checks:checked").each(function () {
                var idArr = $(this).val().split('__')
                if (t == 3 && parseInt(idArr[1]) == 1) {
                    checkIds.push(idArr[0])
                }
                if (t == 1 && (parseInt(idArr[1]) == 2 || parseInt(idArr[1]) == 3)) {
                    checkIds.push(idArr[0])
                }
                ids.push(idArr[0]);
            });
            if (ids.length > 50) {
                $("#checxAll").prop("checked", false);
                $("input[name='hosp_id[]']").prop("checked", false);
                return layer.msg('最多只能操作50家医院！', {icon: 1});
            }
            if (checkIds.length > 0) {
                return false
            }
            return ids;
        }

        function get_ids(t) {
            var ids = get_check_ids_open(t)
            if (ids.length == 0) {
                layer.msg('请勾选号源医院', {icon: 2});
                return false;
            }
            if (ids == false) {
                layer.msg('勾选号源医院与您操作不符合，请检查', {icon: 2});
                return false;
            }
            return ids;
        }

        $('#stop-open').click(function (e) {
            var ids = get_ids(1)
            if (ids) {
                request_ajax_coo('停止开放', ids, 2, e)
            }
        })

        $('.hosp-remarks').click(function (e) {
            var real_hosp_id = $(this).attr('real_hosp_id');
            var hosp_name_txt = $(this).attr('hosp_name_txt');
            var remark_txt = $(this).attr('remark_txt');
            var data_status = $(this).attr('data_status');

            var tp_platform = $('#tp_platform_list').val();
            var _this = $(this);
            confi_title = '备注';
            confi_content = '<div class="layui-form-item layui-inline" style="margin-top: 5rem"><label class="layui-form-label">备注</label><div class="layui-input-block" ><input style="width:30rem"  type="text" name="hospital_real_remarks" value="'+remark_txt+'" id="hospital_real_remarks" placeholder="请输入备注信息(50字以内)" autocomplete="off" maxlength="50" class="layui-input"></div></div>';

            var openStartStopUrl = "<?=$updateRemarksUrl;?>";
            idStr = real_hosp_id.toString();
            layer.open({
                type: 1,
                title: confi_title,
                area: ['500px', '320px'],
                content: confi_content,
                btn: ['确定', '取消'],
                yes: function (index, layero) {
                    var hospital_real_remarks = $('#hospital_real_remarks').val();
                    if (hospital_real_remarks.length > 50) {
                        return layer.msg('备注信息不能大于50个字', {icon: 2});
                    }

                    $.post(openStartStopUrl, {
                        'ids': idStr,
                        'remarks': hospital_real_remarks,
                        'hosp_name_txt': hosp_name_txt,
                        'tp_platform': tp_platform,
                        'rel_hosp_status': data_status,
                        'coo_id':<?php echo $coo_id ?>,
                        "_csrf-backend":$('#_csrf-backend').val()}, function (res) {
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
                btn2: function (index, layero) {
                    _this.removeAttr('disabled');
                    layer.close(index);
                },
                end: function () {
                    _this.removeAttr('disabled');
                    $('#layui-layer-shade2').hide();
                }
            });
        })

        $("#start-open").click(function (e) {
            var ids = get_ids(3)
            if (ids) {
                request_ajax_coo('开始开放', ids, 1, e)
            }
        })

        $(".one-start-stop-open").click(function (e) {
            var data_id = $(this).attr('open_start_id');
            var data_type = $(this).attr('data_type');
            var ids = [];
            if (data_type == 1) {
                var start_stop_itle = '开始开放'

            } else if (data_type == 2) {
                var start_stop_itle = '停止开放'
            } else {
                layer.msg('类型有误', {icon: 2});
                return false;
            }
            ids.push(data_id);
            request_ajax_coo(start_stop_itle, ids, data_type, e)
        });

        function request_ajax_coo(openTitle, ids, tp, e) {
            var tp_platform = $('#tp_platform_list').val();
            var _this = $(this);
            if (tp == 2) {
                confi_text = '停止开放';
                confi_title = '停止开放';
                confi_content = '<div class="layui-form-item layui-inline"><h2 style="margin: 3rem 0 0 5rem; color: red">您是否确认停止开放？</h2></div>';
                confi_content += '<div class="layui-form-item layui-inline" style="margin-top: 5rem"><label class="layui-form-label" style="width: auto" >请填写备注<span style="color: red">*</span></label><div class="layui-input-block" ><input style="width:30rem"  type="text" name="hospital_remarks" value="" id="hospital_remarks" placeholder="请输入停止开放备注信息(50字以内)" autocomplete="off" maxlength="50" class="layui-input"></div></div>';
            } else if (tp == 1) {
                confi_text = '您是否确认开始开放？';
                confi_title = '开始开放';
                confi_content = '<div class="layui-form-item layui-inline"><h2 style="margin: 3rem 5rem 0 5rem; color: red">您是否确认开始开放？</h2></div>';
            } else {
                return layer.msg('类型有误,请确认操作', {icon: 2});
            }
            var openStartStopUrl = "<?=$openStartStopUrl;?>";
            idStr = ids.toString();
            layer.open({
                type: 1,
                title: confi_title,
                area: ['500px', '320px'],
                content: confi_content,
                btn: ['确定', '取消'],
                yes: function (index, layero) {
                    if (tp == 2) {
                        var remarks = $('#hospital_remarks').val().trim().replace('/\s/g','');
                        if (!remarks) {
                            return layer.msg('请填写备注信息', {icon: 2});
                        }
                        if (remarks.length > 50) {
                            return layer.msg('备注信息不能大于50个字', {icon: 2});
                        }
                    } else {
                        var remarks = '';
                    }
                    $.post(openStartStopUrl, {
                        'ids': idStr,
                        'open_type': tp,
                        'remarks': remarks,
                        'tp_platform': tp_platform,
                        'coo_id':<?php echo $coo_id ?>,
                        "_csrf-backend":$('#_csrf-backend').val()}, function (res) {
                        if (res.status == 1) {
                            layer.msg(res.msg, {icon: 1, time: 5000});
                            setTimeout(function () {
                                parent.location.reload();
                            }, 5000);
                        } else {
                            layer.msg(res.msg, {icon: 2});
                            setTimeout(function () {
                                parent.location.reload();
                            }, 3000);
                        }
                    });

                },
                btn2: function (index, layero) {
                    _this.removeAttr('disabled');
                    layer.close(index);
                },
                end: function () {
                    _this.removeAttr('disabled');
                    $('#layui-layer-shade2').hide();
                }
            });
        }
    </script>
