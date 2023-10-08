<?php

namespace common\models;

use common\models\EsBase;

class SearchEsModel extends EsBase
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
        $this->index   = 'guahao_hospital_doctor_index,hospital_disease_index';

        parent::__construct();

    }


}
