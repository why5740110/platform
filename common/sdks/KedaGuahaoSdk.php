<?php
/**
 * @file KedaGuahaoSdk.php
 * @author wanghongying
 * @version 2.0
 * @date 2023/04/24
 */
namespace common\sdks;

use common\models\GuahaoOrderModel;
use yii\helpers\ArrayHelper;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class KedaGuahaoSdk
{
    const METHOD_POST = 'post';
    const REQUESTOPTIONS_XML = 'xml';

    protected static $_instance = null;
    public $domain = '';
    public $token    = '';
    public $flag;
    public $redis;
    public $tokenResult;
    public $tokenKey = 'KedaTokenKey';

    /**
     * @var Client|mixed
     */
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'config' => ['timeout' => 10]
        ]);
        $this->flag = 0;//标记
        $this->domain = ArrayHelper::getValue(\Yii::$app->params, 'keda_guahao.apiUrl');
        //获取token
        $this->redis = \Yii::$app->redis_codis;
        $this->token = $this->redis->get($this->tokenKey);
        if (!$this->token) {
            $this->getToken();
        }
    }

    //路由配置
    private $maps = [
        'oauth_token' => '/apiGateway/iptv-data-collect-adapter/iptv/oauth/token',//获取token
        'order_push' => '/apiGateway/iptv-data-collect-adapter/kedaxunfei/guahao/push', //推送订单
    ];

    /**
     * 单例
     * @return static
     * @author wanghongying
     * @date 2023/04/24
     */
    public static function getInstance()
    {
        if (static::$_instance === null) {
            return new static;
        }
        return static::$_instance;
    }

    public function getToken()
    {
        $params = [
            "grantType" => ArrayHelper::getValue(\Yii::$app->params, 'keda_guahao.grantType'),
            "clientId" => ArrayHelper::getValue(\Yii::$app->params, 'keda_guahao.clientId'),
            "clientSecret" => ArrayHelper::getValue(\Yii::$app->params, 'keda_guahao.clientSecret')
        ];
        $res  = $this->postJson($this->domain . $this->maps['oauth_token'], $params);
        $res = $res ? json_decode($res, true) : [];
        $this->tokenResult = $res;
        if ($res) {
            $code = ArrayHelper::getValue($res, 'code');
            if ($code == 200) {
                $data = ArrayHelper::getValue($res, 'data');
                if ($data) {
                    $this->token = "Bearer " . ArrayHelper::getValue($data, 'accessToken');
                    $expire = ArrayHelper::getValue($data, 'expiresIn');
                    if ($expire > 0) {
                        //记录redis缓存
                        $this->redis->setex($this->tokenKey, $expire, $this->token);
                    } else {
                        $this->getToken();
                    }
                }
            } else if ($code == 9001) {
                if ($this->flag > 2) {//连续请求3次之后还是返回9001 终止请求
                    $this->token = '';
                } else {
                    $this->getToken();
                }
                $this->flag ++;
            } else {
                $this->token = '';
            }
        }
    }

    /**
     *订单变更推送给科大讯飞   通知合作方（科大讯飞）
     * @param $order_sn  //挂号订单号
     * @return array|mixed
     * @throws \Exception
     * @author wanghongying
     * @date 2023/04/24
     */
    public function pushOrderStatus($order_sn){
        if($order_sn) {
            $detail = GuahaoOrderModel::findOne(['order_sn' => $order_sn]);
            if (!$detail) return [];
            //获取用户信息
            $user = CenterSDK::getInstance()->memberGetUser(['type' => 'uid', 'str' => $detail->uid]);
            $params = [
                'order_sn'        => $detail->order_sn,
                'uid'             => $detail->uid,
                'hospital_name'   => $detail->hospital_name,
                'department_name' => $detail->department_name,
                'visit_time'      => $detail->visit_time,
                'visit_nooncode'  => $detail->visit_nooncode,
                'state'           => $detail->state,
                'mobile'          => isset($user['mobile']) ? $user['mobile'] : '',
                'create_time'     => $detail->create_time
            ];
            $header["Authorization"] = $this->token;
            $res  = $this->postJson($this->domain . $this->maps['order_push'], $params, [], $header);
            return $res ? json_decode($res, true) : [];
        }else{
            return [];
        }
    }

    /**
     * @param string $url
     * @param array $postData
     * @param array $queryParams
     * @param array $headers
     * @param array $options
     * @return string
     */
    public function postJson($url, $postData = [], $queryParams = [], $headers = [], $options = [])
    {
        return $this->request(self::METHOD_POST, $url, $queryParams, $postData, RequestOptions::JSON, $headers, $options);
    }

    /**
     * @param string $method
     * @param string $url
     * @param $queryParams
     * @param $queryData
     * @param $bodyFormat
     * @param $headers
     * @param $options
     * @return string
     */
    public function request(
        $method,
        $url,
        $queryParams = [],
        $queryData = [],
        $bodyFormat = RequestOptions::FORM_PARAMS,
        $headers = [],
        $options = []
    )
    {
        $options[RequestOptions::QUERY] = $queryParams;
        $options[RequestOptions::HEADERS] = $headers;

        switch ($bodyFormat) {
            case RequestOptions::FORM_PARAMS:
                $options[RequestOptions::FORM_PARAMS] = $queryData;
                break;
            case RequestOptions::JSON:
                $options[RequestOptions::JSON] = $queryData;
                $options[RequestOptions::HEADERS]['Content-Type'] = 'application/json; charset=UTF-8';
                break;
            case RequestOptions::MULTIPART:
                $options[RequestOptions::MULTIPART] = $queryData;
                break;
            case self::REQUESTOPTIONS_XML:
                $options[RequestOptions::BODY] = $queryData;
                $options[RequestOptions::HEADERS]['Content-Type'] = 'text/xml; charset=UTF-8';
                break;
            default:
                $options[RequestOptions::BODY] = $queryData;
                break;
        }
        $indexStart = microtime(true);
        try {
            $response = $this->client->{$method}($url, $options);
            $result = $this->parseResponse($response);
            //记录日志
            $logData = [
                'curlAction' => '----------------请求科大讯飞接口----------------',
                'method' => $method,
                'url' => $url,
                'options' => array_filter($options),
                'bodyFormat' => $bodyFormat,
                'result' => $result,
                'getStatusCode' => $response->getStatusCode(),
                'getReasonPhrase' => $response->getReasonPhrase(),
                'log_code' => 200,
                'log_spend_time' => round(microtime(true) - $indexStart, 2),
            ];
            if ($this->tokenResult) $logData['tokenResult'] = $this->tokenResult;
            \Yii::$app->params['DataToKedaRequest'] = $logData;
            return $result;
        } catch (\Throwable $e) {
            //记录日志
            $logData = [
                'curlAction' => '----------------请求科大讯飞接口----------------',
                'method' => $method,
                'url' => $url,
                'options' => array_filter($options),
                'bodyFormat' => $bodyFormat,
                'getStatusCode' => $e->getCode(),
                'getReasonPhrase' => $e->getMessage(),
                'log_code' => 500,
                'log_spend_time' => round(microtime(true) - $indexStart, 2),
            ];
            if ($this->tokenResult) $logData['tokenResult'] = $this->tokenResult;
            \Yii::$app->params['DataToKedaRequest'] = $logData;
        }
        return '';
    }

    /**
     * @param ResponseInterface $response
     * @return string
     * @throws Exception
     */
    private function parseResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() != 200) {
            throw new Exception($response->getStatusCode(), $response->getReasonPhrase());
        }
        return $response->getBody()->getContents();
    }
}