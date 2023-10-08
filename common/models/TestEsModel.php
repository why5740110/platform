<?php

namespace common\models;

use common\models\EsBase;

class TestEsModel extends EsBase
{
    public $index;
    public $type;
    public $routing;

    public function __construct()
    {
        $db            = (\Yii::$app->get('elasticsearch')->nodes) ?? [];
        $auth          = (\Yii::$app->get('elasticsearch')->auth) ?? [];
        $this->hosts   = array_column($db, 'http_address');
        $this->username   = $auth['username'] ?? '';
        $this->password   = $auth['password'] ?? '';
        $this->index   = 'hospital_henan_index';
        $this->routing = 1;

        parent::__construct();

        $this->mapping = [
            'properties' => [
                'citycode'                   => ['type' => 'integer'], ##医院id
                'hosname'                     => ['type' => 'keyword'], ##医生id
                'hosid'               => ['type' => 'integer'], ##医生姓名
            ],
        ];
    }

    # 属性
    public function attributes()
    {
        return array_keys($this->mapping['properties']);
        // $mapConfig = $this->getEsMapping();
        // return array_keys($mapConfig[$this->index]['mappings']['properties']);
    }

    /**
     * Set (update) mappings for this model
     */
    public static function updateMapping()
    {
        return static::getInstance()->updateEsMapping();
    }

    /**
     * @return mixed
     */
    public static function getMapping()
    {
        return static::getInstance()->getEsMapping();
    }

    public function selectEs($where = [], $page = 1, $pagesize = 10)
    {
        $offset       = max(($page - 1), 0) * $pagesize;
        $joinWhere = [
            "bool" => [
                'must' => [
                    [
                        'match' => [
                            'hosname' => '洛阳市第五人民医院',
                        ],
                    ],
                ],
            ],
        ];
        $res = self::search_find($joinWhere, $offset, $pagesize, $order);
        return $res;
    }
}