<?php
/**
 * Created by wangwencai.
 * @file: UpLoader.php
 * @author: wangwencai <wangwencai@yuanxinjituan.com>
 * @version: 1.0
 * @date 2022-07-18
 */

namespace common\libs;

use Yii;
use yii\web\UploadedFile;
use common\sdks\BapiAdSdkModel;

class Uploader
{
    public $extensions = [/*'gif',*/ 'jpg', 'jpeg', 'png'];
    public $maxSize = 1024 * 1024; // 1mb

    public $platform;
    public $type;
    public $path;
    public $fileDate;

    public $errorMsg;

    private $fileName;

    public function __construct($file_name)
    {
        $this->fileName = $file_name;
    }

    /**
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-18
     * @return array|null
     * @throws \Exception
     */
    public function upload()
    {
        $upload_image = UploadedFile::getInstanceByName($this->fileName);
        if (!$upload_image) {
            $this->errorMsg = '请上传图片文件';
            return null;
        }
        if ($upload_image->size > $this->maxSize) {
            $this->errorMsg = '图片大小超过1MB';
            return null;
        }
        $suffix = explode('/', $upload_image->type)[1];
        if (!in_array(strval($suffix), $this->extensions)) {
            $this->errorMsg = '请上传标准图片文件, 支持' . join(',', $this->extensions);
            return null;
        }
        $content = '';
        $fp = fopen($upload_image->tempName, 'r');
        if ($fp) {
            while (!feof($fp)) {
                $content .= fgets($fp, 8888);
            }
        }

        if (!$content) {
            $this->errorMsg = '未读取到文件内容';
            return null;
        }

        $params['platform'] = $this->platform ?? 'nisiya';
        $params['type'] = $this->type ?? 'guahao';
        $params['path'] = $this->path ?? 'min_doctor';
        $params['fileDate'] = $this->fileDate ?? '';
        $params['file'] = base64_encode($content);

        $result = BapiAdSdkModel::getInstance()->uploadOss($params);
        if (false != $result) {
            if ($result['code'] == 200) {
                return ['url' => $result['data']['img_path'], 'img_path' => $result['data']['img_path'], 'img_url' => $result['data']['img_url']];
            } else {
                $this->errorMsg = $result['msg'];
                return null;
            }
        } else {
            $this->errorMsg = '上传图片失败，请重试';
            return null;
        }
    }

    /**
     * 获取错误信息
     * @author wangwencai <wangwencai@yuanxinjituan.com>
     * @date 2022-07-18
     * @return mixed
     */
    function getError()
    {
        return $this->errorMsg;
    }
}