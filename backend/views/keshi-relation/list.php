<?php
use yii\helpers\Url;
use yii\helpers\Html; 
use common\libs\CommonFunc;
use common\components\GoPager;//新分页
$request = \Yii::$app->request;
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
<div class="layui-layer-shade" id="layui-layer-shade2" times="2" style="z-index: 19891015; background-color: rgb(0, 0, 0); opacity: 0.3;"></div>

<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="layui-row">
    <form class="layui-form" action="">

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:60px;">状态</label>
            <div class="layui-input-block" style="width:100px;margin-left:60px;">
                <select name="is_relation">
                    <option value="0">全部</option>
                    <option value="1" <?php if(trim($request->get('is_relation')) == 1){ echo 'selected="selected"'; }?>><?php echo '已关联'; ?></option>
                    <option value="999" <?php if(trim($request->get('is_relation')) == 999){ echo 'selected="selected"'; }?>><?php echo '未关联'; ?></option>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">医生平台</label>
            <div class="layui-input-block" style="width:100px;margin-left:100px;">
                <?php $tp_platform_list = ['0'=>'全部'] + CommonFunc::getTpPlatformNameList(1);?>
                <?php echo Html::dropDownList('tp_platform',$request->get('tp_platform') ?? '',$tp_platform_list,array('id'=>'tp_platform_list',"class"=>"form-control input-sm"));?>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:145px;">第三方平台医院</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:180px;">
                <input type="text" name="hospital_name"  placeholder="请输入医院名称" autocomplete="off" class="layui-input" value="<?php echo trim($request->get('hospital_name', ''));?>">

            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">科室名称</label>
            <div class="layui-input-block" style="width:150px;margin-left:100px;">
                <input type="text" name="keshi"  placeholder="请输入医生标准科室" <?php if($request->get('keshi')){echo 'value="'.$request->get('keshi').'"';}?>  autocomplete="off" class="layui-input">
            </div>
        </div>
        <br/>
        <div class="layui-form-item layui-inline">
            <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="formDemoPane">搜索</button>
            <button type="reset" id="reset" class="layui-btn layui-btn-sm layui-btn-primary">重置</button>
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
            <th>ID</th>
            <th>来源</th>
            <th>第三方医院ID</th>
            <th>医院名称</th>
            <th>关联医院ID</th>
            <th>第三方医院科室</th>
            <th>关联的科室</th>
            <th>状态</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if(!empty($dataProvider)): ?>
            <?php foreach ($dataProvider as $value): ?>
                <tr>
                    <td><?php echo $value['id']; ?></td>
                    <td>
                        <?php
                            $tp_platform_list = CommonFunc::getTpPlatformNameList(1);
                            echo $tp_platform_list[$value['tp_platform']] ?? '';
                        ?>
                    </td>
                    <td><?php echo $value['tp_hospital_code']; ?></td>
                    <td><?php echo $value['hospital_name']; ?></td>
                    <td><?php echo $value['hospital_id'];?></td>
                    <td>
                        <?php echo $value['third_fkname'].'-'.$value['department_name'];?><br/>
                    </td>
                    <td><?php echo $value['keshi'];?></td>
                    <td><?php echo $value['is_relation']==0?'未关联':'已关联';?></td>
                    <td>
                        <?php if($value['is_relation'] == 0 || $value['hospital_id']==0){?>
                            <button type="button" class="layui-btn layui-btn-xs recheck-open"
                                    tp_platform="<?php echo $value['tp_platform'];?>"
                                    tp_hospital_code="<?php echo $value['tp_hospital_code']; ?>"
                                    hospital_id="<?php echo $value['hospital_id']; ?>"
                                    tp_department_id="<?php echo $value['tp_department_id']; ?>">
                                关联
                            </button>
                        <?php }else{ ?>

                            <button type="button" class="layui-btn layui-btn-xs layui-btn-normal">
                                已关联
                            </button>
                        <?php }?>

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
        'goFormActive' => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id . '/list'], $requestParams, ['1' => 1])),
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

<script type="text/javascript">

$(".recheck-open").click(function() {
    var hospital_id = $(this).attr('hospital_id');
    var tp_department_id = $(this).attr('tp_department_id');
    var tp_platform = $(this).attr('tp_platform');
    var tp_hospital_code = $(this).attr('tp_hospital_code');
    if (hospital_id == 0) {
        layer.msg('医院没有关联,请先关联医院', {icon: 2});
    } else {
        $('#layui-layer-shade2').show();
        var lock = false;
        layer.open({
            type: 2,
            area: 'auto',
            scrollbar: false,
            closeBtn: false,
            shadeClose: true,
            skin: 'layui-layer-demo',
            title: '关联科室',
            area: ['70%', '70%'],
            content: "/keshi/third-relation?hospital_id=" + hospital_id,
            btn: ['保存', '取消'],
            yes: function(index, layero) {
                //防止重复提交
                if (lock) {
                    return false;
                }
                lock = true;
                var fkid = $(layer.getChildFrame(".miao_search_fkid option:selected", index)).val(); // 文本值
                var skid = $(layer.getChildFrame(".miao_search_skid option:selected", index)).val(); // 文本值
                if (!fkid || !skid) {
                    layer.msg('科室不能为空！', {icon: 2});
                    lock = false;
                    return false;
                }
                $.ajax({
                    url: '/keshi/save-third-relation',
                    data: {
                        'hospital_id': hospital_id,
                        'tp_department_id': tp_department_id,
                        'frist_department_id': fkid,
                        'second_department_id': skid,
                        'dep_id': skid,
                        'tp_platform': tp_platform,
                        'tp_hospital_code': tp_hospital_code,
                        "_csrf-backend":$('#_csrf-backend').val()
                    },
                    timeout: 20000,
                    type: 'POST',
                    beforeSend: function () {
                        loading = showLoad();
                    },
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            layer.msg(res.msg, {icon: 1});
                            setTimeout(function() {
                                window.location.href = window.location.href;
                            }, 1000);
                        } else {
                            layer.msg(res.msg, {icon: 2});
                            setTimeout(function() {
                                window.location.href = window.location.href;
                            }, 2000);
                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        layer.msg('获取失败，请刷新重试', {icon: 2});
                    }
                });
            },
            btn2: function(index, layero) {
                layer.close(index);
            },
            //关闭窗口时回调
            end: function() {
                //解除提交锁定
                lock = false;
                $('#layui-layer-shade2').hide();
            }
        });
    }
});
    
</script>

