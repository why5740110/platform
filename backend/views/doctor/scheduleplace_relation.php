<?php

use common\components\GoPager;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link href="/layui/css/layui.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
    <script type="text/javascript" src="/js/topcommon.js"></script>
    <script src="/js/jquery.js"></script>
    <script src="/layui/layui.js"></script>
    <style>
        .layui-table[lay-size=sm] td, .layui-table[lay-size=sm] th {
            font-size: 12px;
            padding: 2px 10px;
        }
    </style>
</head>
<body>

<div class="layui-row">
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label"></label>
        <div class="layui-input-block" style="width:200px;">
            共<?php echo $totalCount; ?>条
        </div>
    </div>
    <table class="table table-hover layui-table" lay-even="true" style="overflow-x:scroll;margin-top: 5px;">
        <thead>
        <tr>
            <th style="width: 1%">第三方执业地点ID</th>
            <th style="width: 1%">第三方执业地点名称</th>
            <th style="width: 7%">平台来源</th>
        </tr>
        </thead>
        <tbody  align="center" valign="center" border="1">
        <?php if (!empty($dataProvider)): ?>
            <?php foreach ($dataProvider as $value): ?>
                <tr>
                    <td style="width:9%"><?php echo $value['tp_scheduleplace_id']; ?></td>
                    <td style="width:9%"><?php echo $value['scheduleplace_name']; ?></td>
                    <td style="width:9%"><?php echo $platform[$value['tp_platform']] ?? '未知'; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="14" style="text-align: center">
                    <div class="empty">未搜索到任何数据</div>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
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
</div>
</body>
</html>