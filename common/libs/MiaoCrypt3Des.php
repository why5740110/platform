<?php
/**
 *  3Des 加密 (阿里健康 h5 对接时参数加解密)
 * @file MiaoCrypt3Des.php
 * @author liuyingwei <liuyingwei@yuanxinjituan.com>
 * @version 1.0
 * @date 2022-02-28
 */
namespace common\libs;

use yii\helpers\ArrayHelper;

class MiaoCrypt3Des {

    private $_key = ""; // 这个待确定约定的key

    public function __construct($key=null)
    {
        $this->_key = ArrayHelper::getValue(\Yii::$app->params, 'ali_healthy.crypt_key');
    }

    public function encrypt($str)
    {
        $td = $this->gettd();
        $ret = @mcrypt_generic($td, $this->pkcs5_pad($str, 8));
        @mcrypt_generic_deinit($td);
        @mcrypt_module_close($td);
        return $this->strToHex($ret);
    }

    public function decrypt($encrypted){
        $encrypted = str_replace(" ", "+", $encrypted);
        $encrypted = base64_decode($encrypted);
        $key = str_pad($this->_key,24,'0');
        $td = @mcrypt_module_open(MCRYPT_3DES,'','ecb','');
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);
        $ks = @mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        $decrypted = @mdecrypt_generic($td, $encrypted);
        @mcrypt_generic_deinit($td);
        @mcrypt_module_close($td);
        $y=$this->pkcs5_unpad($decrypted);
        return $y;
    }

    private function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }

    private function getiv()
    {
        return pack('H16', '0000000000000000');
    }

    private function gettd()
    {
        $iv = $this->getiv();
        $td = @mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        @mcrypt_generic_init($td, $this->_key, $iv);
        return $td;
    }

    private function strToHex($string)
    {
        $hex = '';
        for ($i=0; $i<strlen($string); $i++){
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0'.$hexCode, -2);
        }
        return strToUpper($hex);
    }

    private function hexToStr($hex)
    {
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }
}
