<?php

namespace rockunit\models;


class TagDb extends \rockunit\db\models\ActiveRecord
{
    public static function tableName()
    {
        return 'sphinx_tag';
    }
}
