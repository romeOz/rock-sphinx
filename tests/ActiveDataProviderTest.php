<?php

namespace rockunit;

use rock\sphinx\ActiveDataProvider;
use rock\sphinx\Query;
use rockunit\models\ActiveRecord;
use rockunit\models\ArticleIndex;
use rockunit\db\models\ActiveRecord as ActiveRecordDb;
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
        ActiveRecord::$connection = $this->getConnection(false);
        ActiveRecordDb::$connection = $this->getDbConnection();
        $_SERVER['QUERY_STRING'] = '';
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        $_SERVER['QUERY_STRING'] = '';
    }


    /**
     * @dataProvider providerQuery
     * @param int $page
     * @param int $id
     */
    public function testQuery($page, $id)
    {
        $_SERVER['QUERY_STRING'] = "page=$page";
        $provider = new ActiveDataProvider([
            'connection' => $this->getConnection(false),
            'query' =>  (new Query())->from('article_index'),
            'pagination' => ['limit' => 1, 'sort' => SORT_DESC]
        ]);

        $this->assertSame(1, $provider->getCount());
        $this->assertSame(2, $provider->getTotalCount());
        $this->assertSame(2, $provider->getPagination()->getTotalCount());
        $this->assertSame($page, $provider->getPagination()->getPageCurrent());
        $this->assertNotEmpty($provider->getPagination()->toArray());
        $this->assertSame($id, $provider->getModels()[0]['id']);
    }

    public function providerQuery()
    {
        return [
            [1, 2],
            [2, 1]
        ];
    }

    /**
     * @dataProvider providerActiveQuery
     * @param int $page
     * @param string $content
     */
    public function testActiveQuery($page, $content)
    {
        $_SERVER['QUERY_STRING'] = "page=$page";
        $snippetCallback = function ($rows){
            $result = [];
            foreach ($rows as $row) {
                $row = $row['source'];
                $result[] = ['source.title' => $row['title'], 'source.content' => $row['content']];
            }
            return $result;
        };
        $snippetOptions = [
            //'limit' => 1000,
            'before_match' => '<span>',
            'after_match' => '</span>'
        ];

        $provider = new ActiveDataProvider([
            'query' => ArticleIndex::find()
                ->snippetCallback($snippetCallback)
                ->snippetOptions($snippetOptions)
                ->with('source')
                ->match('article')
                ->asArray(),

            'pagination' => ['limit' => 1, 'sort' => SORT_DESC]
        ]);
        $this->assertSame(1, $provider->getCount());
        $this->assertSame(2, $provider->getTotalCount());
        $this->assertEquals($content, $provider->getModels()[0]['source']['content']);
        $this->assertSame(2, $provider->getPagination()->getTotalCount());
        $this->assertSame($page, $provider->getPagination()->getPageCurrent());
        $this->assertNotEmpty($provider->getPagination()->toArray());

        // as models

        $provider = new ActiveDataProvider([
            'query' => ArticleIndex::find()
                ->snippetCallback($snippetCallback)
                ->snippetOptions($snippetOptions)
                ->with('source')
                ->match('article'),

            'pagination' => ['limit' => 1, 'sort' => SORT_DESC]
        ]);
        $this->assertSame(1, $provider->getCount());
        $this->assertSame(2, $provider->getTotalCount());
        $this->assertEquals($content, $provider->getModels()[0]['snippet']['source.content']);
        $this->assertEquals($content, $provider->getModels()[0]['source']['content']);
        $this->assertSame(2, $provider->getPagination()->getTotalCount());
        $this->assertSame($page, $provider->getPagination()->getPageCurrent());
        $this->assertNotEmpty($provider->getPagination()->toArray());
    }

    public function providerActiveQuery()
    {
        return [
            [1, 'This <span>article</span> is about dogs'],
            [2, 'This <span>article</span> is about cats']
        ];
    }

    /**
     * @depends testQuery
     */
    public function testFacetQuery()
    {
        $query = new Query();
        $query->from('article_index');
        $query->facets([
            'author_id'
        ]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'connection' => $this->getConnection(),
        ]);
        $models = $provider->getModels();
        $this->assertEquals(2, count($models));
        $this->assertEquals(2, count($provider->getFacet('author_id')));
    }

    /**
     * @depends testQuery
     */
    public function testTotalCountFromMeta()
    {
        $query = new Query();
        $query->from('article_index');
        $query->showMeta(true);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'connection' => $this->getConnection(),
            'pagination' => [
                'limit' => 1
            ]
        ]);
        $models = $provider->getModels();
        $this->assertEquals(1, count($models));
        $this->assertEquals(2, $provider->getTotalCount());
    }

    /**
     * @dataProvider providerAutoAdjustMaxMatches
     * @param int $page
     * @param int $id
     */
    public function testAutoAdjustMaxMinMatches($page, $id)
    {
        $_SERVER['QUERY_STRING'] = "page=$page";
        $query = new Query();
        $query->from('article_index');
        $query->orderBy(['id' => SORT_ASC]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'connection' => $this->getConnection(),
            'pagination' => [
                //'pageLimit' => 100,
                'limit' => 1
            ]
        ]);
        $this->assertEquals($id, $provider->getModels()[0]['id']);
    }

    public function providerAutoAdjustMaxMatches()
    {
        return [
            [9999, 2],
            [-1, 1]
        ];
    }

    public function testActiveQuerySort()
    {
        $provider = new ActiveDataProvider([
            'connection' => $this->getConnection(false),
            'query' =>  (new Query())->from('article_index'),
            'sort' => [
                'attributes' => ['id'],
            ],
            'pagination' => ['limit' => 1, 'sort' => SORT_DESC]
        ]);

        $this->assertEquals(1, $provider->getModels()[0]['id']);

        $_SERVER['QUERY_STRING'] = 'sort=-id';
        $provider = new ActiveDataProvider([
            'connection' => $this->getConnection(false),
            'query' =>  (new Query())->from('article_index'),
            'sort' => [
                'attributes' => ['id'],
            ],
            'pagination' => ['limit' => 1, 'sort' => SORT_DESC]
        ]);

        $this->assertEquals(2, $provider->getModels()[0]['id']);
    }
}