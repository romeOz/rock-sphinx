ORM for Sphinx Search.
=======================

This API [Sphinx](http://sphinxsearch.com/docs) full text search engine, which uses [SphinxQL](http://sphinxsearch.com/docs/current.html#sphinxql-reference).

Independent fork by [Yii2 Sphinx Search](https://github.com/yiisoft/yii2/tree/master/extensions/sphinx).

[![Latest Stable Version](https://poser.pugx.org/romeOz/rock-sphinx/v/stable.svg)](https://packagist.org/packages/romeOz/rock-sphinx)
[![Total Downloads](https://poser.pugx.org/romeOz/rock-sphinx/downloads.svg)](https://packagist.org/packages/romeOz/rock-sphinx)
[![Build Status](https://travis-ci.org/romeOz/rock-sphinx.svg?branch=master)](https://travis-ci.org/romeOz/rock-sphinx)
[![HHVM Status](http://hhvm.h4cc.de/badge/romeoz/rock-sphinx.svg)](http://hhvm.h4cc.de/package/romeoz/rock-sphinx)
[![Coverage Status](https://coveralls.io/repos/romeOz/rock-sphinx/badge.svg?branch=master)](https://coveralls.io/r/romeOz/rock-sphinx?branch=master)
[![License](https://poser.pugx.org/romeOz/rock-sphinx/license.svg)](https://packagist.org/packages/romeOz/rock-sphinx)

[Rock Sphinx on Packagist](https://packagist.org/packages/romeOz/rock-sphinx)

Features
-------------------
 
 * Query Builder/DBAL/DAO: Querying the database using a simple abstraction layer
 * Active Record: The Active Record ORM, retrieving and manipulating records, and defining relations
 * Support [Runtime Indexes](http://sphinxsearch.com/docs/current.html#rt-indexes)
 * [Call Snippets](http://sphinxsearch.com/docs/current.html#sphinxql-call-snippets)
 * Behaviors (TimestampBehavior,...)
 * **Validation and Sanitization rules for AR (Model)**
 * **Query Caching**
 * **Data Providers**
 * **Module for [Rock Framework](https://github.com/romeOz/rock)**
 
> Bolded features are different from [Yii2 Sphinx Search](https://github.com/yiisoft/yii2/tree/master/extensions/sphinx).

Installation
-------------------

From the Command Line:

`composer require romeoz/rock-sphinx:*@dev`

In your composer.json:

```json
{
    "require": {
        "romeoz/rock-sphinx": "*@dev"
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
 * [Rock Cache](https://github.com/romeOz/rock-cache) **(optional)**. Should be installed: `composer require romeoz/rock-cache:*`
 * [Rock Validate](https://github.com/romeOz/rock-validate) **(optional)**. Should be installed: `composer require romeoz/rock-validate:*`
 * [Rock Sanitize](https://github.com/romeOz/rock-sanitize) **(optional)**. Should be installed: `composer require romeoz/rock-sanitize:*`
 * [Rock Behaviors](https://github.com/romeOz/rock-behaviors) **(optional)**. Should be installed: `composer require romeoz/rock-behaviors:*`

License
-------------------

The Object Relational Mapping (ORM) for Sphinx Search is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).