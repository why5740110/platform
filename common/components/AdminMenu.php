<?php

/**
 * 后台菜单列表
 * @file AdminMenu.php
 * @author lizhanghu <lizhanghu@yuanxin-inc.com>
 * @version 1.0
 * @date 2018-11-28
 */
namespace common\components;

class AdminMenu
{
    /**
     * 超级管理员角色
     */
    const USER_ROLE_ADMIN = 1;

    /**
     * 菜单数组列表
     * @var array
     */
    public static $menuList = [
        [
            'name' => '系统首页', 'controller' => ['site'], 'action' => 'site/index', 'submenu' => []
        ]
    ];

    /**
     * 获取用户权限下的菜单
     * @param  array $accessList 权限列表
     * @param  int $level 用户角色 1超管
     * @param  array $subMenu 子菜单数组
     * @author lizhanghu <lizhanghu@yuanxin-inc.com>
     * @date 2018-11-28
     * @return array
     */
    public static function getUserMenu($accessList, $level, $subMenu = false)
    {

        //如果是管理员则返回全部菜单
        if ($level == self::USER_ROLE_ADMIN) {
            return self::$menuList;
        }

        //如果是地柜调用则重新赋值
        if (!$subMenu) {
            $returnMenu = self::$menuList;
        } else {
            $returnMenu = $subMenu;
        }

        foreach ($returnMenu as $key => $item) {

            //删除权限不存在的菜单
            if ($item['action'] && !in_array($item['action'], $accessList)) {
                unset($returnMenu[$key]);
                continue;
            }

            //如果有子菜单则递归
            if (!empty($item['submenu'])) {
                $returnMenu[$key]['submenu'] = self::getUserMenu($accessList, $level, $item['submenu']);
            }

            //如果递归后没有子菜单则删除当前key
            if (empty($returnMenu[$key]['submenu']) && empty($returnMenu[$key]['action'])) {
                unset($returnMenu[$key]);
            }

        }

        return $returnMenu;

    }
}
