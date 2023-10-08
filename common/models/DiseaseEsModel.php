<?php

namespace common\models;

use common\models\EsBase;

class DiseaseEsModel extends EsBase
{

    public $index;
    public $type;

    public function __construct()
    {
        $db            = (\Yii::$app->get('elasticsearch')->nodes) ?? [];
        $auth          = (\Yii::$app->get('elasticsearch')->auth) ?? [];
        $this->hosts   = array_column($db, 'http_address');
        $this->username   = $auth['username'] ?? '';
        $this->password   = $auth['password'] ?? '';
        $this->index = 'hospital_disease_index';

        parent::__construct();

        $this->mapping = [
            'properties' => [
                'disease_id'           => ['type' => 'integer'],
                'disease_name'         => ['type' => 'text', 'analyzer' => 'ik_max_word'], ##疾病名称分词
                'disease_keyword'      => ['type' => 'keyword'], ##疾病名称不分词
                'initial'              => ['type' => 'keyword'], ##疾病首字母
                'pinyin'               => ['type' => 'keyword'], ##疾病拼音
                'frist_department_id'  => ['type' => 'text', 'analyzer' => 'ik_max_word'], //一级科室id
                'second_department_id' => ['type' => 'text', 'analyzer' => 'ik_max_word'], //二级科室id
                'status'               => ['type' => 'byte'], ##医院状态'状态 0 正常 1未审核',
            ],
        ];
    }

    # 属性
    public function attributes()
    {
        $mapConfig = $this->getEsMapping();
        return array_keys($mapConfig[$this->index]['mappings']['properties']);
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

}
