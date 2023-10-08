<?php
/**
 * 菜单组件
 * @file menu.php
 */

namespace common\components;

use yii\base\Component;
use Yii;

class Menu extends Component
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * 获取顶部一级菜单
     * @return array
     */
    public static function getTop($data=[])
    {
        if($data) {
            foreach ($data as $item) {
                if ($item['fid'] == 0) {
                    $top[] = $item;
                }
            }
            return $top;
        }
        return false;
    }

    /**
     * 获取子集菜单
     * @param $arrCat
     * @param int $parent_id
     * @return mixed
     */
    public static function getMenuTree($arrCat, $parent_id = 0)
    {
        foreach ($arrCat as $key => $item) {
            if ($item['id'] == $parent_id) {
                $arrTree = $item;
            }
        }

        foreach ($arrTree['child'] as &$item) {
            $item['top_id'] = $parent_id;
        }
        return $arrTree['child'];
    }

    /**
     * 当前url
     * @param $data
     * @param $route
     * @return bool
     */
    public static function getCurrentUrl($data, $route)
    {
        if($data){
            foreach ($data as $item) {
                if ($item['url'] == $route) {
                    return $item['id'];
                }
            }
        }
        return false;
    }

}