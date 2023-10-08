<?php
/**
 * 过滤
 * @file FilterSdk.php
 * @author shangheguang <shangheguang@yuanxin-inc.com>
 * @version 2.0
 * @date 2020-05-25
 */

namespace nisiya\branddoctorsdk\cooperation;

use nisiya\branddoctorsdk\CommonSdk;

class FilterSdk extends CommonSdk
{

    public function __construct()
    {
        $this->domain_index =  'cooperation';
        parent::__construct();
    }

    /**
     * 按分类检测敏感词接口
     * @param string $content 需要检测的内容
     * @param int $type 分类 1:标题清洗敏感词；2:公用敏感词；3:评论敏感词
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return array|bool|mixed
     */
    public function filter($content,  $type = 1)
    {
        $params = [
            'content' => $content,
            'type' => intval($type),
        ];
        return $this->sendHttpRequest($params, __METHOD__, 'post');
    }

    /**
     * 自动分科接口
     * @param string $content 要分科的内容
     * @param int $age 年龄
     * @param string $age_type ('岁','月','天')
     * @author shangheguang <shangheguang@yuanxin-inc.com>
     * @date 2020-05-25
     * @return array|bool|mixed
     */
    public function getDepartment($content, $age = 0,  $age_type = '')
    {
        $params = [
            'content' => $content,
            'age' => intval($age),
            'age_type' => $age_type,
        ];
        return $this->sendHttpRequest($params, __METHOD__, 'post');
    }


}