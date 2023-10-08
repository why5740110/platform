<?php
/**
 * @file HdfMessageModel.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/11/27
 */


namespace common\models;


class HdfMessageModel extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tb_hdf_message}}';
    }
}