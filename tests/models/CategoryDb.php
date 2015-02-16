<?php

namespace rockunit\models;

class CategoryDb extends \rockunit\db\models\ActiveRecord
{
    public static function tableName()
    {
        return 'sphinx_category';
    }
}
