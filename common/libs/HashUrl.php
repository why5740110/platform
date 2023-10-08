<?php
/**
 * 医院/医生 id加密
 * @file HashUrl.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/7/25
 */


namespace common\libs;


class HashUrl
{
    //加盐值
    const SALT = "doc*3Z#Dm6e1hosp";

    /*加盐值*/
    const QUES_SALT = "SGGYXKJ2018LM";

    const GUAHAO_TP_SALT = "MS1rKe4G0*bfKolC";

    /**
     * @param $id
     * @return string
     * @author xiujianying
     * @date 2020/7/25
     */
    public static function getIdEncode($id)
    {
        $hashIds = new \Hashids\Hashids(self::SALT, 10);
        $encode_id = $hashIds->encode($id);
        return $encode_id;
    }


    /**
     * @param $id_hash_str
     * @return int|mixed
     * @author xiujianying
     * @date 2020/7/25
     */
    public static function getIdDecode($id_hash_str)
    {
        if (empty($id_hash_str)) {
            return 0;
        }

        $hashIds = new \Hashids\Hashids(self::SALT, 10);
        $id = $hashIds->decode($id_hash_str);
        return isset($id[0]) ? $id[0] : 0;
    }

    /**
     * 通过问答ID获取问答详情页地址
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2018-10-29
     * @param int $question_id 问答ID
     * @return string
     */
    public static function getQuestionDetailUrl($question_id)
    {
        $question_id = intval($question_id);
        if (empty($question_id)) {
            return '/';
        }
        $hashIds = new \Hashids\Hashids(self::QUES_SALT, 16);
        $encode_id = $hashIds->encode($question_id);
        return 'question/' . $encode_id . '.html';
    }

    /**
     * 根据问答ID获取ID hash字符串
     * @author niewei <niewei@yuanxin-inc.com>
     * @date 2018-10-29
     */
    public static function getQuestionIdEncode($id)
    {
        $hashIds = new \Hashids\Hashids(self::QUES_SALT, 16);
        $encode_id = $hashIds->encode($id);
        return $encode_id;
    }

    /**
     * 根据ID has字符串 返回数字ID
     * @author niewei <niewei@yuanxin-inc.com>
     * @date 2018-10-29
     * @param $id_has_str
     */
    public static function getQuestionIdDecode($id_hash_str)
    {
        if(empty($id_hash_str)){
            return 0;
        }

        $hashIds = new \Hashids\Hashids(self::QUES_SALT, 16);
        $id = $hashIds->decode($id_hash_str);
        return isset($id[0]) ? $id[0] : 0;
    }

    /**
     * 挂号排班ID加密
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-13
     * @version 1.0
     * @param   [type]     $id [description]
     * @return  [type]         [description]
     */
    public static function getGuahaoTpIdEncode($id)
    {
        $hashIds = new \Hashids\Hashids(self::GUAHAO_TP_SALT, 10);
        $encode_id = $hashIds->encode($id);
        return $encode_id;
    }

    /**
     * 挂号排班ID解密
     * @author yangquanliang <yangquanliang@yuanxin-inc.com>
     * @date    2021-01-13
     * @version 1.0
     * @param   [type]     $id_hash_str [description]
     * @return  [type]                  [description]
     */
    public static function getGuahaoTpIdDecode($id_hash_str)
    {
        $hashIds = new \Hashids\Hashids(self::GUAHAO_TP_SALT, 10);
        $id = $hashIds->decode($id_hash_str);
        return isset($id[0]) ? $id[0] : 0;
    }

}
