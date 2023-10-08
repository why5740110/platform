<?php

use yii\helpers\Url;
use yii\helpers\Html;

//引入辅助表单类
use yii\helpers\ArrayHelper;
use yii\grid\GridView;

//引入数据小插件类
use yii\widgets\ActiveForm;
use common\libs\HashUrl;
use common\models\LogHospitalApiLogNew;

use dosamigos\datetimepicker\DateTimePicker;
use common\components\GoPager;
use common\libs\CommonFunc;

//新分页
$this->title = '接口日志';

$logPlatformNameList = CommonFunc::getLogPlatformNameList();
?>

<div class="row" style="overflow: scroll; ">
    <div class="backer_top_nav bgfff">
        <div class="form-group">
            <?php
            $form = ActiveForm::begin(['action' => '/log/api-list', 'method' => 'get', 'options' => ['name' => 'form'], 'id' => 'layer-form-table']);
            ?>

            <ul style="padding-top: 10px;">

                <li style="float: left;padding-left: 5px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>平台类型：</td>
                            <td>
                                <?php $catelist = ['' => '请选择'] + $logPlatformNameList; ?>
                                <?php echo Html::dropDownList('platform', $requestParams['platform'] ?? '', $catelist, ['class' => 'form-control input-sm']); ?>
                            </td>
                        </tr>
                    </table>
                </li>

                <li style="float: left;padding-left: 5px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>接口类型:</td>
                            <td>
                                <input name='request_type' type="text"
                                       value="<?php echo !empty($requestParams['request_type']) ? Html::encode($requestParams['request_type']) : ''; ?>"
                                       placeholder="接口类型" class="form-control input-sm"/>
                            </td>
                        </tr>
                    </table>
                </li>

                <li style="float: left;padding-left: 5px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>索引:</td>
                            <td>
                                <input name='index' type="text"
                                       value="<?php echo !empty($requestParams['index']) ? Html::encode($requestParams['index']) : ''; ?>"
                                       placeholder="索引" class="form-control input-sm"/>
                            </td>
                        </tr>
                    </table>
                </li>

                <li style="float: left;padding-left: 5px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>请求时间：</td>
                            <td>  <?php
                                echo DateTimePicker::widget([
                                    'language' => 'zh-CN',
                                    'size' => 'sm',
                                    'name' => 'create_time',
                                    'value' => !empty($requestParams['create_time']) ? Html::encode($requestParams['create_time']) : '',
                                    'clientOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                        'todayBtn' => true,
                                        'minView' => 2,
                                        'startView' => 2
                                    ],
                                ]);
                                ?>
                            </td>
                        </tr>
                    </table>
                </li>


                <li style="float: left;padding-left: 25px;">
                    <table style="text-align: center;height: 40px;">
                        <tr>
                            <td>
                                <?php echo Html::submitButton('搜索', array('class' => 'btn btn-block btn-primary')); ?>
                            </td>
                        </tr>
                    </table>
                </li>


            </ul>

            <?php ActiveForm::end(); ?>
        </div>
        <hr/>

        <div class="tabContent " style="clear: both">
            <div style="">
                <div class="p10">
                    <p class="tr" style="font-size: 15px;margin-bottom: 10px;">共<?php echo $totalCount; ?>条</p>
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th style="width:4%">ID</th>
                            <th style="width:4%">平台类型</th>
                            <th style="width:4%">接口类型</th>
                            <th style="width:4%">索引</th>
                            <th style="width:4%">简略日志</th>
                            <th style="width:4%">耗时</th>
                            <th style="width:4%">状态</th>
                            <th style="width:4%">请求时间</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($dataProvider)): ?>
                            <?php foreach ($dataProvider as $value): ?>
                                <tr>
                                    <td style="text-align: center"><?php echo $value['id']; ?></td>
                                    <td style="text-align: center"><?php echo $logPlatformNameList[$value['platform']] ?? ''; ?></td>
                                    <td style="text-align: center"><?php echo $value['request_type']; ?></td>
                                    <td style="text-align: center"><?php echo $value['index']; ?></td>
                                    <td style="width:4%"><?php echo '<a class="apilog" title="查看日志详情" data-id="' . $value['id'] . '" data-daystr="' . date('Y-m-d', $value['create_time']) . '" href="javascript:void(0);">' . Html::encode(mb_substr($value['log_detail'], 0, 50)) . '...</a>'; ?></td>
                                    <td style="text-align: center"><?php echo $value['spend_time']; ?></td>
                                    <td style="text-align: center"><?php echo $value['code']; ?></td>
                                    <td style="text-align: center"><?php echo date("Y-m-d H:i:s", $value['create_time']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="15">
                                    <div class="empty">没有筛选到任何内容哦</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <?= GoPager::widget([
                        'pagination' => $pages,
                        'goFormActive' => Yii::$app->urlManager->createUrl(array_merge([Yii::$app->controller->id . '/api-list'], $requestParams, ['1' => 1])),
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

            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    //操作日志
    $(document).on("click", '.apilog', function () {
        var id = $(this).attr('data-id');
        var create_time = $(this).attr('data-daystr');

        if (!id) {
            swal({
                title: "数据获取失败，请刷新重试",
                text: "2秒后自动关闭！",
                timer: 2000,
                showConfirmButton: true,
                background: '#ccc'
            });
            return false;
        }
        layer.open({
            type: 2,
            title: '接口日志',
            area: ['960px', '660px'],
            content: "/log/api-list?log_id=" + id + '&create_time=' + create_time,

            btn: ['确定'],
            yes: function (index, layero) {
                layer.close(index);
            }
        });
    });
</script>