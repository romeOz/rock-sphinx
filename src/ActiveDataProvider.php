<?php

namespace rock\sphinx;

/**
 * ActiveDataProvider is an enhanced version of {@see \rock\db\common\ActiveDataProvider} specific to the Sphinx.
 * It allows to fetch not only rows and total rows count, but also a meta information and facet results.
 *
 * The following is an example of using ActiveDataProvider to provide facet results:
 *
 * ```php
 * $provider = new ActiveDataProvider([
 *     'query' => Post::find()->facets(['author_id', 'category_id']),
 *     'pagination' => [
 *         'pageSize' => 20,
 *     ],
 * ]);
 *
 * // get the posts in the current page
 * $posts = $provider->getModels();
 *
 * // get all facets
 * $facets = $provider->getFacets();
 *
 * // get particular facet
 * $authorFacet = $provider->getFacet('author_id');
 * ```
 *
 * In case {@see \rock\sphinx\Query::$showMeta} is set ActiveDataProvider will fetch total count value from the query meta information,
 * avoiding extra counting query:
 *
 * ```php
 * $provider = new ActiveDataProvider([
 *     'query' => Post::find()->showMeta(true),
 *     'pagination' => [
 *         'pageSize' => 20,
 *     ],
 * ]);
 *
 * $totalCount = $provider->getTotalCount(); // fetched from meta information
 * ```
 *
 * Note: when using 'meta' information results total count will be fetched after pagination limit applying,
 * which eliminates ability to verify if requested page number actually exist. 
 *
 * Note: because pagination offset and limit may exceed Sphinx 'max_matches' bounds, data provider will set 'max_matches'
 * option automatically based on those values. However, if {@see \rock\sphinx\Query::$showMeta} is set, such adjustment is not performed
 * as it will break total count calculation, so you'll have to deal with 'max_matches' bounds on your own.
 *
 * @property array $meta search query meta info in format: name => value.
 * @property array $facets query facet results.
 */
class ActiveDataProvider extends \rock\db\common\ActiveDataProvider
{
    /**
     * @var array search query meta info in format: name => value.
     */
    private $_meta;
    /**
     * @var array query facet results.
     */
    private $_facets;

    /**
     * @param array $facets query facet results.
     */
    public function setFacets($facets)
    {
        $this->_facets = $facets;
    }

    /**
     * @return array query facet results.
     */
    public function getFacets()
    {
        if (!is_array($this->_facets)) {
            $this->prepareModels();
        }
        return $this->_facets;
    }

    /**
     * @param array $meta search query meta info
     */
    public function setMeta($meta)
    {
        $this->_meta = $meta;
    }

    /**
     * @return array search query meta info
     */
    public function getMeta()
    {
        if (!is_array($this->_meta)) {
            $this->prepareModels();
        }
        return $this->_meta;
    }

    /**
     * Returns results of the specified facet.
     * @param string $name facet name
     * @throws SphinxException if requested facet does not present in results.
     * @return array facet results.
     */
    public function getFacet($name)
    {
        $facets = $this->getFacets();
        if (!isset($facets[$name])) {
            throw new SphinxException("Facet '{$name}' does not present.");
        }
        return $facets[$name];
    }

    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        if (!$this->query instanceof Query) {
            throw new SphinxException('The "query" property must be an instance "' . Query::className() . '" or its subclasses.');
        }
        $query = clone $this->query;
        if (($pagination = $this->getPagination()) !== false) {
            if (empty($query->showMeta)) {
                $pagination->totalCount = $this->getTotalCount();
                $limit = $pagination->getLimit();
                $offset = $pagination->getOffset();
                // pagination may exceed 'max_matches' boundary producing query error
                if (!isset($query->options['max_matches'])) {
                    $query->options['max_matches'] = $offset + $limit;
                }
                $query->limit($limit)->offset($offset);
            } else {
                // pagination fails to validate page number, if total count is unknown at this stage
                $query->limit($pagination->getLimit())->offset($pagination->getOffset());
            }
        }

        $results = $query->search($this->connection);
        $this->setMeta($results['meta']);
        $this->setFacets($results['facets']);

        if ($pagination !== false) {
            $pagination->totalCount = $this->getTotalCount();
        }

        return $results['hits'];
    }

    /**
     * @inheritdoc
     */
    protected function prepareTotalCount()
    {
        if (!$this->query instanceof Query) {
            throw new SphinxException('The "query" property must be an instance "' . Query::className() . '" or its subclasses.');
        }

        if (!empty($this->query->showMeta)) {
            $meta = $this->getMeta();
            if (isset($meta['total_found'])) {
                return (int) $meta['total_found'];
            }
            if (isset($meta['total'])) {
                return (int) $meta['total'];
            }
        }

        $query = clone $this->query;
        return (int) $query->limit(-1)->offset(-1)->orderBy([])->facets([])->showMeta(false)->count('*', $this->connection);
    }
}