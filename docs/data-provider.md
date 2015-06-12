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

    public function getSource()
    {
        return $this->hasOne(ArticlesDb::className(), ['id' => 'id']);
    }
}
```

The following is an example of using it to provide ActiveRecord instances:

```php
$provider = new ActiveDataProvider([
    'query' => ArticleIndex::find()->match('foo')->asArray(),
    'pagination' => ['limit' => 1, 'sort' => SORT_DESC, 'page' => (int)$_GET['page']]
]);

$provider->getModels(); // returns list items in the current page
$provider->getPagination(); // returns pagination provider
```

Use link to DB Model: 

```php
$snippetCallback = function ($rows){
    $result = [];
    foreach ($rows as $row) {
        $row = $row['source'];
        $result[] = ['source.title' => $row['title'], 'source.content' => $row['content']];
    }
    return $result;
};
$snippetOptions = [
    'before_match' => '<span>',
    'after_match' => '</span>'
];

$query = ArticleIndex::find()
                 ->snippetCallback($snippetCallback)
                 ->snippetOptions($snippetOptions)
                 ->with('source')
                 ->match('foo')
                 ->asArray();

$provider = new ActiveDataProvider([
    'query' => $query,
    'pagination' => ['limit' => 1, 'sort' => SORT_DESC, 'page' => (int)$_GET['page']]
]);

$provider->getModels(); // returns list items in the current page
$provider->getPagination(); // returns pagination provider
```

And the following example shows how to use ActiveDataProvider without ActiveRecord:

```php
$provider = new ActiveDataProvider([
    'query' =>  (new Query())->from('article_index')->match('foo'),
    'pagination' => ['limit' => 1, 'sort' => SORT_DESC, 'page' => (int)$_GET['page']]
]);

$provider->getModels(); // returns list items in the current page
$provider->getPagination(); // returns pagination provider
```