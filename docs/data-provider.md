Active data provider
----------------------

ActiveDataProvider provides data by performing Sphinx Search queries using `\rock\sphinx\Query` and `\rock\sphinx\ActiveQuery`.

Example model:

```php
class ArticlesIndex extends \rock\sphinx\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function indexName()
    {
        return 'articles_index';
    }
    
    /**
     * @return \rock\sphinx\ArticleIndexQuery
     */
    public static function find()
    {
        return new ArticleIndexQuery(get_called_class());
    }

    public function getSourceArticlesDbLink()
    {
        return $this->hasOne(ArticlesDb::className(), ['id' => 'id', 'author_id' => 'author_id']);
    }
}
```

The following is an example of using it to provide ActiveRecord instances:

```php
$attribute = 'title'; 
$highlight = 'about';
$optionsSnippet = [
  'limit' => 1000,
  'before_match' => '<span>',
  'after_match' => '</span>'
];
$optionsPagination = [
    'limit' => 20,
    'sort' => SORT_DESC,
    'pageLimit' => 5,
    'pageCurrent' => (int)$_GET['page'],
];

$provider = new ActiveDataProvider([
   'query' => ArticlesDb::find()->orderBy('id ASC'),
   'model' => ArticlesIndex::className(),
   'callSnippets' => [
       $attribute => [$highlight, $optionsSnippet],
   ],
   'pagination' => $optionsPagination
]);

$provider->get(); // returns list items in the current page
$provider->getPagination(); // returns data pagination
```

Use link to DB Model: 

```php
$provider = new ActiveDataProvider([
   'query' => ArticlesIndex::find()->match('about')->with('articlesDbLink'),
   'callSnippets' => [
        $attribute => [$highlight, $optionsSnippet],
   ],
   'pagination' => $optionsPagination
]);
```

And the following example shows how to use ActiveDataProvider without ActiveRecord:

```php
$config = [
    'query' => (new \rock\db\Query())->from('articles'), // DB
    'model' => ArticlesIndex::className(), // sphinx model
    'callSnippets' => [
        $attribute =>
            [
                $highlight,
                $optionsSnippet
            ],
    ],
    'pagination' => 
];
$provider = new ActiveDataProvider($config);

$provider->get(); // returns list items in the current page
$provider->getPagination(); // returns data pagination
```

Array data provider
-------------------

ArrayDataProvider implements a data provider based on a data array.

```php
$link = 'articlesDbLink';
$items = ArticlesIndex::find()->match('about')->with($link)->asArray()->all();
$provider = new ActiveDataProvider([
   'array' => $items,
   'model' => ArticleIndex::className(),
   'with' => $link,
   'callSnippets' => [
        $attribute => [$highlight, $optionsSnippet],
   ],
   'pagination' => $optionsPagination
]);

$provider->get(); // returns list items in the current page
$provider->getPagination(); // returns data pagination
```