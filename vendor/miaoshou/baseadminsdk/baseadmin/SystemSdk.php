<?php
/**
 * 子系统登录SDK
 */
namespace nisiya\baseadminsdk\baseadmin;

use nisiya\baseadminsdk\CommonSdk;

class SystemSdk extends CommonSdk
{
    /**
     * 子系统验证登录token
     *
     * @param $token
     * @param $keyword
     * @return array|bool|mixed|string
     */
    public function checkToken($token, $keyword)
    {
        $params = ['token' => $token, 'keyword' => $keyword];
        $is_post = true;
        return $this->send($is_post, $params, __METHOD__);
    }

    /**
     * 子系统退出登录token
     *
     * @param $token
     * @return array|bool|mixed|string
     */
    public function checkLogOut($token)
    {
        $params = ['token' => $token];
        $is_post = true;
        return $this->send($is_post, $params, __METHOD__);
    }
}
