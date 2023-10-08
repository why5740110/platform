<?php
use yii\helpers\Url;
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
    <div class="layui-form-item layui-inline" style="width:100%;">
        <?php if(!empty($hospital_list)): ?>
            <table id="main-table"  class="table table-hover layui-table" lay-even="true" style="overflow-x:scroll;margin-top: 5px;">
                <thead>
                <tr>
                    <th style="vert-align: middle;text-align: center">第三方平台医院ID</th>
                    <th style="vert-align: middle;text-align: center">医院名称</th>
                    <th style="vert-align: middle;text-align: center">来源</th>
                </tr>
                </thead>
                <tbody align="center" valign="center">
                <?php foreach ($hospital_list as $value): ?>
                    <tr>
                        <td style=""><?php echo $value['tp_hospital_code']; ?></td>
                        <td style=""><?php echo $value['hospital_name']; ?></td>
                        <td style=""><?php echo $value['tp_platform_name']; ?></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        <?php else: ?>
        暂无关联医院
        <?php endif; ?>
    </div>

</div>
</body>
</html>


