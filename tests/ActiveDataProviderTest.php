<?php

namespace rockunit;

use rock\sphinx\ActiveDataProvider;
use rockunit\models\ActiveRecord;
use rockunit\models\ArticleDb;
use rockunit\models\ArticleIndex;

/**
 * @group search
 * @group sphinx
 * @group db
 */
class ActiveDataProviderTest extends SphinxTestCase
{
    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection(false);
    }


    protected $optionsSnippet = [
        'limit' => 1000,
        'before_match' => '<span>',
        'after_match' => '</span>'
    ];

    public function testQuery()
    {
        $config = [
            'connection' => $this->getDbConnection(false),
            'query' => (new \rock\db\Query())->from('sphinx_article'),
            'model' => ArticleIndex::className(),
            'callSnippets' => [
                'content' =>
                    [
                        'about',
                        $this->optionsSnippet
                    ],

            ],
            'pagination' => ['limit' => 1, 'sort' => SORT_DESC]
        ];
        $provider = (new ActiveDataProvider($config));
        $this->assertSame(
            $provider->get()[0]['content'],
            'This article is <span>about</span> cats'
        );
        $this->assertSame(count($provider->get()), 1);
        $this->assertNotEmpty($provider->getPagination()->toArray());
        $this->assertSame($provider->getTotalCount(), 2);
    }

    public function testActiveQuery()
    {
        ArticleDb::$connection = $this->getDbConnection(false);
        $provider = new ActiveDataProvider([
            'query' => ArticleDb::find()->orderBy('id ASC')->asArray(),
            'model' => ArticleIndex::className(),
            'callSnippets' => [
               'content' =>
                   [
                       'about',
                       $this->optionsSnippet
                   ],

            ],
            'pagination' => ['limit' => 1, 'sort' => SORT_DESC]
        ]);
        $this->assertSame(
            $provider->get()[0]['content'],
            'This article is <span>about</span> cats'
        );
        $this->assertSame(count($provider->get()), 1);
        $this->assertNotEmpty($provider->getPagination()->toArray());
        $this->assertSame($provider->getTotalCount(), 2);

        $provider = new ActiveDataProvider([
           'query' => ArticleDb::find()->orderBy('id ASC')->indexBy('id'),
           'model' => ArticleIndex::className(),
           'callSnippets' => [
               'content' =>
                   [
                       'about',
                       $this->optionsSnippet
                   ],

           ],
           'pagination' => ['limit' => 1, 'sort' => SORT_DESC]
        ]);

        $this->assertSame(
            $provider->get()[1]['content'],
            'This article is <span>about</span> cats'
        );
        $this->assertSame(count($provider->get()), 1);
        $this->assertNotEmpty($provider->getPagination()->toArray());
        $this->assertSame($provider->getTotalCount(), 2);

        $provider = new ActiveDataProvider([
           'query' => ArticleIndex::find()->match('about')->with('sourceCompositeLink')->indexBy('id'),
           //'model' => ArticleIndex::className(),
           //'with' => 'sourceCompositeLink',
           'callSnippets' => [
               'content' =>
                   [
                       'about',
                       $this->optionsSnippet
                   ],

           ],
           'pagination' => ['limit' => 1, 'sort' => SORT_DESC]
        ]);

        $this->assertSame(
            $provider->get()[1]['sourceCompositeLink']['content'],
            'This article is <span>about</span> cats'
        );

        $this->assertSame(count($provider->get()), 1);
        $this->assertNotEmpty($provider->getPagination()->toArray());
        $this->assertSame($provider->getTotalCount(), 2);
    }

    public function testArray()
    {
        ArticleDb::$connection = $this->getDbConnection(false);
        $array = ArticleIndex::find()->match('about')->with('sourceCompositeLink')->indexBy('id')->asArray()->all();
        $provider = new ActiveDataProvider([
           'array' => $array,
           'model' => ArticleIndex::className(),
           'with' => 'sourceCompositeLink',
           'only' => ['sourceCompositeLink'],
           'callSnippets' => [
               'content' =>
                   [
                       'about',
                       $this->optionsSnippet
                   ],

           ],
           'pagination' => ['limit' => 1, 'sort' => SORT_DESC]
        ]);

        $this->assertSame(
            $provider->get()[1]['sourceCompositeLink']['content'],
            'This article is <span>about</span> cats'
        );

        $this->assertSame(count($provider->get()), 1);
        $this->assertNotEmpty($provider->getPagination()->toArray());
        $this->assertSame($provider->getTotalCount(), 2);
        $this->assertSame(count(current($provider->toArray())),1);
    }
}
