<?php
/**
 * 医生头像 oss 迁移脚本
 * @file DoctorAvatarMigration.php
 * @author liuyingwei <liuyingwei@yuanxinjituan.com>
 * @version 1.0
 * @date 2021-08-25
 */

namespace console\controllers;

use common\libs\CommonFunc;
use common\models\DoctorModel;
use common\sdks\BapiAdSdkModel;
use yii\helpers\FileHelper;
use common\sdks\BaseSdk;


class DoctorAvatarMigrationController extends \yii\console\Controller
{
    public      $start_time;
    protected   $doctor_avatar_path   = '';
    protected   $old_avatar_dir       = '';
    protected   $doctor_avatar_dir    = '';
    protected   $local_path           = '';
    protected   $log_path             = '';
    protected   $log_name             = '';
    protected   $error_log_name       = '';

    public function init()
    {
        parent::init();
        $this->start_time = microtime(true);
        $this->doctor_avatar_path = \Yii::$app->params['avatarUrl'];
        $this->local_path   = '';
        $this->log_path     = '/tmp/';
        $this->old_avatar_dir       = '/data/upload/user_avatar/doctor_avatar/';
        $this->doctor_avatar_dir    = '/data/ossfs/nisiya/guahao/doctor_avatar/';
    }

    /**
     *   迁移脚本
     *   /usr/local/php7/bin/php /data/wwwroot/nisiya.top/yii doctor-avatar-migration/select-doctor-oss 1 10
     * @param int $start_id
     * @param int $end_id
     * @param int $cp_type
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-09-01
     */
    public function actionSelectDoctorOss($start_id = 0, $end_id = 0, $cp_type=1)
    {
        $startTime  = time();
        $doctorModel    = new DoctorModel();
        //记录日志
        $this->log_name         = 'doctor_start_' . strval($start_id) . '_doctor_end_' . strval($end_id) . '.log';
        $this->error_log_name   = 'error_doctor_start_' . strval($start_id) . '_doctor_end_' . strval($end_id) . '.log';
        file_put_contents($this->log_path . $this->log_name, '');
        file_put_contents($this->log_path . $this->error_log_name, '');

        $query = $doctorModel::find()->select('doctor_id,avatar,create_time,realname,hospital_id')->where(['<>', 'avatar', '']);
        $queryClone = clone $query;
        if ($start_id && $end_id) {
            $query->andWhere(['>=', 'doctor_id', intval($start_id)]);
            $query->andWhere(['<=', 'doctor_id', intval($end_id)]);
            $doctorData = $query->asArray()->all();
            echo '医生头像不为空的当前条件数量为【 ' . count($doctorData) . ' 】' . PHP_EOL;
            $folderArr = [];
            if($cp_type == 2){
                $folderArr  = $this->actionRange(count($doctorData));
            }
            if ($doctorData) {
                foreach ($doctorData as $k => $v) {
                    //检查数据是否已经修改
                    $explodeAvatar  = explode('/',$v['avatar']);
                    $is_date        = strtotime($explodeAvatar[0]) ? strtotime($explodeAvatar[0]) : false;

                    if($is_date  != false){
                        echo '已修改 doctor_id: ' . $v['doctor_id'] . PHP_EOL;
                        continue;
                    }
                    echo $v['realname'] . "===".$v['doctor_id'] . $v['create_time'] . PHP_EOL;
                    echo $this->doctor_avatar_path . $v['avatar'] . PHP_EOL;
                    // copy 根据图片查找文件的物理位置
                    $arr        = explode('/', $v['avatar']);
                    $fileName   = array_pop($arr);
                    if($cp_type == 1){
                        // 1 根据创建日期
                        $year   = date('Y', $v['create_time']) . '/';
                        $month  = date('m', $v['create_time']) . '/';
                        $day    = date('d', $v['create_time']) . '/';
                        $newDir = strval($year) . strval($month) . strval($day);
                        $this->folderMakeDirs($newDir);
                        echo '根据医生创建日期， 生成日期目录' . $newDir . PHP_EOL;
                    }elseif($cp_type == 2){
                        // 2 根据数据id
                        $newDir = array_rand($folderArr,1);
                        echo '根据医生id 通过随机， 生成日期目录' . $newDir . PHP_EOL;
                    }else{
                        echo '参数错误';
                        break;
                    }

                    $this->actionDoctorAvatarCopy($v['doctor_id'], $v['avatar'], $newDir, $fileName, $v['hospital_id']);
                }
            }else{
                echo '当前没有数据可以进行迁移' . PHP_EOL;
            }
        }else{
            // 全部根据数据id 创建 (慎重 数据量会跟大)
            $doctorData = $queryClone->asArray()->all();
            $folderArr  = $this->actionRange(count($doctorData));
            foreach ($doctorData as $k => $v) {
                //获取数据
                echo $v['realname'] . PHP_EOL;
                echo $v['doctor_id'] . PHP_EOL;
                echo $v['create_time'] . PHP_EOL;
                echo $this->doctor_avatar_path . $v['avatar'] . PHP_EOL;

                $arr        = explode('/', $v['avatar']);
                $fileName   = array_pop($arr);
                $newDir     = array_rand($folderArr,1);
                echo '根据医生id 通过随机， 生成日期目录' . $newDir . PHP_EOL;
                $this->actionDoctorAvatarCopy($v['doctor_id'], $v['avatar'], $newDir, $fileName, $v['hospital_id']);
            }
        }

        $endTime = time();

        echo '总耗时： '. strval(intval($endTime) - intval($startTime)). 's'. PHP_EOL;
    }

    /**
     *
     *  /usr/local/php7/bin/php /data/wwwroot/nisiya.top/yii doctor-avatar-migration/doctor-avatar-copy
     * @param $doctor_id
     * @param $primary_avatar
     * @param $new_dir
     * @param $new_file
     * @param $hospital_id
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-08-26
     */
    public function actionDoctorAvatarCopy($doctor_id, $primary_avatar, $new_dir, $new_file, $hospital_id)
    {
        echo '这里是 移动头像脚本 开始 -- 第一步是copy ' . PHP_EOL;
        $primaryAvatar  = $this->local_path . $this->old_avatar_dir . strval($primary_avatar);         // 图片全路径
        $path           = $this->local_path . $this->doctor_avatar_dir . strval($new_dir) . strval($new_file);

        echo $primaryAvatar . $path . PHP_EOL;
        try{
            copy(strval($primaryAvatar), strval($path));
        }catch (\Exception $e){
            $url = $this->doctor_avatar_path . strval($primary_avatar);
            $curl = curl_init($url);
            // 不取回数据
            curl_setopt($curl, CURLOPT_NOBODY, true);
            $result = curl_exec($curl);
            $found = false;
            if ($result !== false) {
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                // 记录日志
                $errorMsg = '远程图片无法打开 状态码【'.$httpCode.'】doctor_id: 【'. $doctor_id .'】';
            }else{
                $errorMsg = '图片地址不存在：'.$e->getMessage();
            }
            file_put_contents($this->log_path . $this->error_log_name, strval($errorMsg) . PHP_EOL, FILE_APPEND);
        }
        // 记录日志， 用来检测数据迁移是否有问题
        $msg = $doctor_id . '|' . strval($primary_avatar) . '|' . strval($new_dir) . strval($new_file) . '|' . $hospital_id;
        echo $msg. PHP_EOL;
        echo $this->log_path.$this->log_name. PHP_EOL;
        file_put_contents($this->log_path . $this->log_name, strval($msg) . PHP_EOL, FILE_APPEND);
    }

    /**
     *  检测迁移的数据是否有问题
     *   /usr/local/php7/bin/php /data/wwwroot/nisiya.top/yii doctor-avatar-migration/check-doctor-avatar 1 10
     * @param int $start_id
     * @param int $end_id
     * @param int $real_update
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-08-27
     */
    public function actionCheckDoctorAvatar($start_id = 0, $end_id = 0, $real_update = 0)
    {
        $startTime  = time();
        $dModel = new DoctorModel();

        echo '这里是 移动头像脚本 开始 -- 第二步是update ' . PHP_EOL;

        $this->log_name     = 'doctor_start_' . strval($start_id) . '_doctor_end_' . strval($end_id) . '.log';
        $doctorUpdateLog    = 'doctor_update_' . strval($start_id) . '_doctor_end_' . strval($end_id) . '.log';



        file_put_contents($this->log_path . $doctorUpdateLog, '');
        foreach (glob($this->log_path . $this->log_name) as $file) {
            $log = new \SplFileObject($file);
            foreach ($log as $line) {
                //这里操作每一行($line)
                $logArr = explode('|', $line);
                if ($logArr[0] == "") {
                    continue;
                }
                // 判读文件是否有效 1 文件是否存在， &&  2 是否可以打开
                $filePath   = trim($this->local_path . $this->doctor_avatar_dir . $logArr[2]);

                echo '医生 doctor_id:【' . trim($logArr[0]) . '】 hospital_id 【' . trim($logArr[3]) . '】' . PHP_EOL;
                $updateMsg  = '医生 doctor_id:【' . $logArr[0] . '】 hospital_id 【' . trim($logArr[3]) . '】';

                if (file_exists($filePath)) {
                    echo trim($logArr[2]) . ' 存在！' . PHP_EOL;
                    // 执行修改数据库
                    if ($real_update) {
                        try {
                            $transition = \Yii::$app->getDb()->beginTransaction();
                            $result     = DoctorModel::updateAll(['avatar' => trim($logArr[2])], ['doctor_id' => intval($logArr[0])]);
                            $transition->commit();
                            // 更新缓存
                            if (intval(trim($logArr[0])) && intval(trim($logArr[3]))) {

                                echo '医生 doctor_id:【' . trim($logArr[0]) . '】 hospital_id 【' . trim($logArr[3]) . '】 异步更新缓存 ' . PHP_EOL;

                                $dModel->UpdateInfo(intval(trim($logArr[0])));
                            }
                            echo $updateMsg.'修改成功'. PHP_EOL. PHP_EOL;
                        } catch (\Exception $e) {
                            $transition->rollBack();
                            $msg = $e->getMessage();
                            echo $updateMsg.'修改失败'. PHP_EOL;
                            echo \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生头像修改失败');
                        }
                    }
                } else {
                    echo trim($logArr[2]) . ' 不存在！' . PHP_EOL;
                }
            }
        }
        $endTime = time();
        echo '总耗时1： '. strval(intval($endTime) - intval($startTime)). 's'. PHP_EOL;
    }

    /**
     *
     * @param $path
     * @param int $mode
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-08-26
     */
    public function folderMakeDirs($path, $mode = 0777)
    {
        $path = $this->local_path . $this->doctor_avatar_dir . $path;
        if (is_dir($path)) {
            echo "对不起！目录 " . $path . " 已经存在！" . PHP_EOL;
        } else {
            //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
            $res = mkdir(iconv("UTF-8", "GBK", $path), 0777, true);
            if ($res) {
                echo "目录 $path 创建成功" . PHP_EOL;
            } else {
                echo "目录 $path 创建失败" . PHP_EOL;
            }
        }
    }

    /**
     *  迁移完成后，检查迁移后的图片是否有问题
     *   /usr/local/php7/bin/php /data/wwwroot/nisiya.top/yii doctor-avatar-migration/check-doctor-avatar-oss 1 10
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-09-01
     */
    public function actionCheckDoctorAvatarOss($start_id = 0, $end_id = 0)
    {
        // 查询所有数据， 迁移是否成功
        $doctorModel    = new DoctorModel();
        $query          = $doctorModel::find()->select('doctor_id,avatar,create_time,realname,hospital_id')->where(['<>', 'avatar', '']);

        if ($start_id && $end_id) {
            $query->andWhere(['>=', 'doctor_id', intval($start_id)]);
            $query->andWhere(['<=', 'doctor_id', intval($end_id)]);
            $doctorData = $query->asArray()->all();
        }else{
            $doctorData = $query->asArray()->all();
        }
        if ($doctorData) {
            foreach ($doctorData as $k => $v) {
                $url = $this->doctor_avatar_path . strval($v['avatar']);
                $curl = curl_init($url);
                // 不取回数据
                curl_setopt($curl, CURLOPT_NOBODY, true);
                $result = curl_exec($curl);
                $found = false;
                if ($result !== false) {
                    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    if($httpCode == 200){
                        $errorMsg = '远程图片可以打开 状态码【'.$httpCode.'】doctor_id: 【'. $v['doctor_id'] .'】';
                    }else{
                        $errorMsg = '远程图片无法打开 状态码【'.$httpCode.'】doctor_id: 【'. $v['doctor_id'] .'】';
                    }
                } else {
                    $errorMsg = '链接有问题 ： doctor_id: 【'. $v['doctor_id'] .'】'.$url;
                }
                echo $errorMsg. PHP_EOL;
            }
        }else{
            echo '无数据';
            exit;
        }
    }

    /**
     *  生成n天前 年月日文件夹 (方法)
     * @param int $n
     * @return string
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-08-27
     */
    public function actionDays($n=1)
    {
        return strval(date("Y/m/d/", strtotime("-" . strval($n) . " day")));
    }

    /**
     * 根据数据总数，生成多个文件夹
     * @param int $countNum
     * @return array
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-08-27
     */
    public function actionRange($countNum = 10)
    {
        $imgNum = ceil($countNum / 10);
        if($countNum > 10000){
            $folderNum = ceil($countNum / 1000);
        }else{
            $folderNum = 9;
        }
        if ($imgNum > 1) {
            $number = range(0, $folderNum);
        }else{
            $number = [0];
        }
        $folderArr = [];
        foreach ($number as $v){
            $day = $this->actionDays($v);
            $this->folderMakeDirs($day);
            $folderArr[$v] = $day;
        }
        return $folderArr;
    }

    /**
     * 处理 数据表中 avatar 是远程链接的问题
     * @param int $start_id
     * @param int $end_id
     * @param int $real_update
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-09-07
     */
    public function actionUpdateDoctorAvatar($start_id=0, $end_id=0, $real_update=0)
    {
        $doctorModel    = new DoctorModel();
        //记录日志
        $query = $doctorModel::find()->select('doctor_id,avatar,create_time,realname,hospital_id');
        $queryClone = clone $query;
        if ($start_id && $end_id) {
            $query->andWhere(['>=', 'doctor_id', intval($start_id)]);
            $query->andWhere(['<=', 'doctor_id', intval($end_id)]);
            $query->andWhere(['like', 'avatar', 'http']);
            $doctorData = $query->all();
            foreach ($doctorData as $k => $v) {
                self::realUpdateDoctorAvatarOss($v,$real_update);
            }
        }else{
            $queryClone->andWhere(['like', 'avatar', 'http']);
            $doctorData = $queryClone->all();
            foreach ($doctorData as $k => $v) {
                self::realUpdateDoctorAvatarOss($v,$real_update);
            }
        }
    }

    /**
     *  avatar 是远程链接的问题 执行函数
     * @param $v
     * @param $real_update
     * @author liuyingwei <liuyingwei@yuanxinjituan.com>
     * @date 2021-09-07
     */
    public static function realUpdateDoctorAvatarOss($v,$real_update)
    {
        $doctorModel    = new DoctorModel();
        $avatar = CommonFunc::filterSourceAvatar($v->avatar);
        if($avatar == ""){
            $result['msg'] = '数据无需上传 直接设置为空';
        }else{
            $result = CommonFunc::uploadImageOssByUrl($avatar);
        }
        if(isset($result['msg'])){
            $updateMsg =  'error_doctor_id:【'.$v->doctor_id.'】图片url【'.$v->avatar.'】返回结果：'.$result['msg'].PHP_EOL.PHP_EOL;
            if ($real_update) {
                try {
                    $transition = \Yii::$app->getDb()->beginTransaction();
                    $doctorModelQuery = DoctorModel::find()->where(['doctor_id' => $v->doctor_id])->one();
                    $doctorModelQuery->doctor_id = $v->doctor_id;
                    $doctorModelQuery->avatar = '';
                    $doctorModelQuery->save();
                    $transition->commit();
                    echo $updateMsg. '修改数据为空成功'. PHP_EOL. PHP_EOL;
                } catch (\Exception $e) {
                    $transition->rollBack();
                    $msg = $e->getMessage();
                    echo $updateMsg.'修改失败'. PHP_EOL;
                    echo \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生头像修改失败');
                }
            }else{
                echo $updateMsg. PHP_EOL. PHP_EOL;
            }
        }else{
            $updateMsg =  'ok_doctor_id:【'.$v->doctor_id.'】图片url【'.$v->avatar.'】返回结果：'.$result['img_path'].PHP_EOL.PHP_EOL;
            // 修改数据库
            // 执行修改数据库
            if ($real_update) {
                echo '执行'. PHP_EOL;
                try {
                    $transition = \Yii::$app->getDb()->beginTransaction();
                    $doctorModelQuery = DoctorModel::find()->where(['doctor_id' => $v->doctor_id])->one();
                    $doctorModelQuery->doctor_id = $v->doctor_id;
                    $doctorModelQuery->avatar = trim($result['img_path']);
                    $doctorModelQuery->save();
                    $transition->commit();
                    echo $updateMsg. '修改成功'. PHP_EOL. PHP_EOL;
                } catch (\Exception $e) {
                    $transition->rollBack();
                    $msg = $e->getMessage();
                    echo $updateMsg.'修改失败1'. PHP_EOL;
                    echo \Yii::warning($msg, __CLASS__ . '::' . __METHOD__ . ' 医生头像修改失败');
                }
            }else{
                echo $updateMsg. PHP_EOL. PHP_EOL;
            }
        }

    }
}