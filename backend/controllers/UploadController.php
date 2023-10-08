<?php

/**
 * @file UploadController.php
 * @author zhangfan <zhangfan01@yuanxin-inc.com>
 * @version 1.0
 * @date 2019/9/12
 */

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use common\sdks\BapiAdSdkModel;
use common\libs\CommonFunc;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use common\models\TbLog;
use common\models\Department;


class UploadController extends BaseController
{
    /**
     * @var array
     */
    public $imageExt = array('gif', 'jpg', 'jpeg', 'png');

    const MAX_NUM = 5001;//excel上传最大数量
    const UP_NUM = 1; //每次累加数

    /**
     * @var bool
     */
    public $enableCsrfValidation = false;
    protected $userAgents        = [
        'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1 (compatible; Baiduspider-render/2.0; +http://www.baidu.com/search/spider.html)',
        'Mozilla/5.0 (Linux; Android 8.0.0; LLD-AL30 Build/HONORLLD-AL30; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/63.0.3239.83 Mobile Safari/537.36 T7/11.1 baiduboxapp/11.1.5.10 (Baidu; P1 8.0.0)',
        'Mozilla/5.0 (Linux;u;Android 4.2.2;zh-cn;) AppleWebKit/534.46 (KHTML,like Gecko) Version/5.1 Mobile Safari/10600.6.3 (compatible; baidumib;mip; + https://www.mipengine.org)',
    ];

    /**
     * 递归创建目录
     * @author zhangfan <zhangfan01@yuanxin-inc.com>
     * @date 2019/9/18
     * @param $dir
     * @return bool
     */
    private function Directory($dir)
    {
        return is_dir($dir) or $this->Directory(dirname($dir)) and mkdir($dir, 0777);
    }

    public function actionUploadAvatarOss()
    {
        $uploadImage = UploadedFile::getInstanceByName('Filedata');
        $content = '';
        $suffix = explode('/', $uploadImage->type)[1];
        $isFalse = CommonFunc::checkDoctorAvatarSuffix($suffix);
        if(false == $isFalse){
            echo json_encode(array('error' => 1, 'message' => '请上传标准图片文件, 支持gif,jpg,png和jpeg! '));
        }else{
            $fp = fopen($uploadImage->tempName, 'r');
            if ($fp) {
                while (!feof($fp)) {
                    $content .= fgets($fp, 8888);
                }
            }
            $params['platform'] = 'nisiya';
            $params['type']     = 'guahao';
            $params['path']     = 'doctor_avatar';
            $params['fileDate'] = '';
            $params['file']     = base64_encode($content);

            $result = BapiAdSdkModel::getInstance()->uploadOss($params);
            if(false != $result){
                if($result['code'] == 200){
                    echo json_encode(array('error' => 0, 'url' => $result['data']['img_path'], 'img_path' => $result['data']['img_path'], 'img_url' => $result['data']['img_url']));
                } else {
                    echo json_encode(array('error' => 1,'message'=>$result['msg']));
                }
            }else{
                echo json_encode(array('error' => 1, 'message' => '请上传标准图片文件, 支持gif,jpg,png和jpeg！'));
            }
        }
    }

    public static function UploadImageOss($url , $file_date='')
    {
        $params['platform'] = 'nisiya';
        $params['type']     = 'guahao';
        $params['path']     = 'doctor_avatar';
        $params['file']     = $url;
        $params['fileDate'] = trim(strval($file_date),'/');
        $result = BapiAdSdkModel::getInstance()->uploadOss($params);
        if(false != $result){
            if($result['code'] == 200){
                return ['img_path'=>trim(strval($result['data']['img_path']),'/'),'img_url'=>$result['data']['img_url']];
            } else {
                $result['img_path'] = '';
                return $result;
            }
        } else {
           return  ['img_path'=>'','img_url'=>''];
        }
    }

    public static function curl_file_get_contents($durl)
    {
        $_referer = '';
        if (strpos($durl,'hdfimg') !== false) {
            $_referer = 'https://fxphh.haodf.com/';
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $durl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 35);
        if ($_referer) { 
            curl_setopt ($ch, CURLOPT_REFERER, $_referer);  
        }
        // curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1 (compatible; Baiduspider-render/2.0; +http://www.baidu.com/search/spider.html)');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.190 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $r        = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode >= 200 && $httpCode <= 299) {
            curl_close($ch);
            return $r;
        } else {
            curl_close($ch);
            return '';
        }
    }

    /**
     * 导入匹配科室
     * @author zhanghongjian
     * @date 2021/10/08
     * @return array
     */
    public function actionKeshiRelationImport()
    {
        $this->enableCsrfValidation = false;
        //获取文件以及校验,目前支持csv,xlsx,xls格式
        $upload = UploadedFile::getInstanceByName('file');
        $suffix = substr(strrchr($upload->name, '.'), 1);
        if(!$this->isExcelFile($suffix)){
            $this->returnJson(2, '文件格式错误');
        }

        //格式化excel文件
        $inputFileType = IOFactory::identify($upload->tempName);
        $objRead = IOFactory::createReader($inputFileType);
        $objRead->setReadDataOnly(true);
        $spreadsheet = IOFactory::load($upload->tempName);

        //检测上传数量
        $fileNum  = $spreadsheet->getSheet(0)->getHighestRow();
        if($fileNum > self::MAX_NUM){
            $msg = '最多上传'. self::MAX_NUM .'条数据 ';
            $this->returnJson(2, $msg);
        }

        //整理成数组格式
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        //校验表头
        $msg = $this->checkHeard($sheetData[1]);
        if($msg){
            $this->returnJson(2, $msg);
        }
        //修改数据
        $updateRes = $this->updateDepartment($sheetData);
        //获取返回信息，写入日志
        $message = $this->getMessage($updateRes);

        return $this->returnJson(1, $message);
    }

    /**
     * 是否是excel文件
     * @param string $suffix 文件后缀名
     * @author zhanghongjian
     * date 2021/10/21
     * @return bool
     */
    private function isExcelFile($suffix){
        $suffixArr = [
            'csv',
            'xlsx',
            'xls'
        ];
        if(!in_array($suffix, $suffixArr)){
            return false;
        }
        return true;
    }

    /**
     * 检测excel表头
     * @param array $sheetData
     * @author zhanghongjian
     * date 2021/10/17
     * @return string | false
     */
    private function checkHeard($sheetData){
        $heardArr = [
            'A' => 'ID',
            'B' => '医院一级科室',
            'C' => '医院二级科室',
            'D' => '王氏一级科室ID',
            'E' => '王氏一级科室名称',
            'F' => '王氏二级科室ID',
            'G' => '王氏二级科室名称',
            'H' => '数据状态（1禁用，正常为空）',
        ];
        if(count($heardArr) != count($sheetData)){
            $this->returnJson(2, '表头数据不正确');
        }
        foreach($heardArr as $heardKey => $heardName){
            if($sheetData[$heardKey] != $heardName){
                $msg = '表头数据不正确，请检查第'. $heardKey . '列';
                return $msg;
            }
        }
        return false;
    }

    /**
     * 获取修改数据
     * @param array $updateRes
     * @author zhanghongjian
     * date 2021/10/17
     * @return void
     */
    private function getMessage($updateRes){
        $message = '成功修改' . $updateRes['success_num'] . '条,修改失败' . $updateRes['error_num'] . '条,';
        $message .= '有问题数据' . $updateRes['empty_num'] . '条,已关联未修改的数据' . $updateRes['is_update_num'] . '条';
        $editContent = $this->userInfo['realname'] . '导入科室关联数据:' . $message . '。';
        $editContent .= '修改成功ID'. json_encode($updateRes['success_id_list']) ;
        $editContent .= '修改失败的ID' . json_encode($updateRes['error_id_list']);
        $editContent .= '已关联未修改的数据ID' . json_encode($updateRes['is_upate_id_list']);
        $editContent .= '数据有问题的ID' . json_encode($updateRes['empty_id_list']);
        $editContent .= '一级科室ID校验未通过ID' . json_encode($updateRes['first_keshi_id_list']);
        $editContent .= '二级科室ID校验未通过ID' . json_encode($updateRes['second_keshi_id_list']);
        $editContent .= '一级科室名称校验未通过ID' . json_encode($updateRes['first_name_id_list']);

        $logId = time().rand(1000, 9999);
        $msg = '导入科室关联的王氏科室' . $logId;
        $this->addLog($editContent, $msg);
        return $message;
    }

    /**
     * 写入日志
     * @param string $editContent 编辑内容
     * @param string $msg 日志描述
     * @author zhanghongjian
     * date 2021/10/17
     * @return
     */
    private function addLog($editContent, $msg){
        //日志最大支持1000当日志大小大于1000，分割日志
        $increasing = 1000;
        $count = strlen($editContent);
        if($count >= $increasing){
            $log = '';
            for($i = 0; $i < $count ; $i += $increasing){
                $start = $i;
                $end = $start + $increasing;
                $log = substr($editContent, $i , $end);
                TbLog::addLog($log, $msg);
            }

        }else{
            TbLog::addLog($editContent, $msg);
        }
        return ;
    }

    /**
     * 检测写入数据
     * @param array $sheetData
     * @param array $depData
     * @param array $depsData
     * @author zhanghongjian
     * date 2021/10/17
     * @return void
     */
    private function checkData($sheetData, $depData, $depsData){
        $update = [];
        //ID为空直接过滤
        if(empty($sheetData['A'])){
            return $update;
        }
        //检测一级科室ID
        if(!empty($sheetData['B'])){
            $depWhere = ['parent_id' => 0, 'department_name' => $sheetData['B']];
            $depId = Department::find()->where($depWhere)->select(['department_id'])->scalar();
            if($depId){
                $update['parent_id'] = $depId;
            }else{
                $update['keshi_data']['keshi_name_id'] = $sheetData['A'];
            }
        }
        //检测王氏一级科室
        if(!empty($sheetData['D'])){
            foreach($depData as $data){
                if($data['id'] == $sheetData['D']){
                    $update['miao_first_department_id'] = $sheetData['D'];
                    break;
                };
            }
            if(empty($update['miao_first_department_id'])){
                $update['keshi_data']['first_keshi_id'] = $sheetData['A'];
            }
        }
        //检测王氏二级科室ID
        if(!empty($sheetData['F'])){
            foreach($depsData as $data){
                if(($data['id'] == $sheetData['F']) && ($data['parentid'] == $update['miao_first_department_id'])){
                    $update['miao_second_department_id'] = $sheetData['F'];
                    break;
                };
            }
            if(empty($update['miao_second_department_id'])){
                $update['keshi_data']['second_keshi_id'] = $sheetData['A'];
            }
        }
        //是否禁用
        if(!empty($sheetData['H'])){
            $update['status'] = 0;
        }
        //一级科室，王氏一级科室，王氏二级科室不允许为空
        if(empty($update['parent_id']) || empty($update['miao_first_department_id']) || empty($update['miao_second_department_id'])){
            unset($update['parent_id']);
            unset($update['miao_first_department_id']);
            unset($update['miao_second_department_id']);
        }

        return $update;
    }

    /**
     * 更新科室关联
     * @param array $sheetData
     * @author zhanghongjian
     * @date 2021/10/17
     * @return void
     */
    private function updateDepartment($sheetData){
        //初始化日志数据
        $forMaxNum = count($sheetData) + self::UP_NUM;
        $successNum = 0;
        $errorNum = 0;
        $emptyNum = 0;
        $isUpdateNum = 0;
        $successIdList = [];
        $errorIdList  = [];
        $isUpateIdList = [];
        $emptyIdList = [];
        $firstKeshiIdList = [];
        $secondKeshiIdList = [];
        $firstNameIdList = [];

        $model = new Department();
        $depData = CommonFunc::getFkeshiInfos();
        $depsData = CommonFunc::getSkeshiInfos();
        //excel文件数据从第二条开始
        for($i = 2; $i < $forMaxNum; $i += self::UP_NUM){
            //数据有问题，直接跳过
            if(!isset($sheetData[$i]['A'])){
                continue;
            }
            //修改条件
            $keshiData = [];
            $update = $this->checkData($sheetData[$i], $depData, $depsData);
            //这里记录日志用
            if(!empty($update['keshi_data'])){
                $keshiData = $update['keshi_data'];
                unset($update['keshi_data']);
                if(!empty($keshiData['first_keshi_id'])){
                    $firstKeshiIdList[] = $keshiData['first_keshi_id'];
                }
                if(!empty($keshiData['second_keshi_id'])){
                    $secondKeshiIdList[] = $keshiData['second_keshi_id'];
                }
                if(!empty($keshiData['keshi_name_id'])){
                    $firstNameIdList[] = $keshiData['keshi_name_id'];
                }
            }

            //修改数据，checkData不通过数据为空
            if(empty($update)){
                $emptyNum ++;
                $emptyIdList[] = $sheetData[$i]['A'];
                //修改数据
            }else{
                if(!empty($update['miao_first_department_id'])){
                    $update['is_match'] = 1;
                }
                $where = "department_id = ".$sheetData[$i]['A'];
                //查询是否关联
                $isRelation = $model->find()->where(['department_id' => $sheetData[$i]['A'], 'is_match' => 1])->select(['department_id'])->scalar();
                //未关联数据
                if(!$isRelation){
                    if($model->updateAll($update, $where)){
                        $successNum ++ ;
                        $successIdList[] = $sheetData[$i]['A'];
                    }else{
                        $errorIdList[] = $sheetData[$i]['A'];
                        $errorNum ++;
                    }
                    //已关联数据
                }else{
                    $isUpateIdList[] = $sheetData[$i]['A'];
                    $isUpdateNum ++;
                }
            }
        }

        //返回日志数据
        return [
            'success_num'          => $successNum,
            'error_num'            => $errorNum,
            'empty_num'            => $emptyNum,
            'is_update_num'        => $isUpdateNum,
            'success_id_list'      => $successIdList,
            'error_id_list'        => $errorIdList,
            'is_upate_id_list'     => $isUpateIdList,
            'empty_id_list'        => $emptyIdList,
            'first_keshi_id_list'  => $firstKeshiIdList,
            'second_keshi_id_list' => $secondKeshiIdList,
            'first_name_id_list'   => $firstNameIdList,
        ];
    }
}
