<?php
/**
 * Created by PhpStorm.
 * @file Log.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2019-09-02
 */

namespace common\libs;


use queues\AsyncLogingJobNew;

class Log
{
    /**
     * 错误异常信息处理
     * @param $exception
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2019-09-02
     */
    public static function sendExceptionMessage($exception)
    {

        $status_code = $exception->statusCode ? $exception->statusCode : $exception->getCode();
        $title = '医院挂号报警-'.YII_ENV;

        $request_params = \Yii::$app->request->isGet ? \Yii::$app->request->getQueryParams() : \Yii::$app->request->getBodyParams();
        $request_params = json_encode($request_params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $message  = '所属项目：' . \Yii::$app->id . '<br/>';
        $message .= '请求地址：' . \Yii::$app->request->getAbsoluteUrl() . '<br/>';
        $message .= '文件路径：' . $exception->getFile() . '<br/>';
        $message .= '页面状态：' . $status_code . '<br/>';
        $message .= '错误内容：' . $exception->getMessage() . '<br/>';
        $message .= '错误行数：' . $exception->getLine() . '<br/>';
        $message .= '请求参数：' . $request_params . '<br/>';
        $message .= '错误追踪：' . $exception->getTraceAsString() . '<br/>';

       if ($status_code != 404) {
           self::sendErrorMailNotice($title, $message);
           self::sendGuaHaoErrorDingDingNotice($title.PHP_EOL.$message);
//            if (YII_ENV == 'prod') {
//                self::sendErrorWeiXinNotice($title, $message);
//            }

       }
    }

    /**
     * 发送邮件报警
     * @param $title
     * @param $message
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2019-09-02
     */
    public static function sendErrorMailNotice($title, $message)
    {
        \Yii::$app->mailer->compose()
            ->setTo(['shangheguang@yunxinjituan.com'])
            ->setFrom('nisiyas@163.com')
            ->setSubject($title)
            ->setTextBody('')
            ->setHtmlBody($message)
            ->send();
    }

    /**
     * 发送微信报警
     * @param $title
     * @param $message
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2019-09-02
     */
    public static function sendErrorWeiXinNotice($title, $message)
    {
        $url = "http://139.196.82.79:8888/doctor/";
        $data['name'] = $title;
        $data['msg'] = $message;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_exec($curl);
        curl_close($curl);
    }

    /**
     * 把需要记录的日志放入到队列中.
     *
     * @param string|object|string $msg 需要记录的数据
     * $msgData = [
     *     'Link' => '请求或被请求地址'
     *     'msgType' => '日志类型'
     *     'data' => '响应结果'
     * ]
     *
     * @return int $job_id 任务ID
     * @throws NotFoundHttpException
     */
    public static function pushLogDataToQueues($msgData, $queue = '')
    {
        $logData = [];
        $logData['logData'] = $msgData;
        // $job_id = Yii::$app->queue->push(new AsyncLogingJob($logData));
        if (!empty($queue)) {
            $new_job_id = \Yii::$app->$queue->push(new AsyncLogingJobNew($logData));
        } else {
            $new_job_id = \Yii::$app->logqueue->push(new AsyncLogingJobNew($logData));
        }

        return $new_job_id;
    }

    /**
     * 钉钉报警 挂号群
     * @param $message
     * @return bool|string
     */
    public static function sendGuaHaoErrorDingDingNotice($message)
    {
        if (YII_ENV == 'prod') {
            $secret = 'SEC3c8760e21324386b2e1377c72eb72fadf7f2949e29cb771bc6b439dd7cae9440';
        } else {
            $secret = 'SECeb5c89a1c278eaa678b96e201edf2dfd381dd2271022330b1c95f0609d82b97a';
        }
        $t = time() * 1000;
        $ts = $t . "\n" . $secret;
        $sig = hash_hmac('sha256', $ts, $secret, true);
        $sig = base64_encode($sig);
        $sig = urlencode($sig);

        $data = array('msgtype' => 'text', 'text' => array('content' => $message));
        $data_string = json_encode($data);

        if (YII_ENV == 'prod') {
            $token = '5ec158ba7d44e6d6f373e1303c30ece1e3921b05e713409349d5131fec068fe4';
        } else {
            $token = '5615f20150228fc5ecaa2b3dc008873c1a3f4e04d1774a3f59c9085012574073';
        }

        $remote_server = "https://oapi.dingtalk.com/robot/send?access_token=" . $token . '&timestamp=' . $t . '&sign=' . $sig;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        //curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        //curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;

    }

}