<?php
/**
 * Created by wangwencai.
 * @file: aes.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-08-10
 */
$this->title = 'AES数据解析';
?>
<form action="<?= \yii\helpers\Url::to('/log/aes') ?>" method="post" enctype="application/x-www-form-urlencoded">
    <div class="radio">
        <label>
            <input value="1" name="type" type="radio" <?= $params['type'] == 1 ? 'checked' : '';?>> 代理商
        </label>
        <label>
            <input value="2" name="type" type="radio" <?= $params['type'] == 2 ? 'checked' : '';?>> 民营医院
        </label>
    </div>
    <div class="form-group">
        <textarea name="encrypt_data" class="form-control" rows="3" placeholder="粘贴加密的请求/响应数据"><?= $params['encrypt_data'] ?? '';?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">提交</button>
</form>
<br>
<div class="box">
    <div class="box-header">解析结果：</div>
    <div class="box-body">
        <?php
        if ($decrypt_data) {
            echo "<pre>";
            var_export(json_decode($decrypt_data, true));
        } else {
            echo "<span class='text-gray'>暂无结果</span>";
        }
        ?>
    </div>
</div>