<?php

namespace rockunit\models;

use rockunit\db\models\ActiveRecord as ActiveRecordDB;

class ArticleDb extends ActiveRecordDB
{
    public static function tableName()
    {
        return 'sphinx_article';
    }

    public function getIndex()
    {

        return $this->hasOne(ArticleIndex::className(), ['id' => 'id']);
//        return new \rock\sphinx\ActiveQuery(ArticleIndex::className(), [
//            'primaryModel' => $this,
//            'link' => ['id' => 'id'],
//            'multiple' => false,
//        ]);
    }
}
