<?php
/**
 * @file list.php
 * @author xiujianying
 * @version 1.0
 * @date 2021/9/15
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\libs\CommonFunc;
use yii\grid\GridView;
use common\components\GoPager;
use yii\helpers\Url;
use common\models\Department;

$this->title = '科室对应';
$request = \Yii::$app->request;

?>

<input type="hidden" name="_csrf-backend" id='_csrf-backend' value="<?php echo \Yii::$app->request->csrfToken; ?>">
<div class="layui-form">
    <?php
    $form = ActiveForm::begin(['action' =>'/keshi-relation/dep-list', 'method'=>'get','options' =>['name'=>'form'],'id'=>'layer-form-table']);
    ?>

    <ul style="padding-top: 10px;">

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:130px;">关联状态:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="is_match" lay-verify="required" lay-search="" lay-filter="search_fkid"  class="search_fkid">
                    <option value="">全部</option>
                    <option value="1" <?php if(1 == ($requestParams['is_match']??0)){ echo 'selected="selected"'; }?>>已关联</option>
                    <option value="2" <?php if(2 == ($requestParams['is_match']??0)){ echo 'selected="selected"'; }?>>未关联</option>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:130px;">数据状态:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="status" lay-verify="required" lay-search="" lay-filter="search_fkid"  class="search_fkid">
                    <option value="">全部</option>
                    <option value="1" <?php if(1 == ($requestParams['status']??0)){ echo 'selected="selected"'; }?>>正常</option>
                    <option value="2" <?php if(2 == ($requestParams['status']??0)){ echo 'selected="selected"'; }?>>禁用</option>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:130px;">王氏一级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="miao_first_department_id" lay-verify="required" lay-search="" lay-filter="miao_search_fkid"  class="miao_search_fkid">
                    <option value="">请选择王氏一级科室</option>
                    <?php if(!empty($miao_fkeshi_list)): ?>
                        <?php foreach($miao_fkeshi_list as $value):?>
                            <option value="<?=$value['id']?>" <?php if($value['id'] == ($requestParams['miao_first_department_id']??0)){ echo 'selected="selected"'; }?>><?=$value['name']?></option>
                        <?php endforeach;?>
                    <?php endif;?>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:130px;">王氏二级科室:</label>
            <div class="layui-input-block layui-inline" style="margin-left:0px;width:280px;">
                <select name="miao_second_department_id" lay-verify="required" lay-search="" lay-filter="miao_search_skid"  class="miao_search_skid">
                    <option value="">请选择王氏二级科室</option>
                    <?php if(!empty($miao_skeshi_list)): ?>
                        <?php foreach($miao_skeshi_list as $value):?>
                            <option value="<?=$value['id']?>" <?php if($value['id'] == ($requestParams['miao_second_department_id']??0)){ echo 'selected="selected"'; }?>><?=$value['name']?></option>
                        <?php endforeach;?>
                    <?php endif;?>
                </select>
            </div>
        </div>

        <div class="layui-form-item layui-inline">
            <label class="layui-form-label" style="width:100px;">二级科室名称:</label>
            <div class="layui-input-block" style="width:150px;margin-left:100px;">
                <input type="text" name="keshi"  placeholder="请输入二级科室名称" <?php if($request->get('keshi')){echo 'value="'.Html::encode($request->get('keshi')).'"';}?>  autocomplete="off" class="layui-input">
            </div>
        </div>

    </ul>
    <div class="layui-form-item layui-inline">
        <button type="submit" style="width: 70px;" class="layui-btn layui-btn-sm">搜索</button>
    </div>
    <div class="layui-form-item layui-inline">
        <a href="<?=Url::to(['keshi-relation/export'])?>"><span class="layui-btn layui-btn-sm" >导出</span></a>
    </div>
    <div class="layui-form-item layui-inline">
        <button type="button" class="layui-btn layui-btn-sm" id="update-excel">
            <i class="layui-icon">&#xe67c;</i>上传excel
        </button>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<hr/>
<div>
    <?= GridView::widget([
        'tableOptions'=>['class' => 'table table-striped table-bordered table-expandable'],
        'layout'=> '{summary}{items}<div class="text-right tooltip-demo">{pager}</div>',
        'summary' => '<div class="text-right" style="font-size: 15px;margin-bottom: 10px;">第{begin} -{end}条, 共{totalCount}条</div>',
        'pager'=>[
            'class' => GoPager::className(),
            'goFormActive' => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id.'/index'],$requestParams,['1'=>1])),
            'firstPageLabel' => '首页',
            'prevPageLabel' => '《',
            'nextPageLabel' => '》',
            'lastPageLabel' => '尾页',
            'totalPageLable' => '共x页',
            'totalCountLable' => '共x条',
            //'goButtonLable' => 'GO',
            'maxButtonCount' => 5
        ],
        'emptyText' => '没有筛选到任何内容哦',
        'dataProvider' => $dataProvider, //数据源($data为后台查询的数据)
        //设计显示的字段(说明:此数组为空,默认显示所有数据库查询出来的字段)
        'columns' => [
            // ['class' => 'yii\grid\CheckboxColumn', 'name' => 'id',],
            [
                'label' => 'ID',
                'attribute' => 'id',
                'format'=>'html',
                'value'=>function ($model,$key,$index,$column){
                    return $model->department_id;
                }
            ],
            [
                'label' => '一级科室',
                'attribute' => 'frist_department_name',
                'format' => 'html',
                'value' => function ($model, $key, $index, $column) {
                    $first_id = $model->parent_id==0?$model->department_id:$model->parent_id;
                    return Department::getKeshi($first_id);
                }
            ],
            [
                'label' => '二级科室',
                'attribute' => 'second_department_name',
                'format' => 'html',
                'value' => function ($model, $key, $index, $column) {
                    return Html::encode($model->department_name);
                }
            ],
            [
                'label' => '王氏一级科室',
                'format' => 'html',
                'value' => function ($model, $key, $index, $column) {
                    if ($model->miao_first_department_id) {
                        return CommonFunc::getKeshiName($model->miao_first_department_id);
                    }else{
                        return '--';
                    }
                }
            ],
            [
                'label' => '王氏二级科室',
                'format' => 'html',
                'value' => function ($model, $key, $index, $column) {
                    if ($model->miao_second_department_id) {
                        return CommonFunc::getKeshiName($model->miao_second_department_id);
                    }else{
                        return '--';
                    }
                }
            ],
            [
                'label' => '数据状态',
                'format' => 'html',
                'value' => function ($model, $key, $index, $column) {
                    return $model->status==1?'正常':'禁用';
                }
            ],
            [
                'label' => '关联状态',
                'format' => 'html',
                'value' => function ($model, $key, $index, $column) {
                    return $model->is_match==1?'已关联':'未关联';
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{edit}{forbidden}',
                'header' => '操作',
                'buttons' => [ // 指定每个方法页面显示(可以使图片/任意字)
                    'edit' => function ($url, $model, $key) {
                        if($model->is_match==1 || $model->status==0) {
                            return '';
                        }else{
                            return Html::a('关联科室', 'javascript:void(0);', ['title' => '关联科室', 'data-id' => $model->department_id, 'class' => 'layui-btn layui-btn-xs match-btn']);
                        }
                    },
                    'forbidden' => function ($url, $model, $key) {
                        $name = $model->status==1?'禁用':'恢复禁用';
                        $status = $model->status==1?'1':'2';
                       return Html::a($name, 'javascript:void(0);', ['title' => $name,'disabled-status'=>$status,'data-id' => $model->department_id,'class'=>'layui-btn layui-btn-xs layui-btn-danger forbidden-btn']);
                    },
                ],
            ],

        ],
    ]);?>


</div>

<script>
    layui.use('form', function(){
        var form = layui.form;
        form.on('select(miao_search_fkid)', function (data){
           var fkeshi_id = data.value;
            /* if(fkeshi_id == ''){
                $(".miao_search_skid").html('<option value="">请选择二级科室</option>');
                form.render('select');
                return false;
            }*/
            var keshiUrl = "/keshi/miao-second-department-list";
            $.get(keshiUrl, {'fkeshi_id':fkeshi_id,"_csrf-backend":$('#_csrf-backend').val()}, function (res){
                if(res.status == 1){
                    var html = '<option value="">请选择二级科室</option>';
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
    layui.use('upload', function(){
        var upload = layui.upload;
   
        //执行实例
        var uploadInst = upload.render({
            elem: '#update-excel'
            //,url: '/keshi-relation/import'
            ,url: '/upload/keshi-relation-import'
            //,auto: false
            ,method: 'post'
            ,accept: 'file'   
            ,before: function(obj){ 
                layer.load();
            }
            ,done: function(res, index, upload){
                if(res.status == 1){
                    layer.msg('上传成功'+res.msg);
                    layer.closeAll('loading');
                }else{
                    layer.msg('上传失败，'+res.msg);
                    layer.closeAll('loading');
                }
            }
            ,error: function(){
                layer.msg('上传失败，请联系开发者');
                layer.closeAll('loading');
                //请求异常回调
            }
        });
    });

    $(".match-btn").click(function () {
        var id = $(this).attr('data-id');
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
            content: "/keshi-relation/dep-match?id=" + id,
            btn : ['关联','取消'],
            yes: function (index, layero) {
                //防止重复提交
                if (lock) {
                    return false;
                }
                lock = true;

                var miao_fkid = $(layer.getChildFrame(".miao_search_fkid option:selected", index)).val(); // 文本值
                var miao_skid = $(layer.getChildFrame(".miao_search_skid option:selected", index)).val(); // 文本值

                if (!miao_fkid || !miao_skid) {
                    layer.msg('王氏科室不能为空！', {icon: 2});
                    lock = false;
                    return false;
                }
                $.ajax({
                    url: '/keshi-relation/dep-match',
                    data: {
                        'submit':1,
                        'id': id,
                        'miao_first_department_id': miao_fkid,
                        'miao_second_department_id': miao_skid,
                        "_csrf-backend":$('#_csrf-backend').val()
                    },
                    timeout: 5000,
                    type: 'POST',
                    success: function (res) {
                        if (res.status == 1) {
                            layer.msg(res.msg, {icon: 1});
                            setTimeout(function (){
                                window.location.href = window.location.href;
                            }, 1000);
                        } else {
                            layer.msg(res.msg, {icon: 2});
                            setTimeout(function (){
                                window.location.href = window.location.href;
                            }, 2000);
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        layer.msg('获取失败，请刷新重试', {icon: 2});
                    }
                });

            },
            btn2: function (index, layero) {
                layer.close(index);
            },
            //关闭窗口时回调
            end: function () {
                //解除提交锁定
                lock = false;
            }
        });

    });


    $('.forbidden-btn').click(function (event) {
        var id = $(this).attr('data-id');
        var disabled = $(this).attr('disabled-status');
        var comfirmMsg = '';
        if(disabled==1){
            comfirmMsg = '确定要禁用吗?';
        }else if(disabled==2){
            comfirmMsg = '确定要恢复禁用吗?';
        }
        var _this = $(this);
        if ($(this).attr('disabled') == 'disabled') {
            return false;
        }
        if (confirm(comfirmMsg) ? true : false) {
            $.ajax({
                url: '/keshi-relation/keshi-edit',
                data: {'id':id,'disabled':disabled,'submit':1,"_csrf-backend":$('#_csrf-backend').val()},
                timeout: 20000,//超时时间20秒
                type: 'POST',
                async: true,
                beforeSend: function () {
                    _this.attr({ disabled: "disabled" });
                    loading = showLoad();
                },
                success: function (res) {
                    layer.close(loading);
                    if (res.status == 1) {
                        layer.msg(res.msg, {icon: 1});
                        setTimeout(function (){
                            window.location.reload();
                        }, 500);
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

    })


</script>
