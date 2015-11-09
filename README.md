ORM for Sphinx Search.
=======================

This API [Sphinx](http://sphinxsearch.com/docs) full text search engine, which uses [SphinxQL](http://sphinxsearch.com/docs/current.html#sphinxql-reference).

Independent fork by [Yii2 Sphinx Search 2.0.4](https://github.com/yiisoft/yii2-sphinx).

[![Latest Stable Version](https://poser.pugx.org/romeOz/rock-sphinx/v/stable.svg)](https://packagist.org/packages/romeOz/rock-sphinx)
[![Total Downloads](https://poser.pugx.org/romeOz/rock-sphinx/downloads.svg)](https://packagist.org/packages/romeOz/rock-sphinx)
[![Build Status](https://travis-ci.org/romeOz/rock-sphinx.svg?branch=master)](https://travis-ci.org/romeOz/rock-sphinx)
[![HHVM Status](http://hhvm.h4cc.de/badge/romeoz/rock-sphinx.svg)](http://hhvm.h4cc.de/package/romeoz/rock-sphinx)
[![Coverage Status](https://coveralls.io/repos/romeOz/rock-sphinx/badge.svg?branch=master)](https://coveralls.io/r/romeOz/rock-sphinx?branch=master)
[![License](https://poser.pugx.org/romeOz/rock-sphinx/license.svg)](https://packagist.org/packages/romeOz/rock-sphinx)

Features
-------------------
 
 * Query Builder/DBAL/DAO: Querying the database using a simple abstraction layer
 * Active Record: The Active Record ORM, retrieving and manipulating records, and defining relations
 * Support [Runtime Indexes](http://sphinxsearch.com/docs/current.html#rt-indexes)
 * [Call Snippets](http://sphinxsearch.com/docs/current.html#sphinxql-call-snippets)
 * Behaviors (TimestampBehavior,...)
 * Data Provider
 * **Validation and Sanitization rules for AR (Model)**
 * **Query Caching** 
 * **Standalone module/component for [Rock Framework](https://github.com/romeOz/rock)**
 
> Bolded features are different from [Yii2 Sphinx Search](https://github.com/yiisoft/yii2-sphinx).

Installation
-------------------

From the Command Line:

```
composer require romeoz/rock-sphinx
```

In your composer.json:

```json
{
    "require": {
        "romeoz/rock-sphinx": "*"
    }
}
```

Quick Start
-------------------

####Query Builder

```php
$rows =  (new \rock\sphinx\Query)
    ->from('items_idx')
    ->match($_POST['search'])
    ->all();
```

####Active Record

```php
// find
$users = ItemsIndex::find()
    ->match($_POST['search'])
    ->all();
    
// insert to runtime index
$record = new RuntimeIndex;
$record->id = 15;
$record->name = 'Tom';
$users ->save();    
```

Documentation
-------------------

* [Basic](https://github.com/yiisoft/yii2/blob/master/extensions/sphinx/README.md): Connecting to a database, basic queries, query builder, and Active Record
* [Data Providers](https://github.com/romeOz/rock-sphinx/blob/master/docs/data-provider.md)

Requirements
-------------------

 * **PHP 5.4+**
 * For validation rules a model required [Rock Validate](https://github.com/romeOz/rock-validate): `composer require romeoz/rock-validate`
 * For sanitization rules a model required [Rock Sanitize](https://github.com/romeOz/rock-sanitize): `composer require romeoz/rock-sanitize`
 * For using behaviors a model required [Rock Behaviors](https://github.com/romeOz/rock-behaviors): `composer require romeoz/rock-behaviors`
 * For using Data Provider required [Rock Data Provider](https://github.com/romeOz/rock-dataprovider/): `composer require romeoz/rock-dataprovider`
 * For caching queries required [Rock Cache](https://github.com/romeOz/rock-behaviors): `composer require romeoz/rock-cache` 

>All unbolded dependencies is optional.

License
-------------------

The Object Relational Mapping (ORM) for Sphinx Search is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).