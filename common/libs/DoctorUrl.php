<?php
/**
 * 医生公共URL地址静态方法类
 * @file DoctorUrl.php
 * @author yueyuchao <yueyuchao@yuanxin-inc.com>
 * @version 1.0
 * @date 2019-4-18
 */
namespace common\libs;

use Yii;

class DoctorUrl
{
    //加盐值
    const SALT = "WiT*3#GaeH5LI7F-";
    //起始id
    public static $IdStart;

    /**
     * 获取医生起始加密id
     * @return mixed
     */
    private static function hashIdStart()
    {
        self::$IdStart = Yii::$app->params['hashDoctorIDStart'];
        return self::$IdStart;
    }

    /**
     * 通过医生ID获取医生主页地址
     * @author yueyuchao <yueyuchao@yuanxin-inc.com>
     * @date 2019-4-18
     * @param int $doctor_id 医生ID
     * @return string
     */
    public static function getDoctorUrl($doctor_id)
    {
        $hashIdStart = self::hashIdStart();
        $doctor_id = intval($doctor_id);
        if ($doctor_id >= $hashIdStart) {
            if (empty($doctor_id)) {
                return '/';
            }
            $hashIds = new \Hashids\Hashids(self::SALT, 16);
            $encode_id = $hashIds->encode($doctor_id);
        } else {
            $encode_id = $doctor_id;
        }
        return '/doctor/' . $encode_id . '.html';
    }

    /**
     * 根据医生ID 获取加密后的hash字符串
     * @author yueyuchao <yueyuchao@yuanxin-inc.com>
     * @date 2019-4-18
     */
    public static function getIdEncode($doctor_id)
    {
        $hashIdStart = self::hashIdStart();
        $encode_id = $doctor_id;
        if ($doctor_id >= $hashIdStart) {
            $hashIds = new \Hashids\Hashids(self::SALT, 16);
            $encode_id = $hashIds->encode($doctor_id);
        }
        return $encode_id;
    }

    /**
     * 根据加密后的医生ID has字符串 返回真实的医生ID
    * @author yueyuchao <yueyuchao@yuanxin-inc.com>
     * @date 2019-4-18
     * @param $id_has_str
     */
    public static function getIdDecode($doctorHashStr)
    {
        $hashIdStart = self::hashIdStart();
        if(empty($doctorHashStr)){
            return 0;
        }

        if (strval(intval($doctorHashStr)) == $doctorHashStr && $doctorHashStr > 0 && $doctorHashStr < $hashIdStart) {
            return $doctorHashStr;
        }

        $hashIds = new \Hashids\Hashids(self::SALT, 16);
        $id = $hashIds->decode($doctorHashStr);
        //判断解密出来的医生ID是否  大于等于  配置文件中医生加密起始ID
        if (isset($id[0]) && $id[0] >= $hashIdStart) {
            return $id[0];
        } else {
            return 0;
        }
    }

}