<?php
namespace common\sdks;
use Yii;

/**
 * 河南挂号sdk
 *
 */
class GuahaoSdk extends BaseSdk
{

    /**
     * 格式化请求xml
     * @param $op
     * @param $xml
     * @return string
     */
    protected static function getRequestXml($op,$xml)
    {
        $spid = \Yii::$app->params['hnguahao']['spid'];
        $xmlstr = <<<XML
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://web.webservice.busines.routdata.com">
        <soapenv:Body>
        <web:Process>
            <web:in0>$spid</web:in0>
            <web:in1>$op</web:in1>
            <web:in2><![CDATA[$xml]]></web:in2>
        </web:Process>
        </soapenv:Body>
        </soapenv:Envelope>
XML;
        return $xmlstr;
    }


    public static function getGuahaoInfo($op,$funArr)
    {
        $xml = self::getRequestXml($op, self::ToXml($funArr));
        $result = self::curll(\Yii::$app->params['hnguahao']['url'], $xml);
        try {
            return self::getXmlToArray($result);
        } catch (\Exception $e) {

        }
    }

        /*'hnguahao' => [
            'spid' => 'H202009S2113108MiaoS1116',
            'url' => 'http://115.29.175.63:8078/GuahaoService/services/GuaHaoService',
        ],*/


    /**
     * CURL
     * curl $params为空则get方式调用,$params不空为POST调用
     * @param string $url
     * @param string $params
     * @param string $header
     * @return boolean|mixed
     */
    public static function curll($url = '', $params = array(), $header=array())
    {
        if (empty($url)) {
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if(!empty($params)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        if(!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if ($result === false) {
            $result = curl_errno($ch);
        }
        curl_close($ch);
        return $result;
    }

    /**
     * 格式化xml
     * @param $xml
     * @return mixed|\SimpleXMLElement|string|string[]|null
     */
    static function getXmlToArray($xml)
    {
        $obj = SimpleXML_Load_String($xml);
        if ($obj === FALSE) return $xml;

        // GET NAMESPACES, IF ANY
        $nss = $obj->getNamespaces(TRUE);
        if (empty($nss)) return $xml;

        // CHANGE ns: INTO ns_
        $nsm = array_keys($nss);
        foreach ($nsm as $key)
        {
            // A REGULAR EXPRESSION TO MUNG THE XML
            $rgx
                = '#'               // REGEX DELIMITER
                . '('               // GROUP PATTERN 1
                . '\<'              // LOCATE A LEFT WICKET
                . '/?'              // MAYBE FOLLOWED BY A SLASH
                . preg_quote($key)  // THE NAMESPACE
                . ')'               // END GROUP PATTERN
                . '('               // GROUP PATTERN 2
                . ':{1}'            // A COLON (EXACTLY ONE)
                . ')'               // END GROUP PATTERN
                . '#'               // REGEX DELIMITER
            ;
            // INSERT THE UNDERSCORE INTO THE TAG NAME
            $rep
                = '$1'          // BACKREFERENCE TO GROUP 1
                . '_'           // LITERAL UNDERSCORE IN PLACE OF GROUP 2
            ;
            // PERFORM THE REPLACEMENT
            $xml =  preg_replace($rgx, $rep, $xml);
        }
        $xml = new \SimpleXMLElement($xml);
        $body = $xml->xpath('//ns1_out')[0];
        return json_decode(json_encode(SimpleXML_Load_String($body, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    /**
     * 输出xml字符
     **/
    public static function ToXml($data)
    {
        if(!is_array($data)
            || count($data) <= 0)
        {
            return false;
        }

        $xml = '<?xml version="1.0" encoding="utf-8"?><request><data>';
        foreach ($data as $key=>$val)
        {
            if($val){
                if (is_numeric($val)){
                    $xml.="<".$key.">".$val."</".$key.">";
                }else{
                    $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
                }
            }else{
                $xml.="<".$key."></".$key.">";
            }

        }
        $xml.="</data></request>";
        return $xml;
    }
}