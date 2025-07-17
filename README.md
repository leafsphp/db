<!-- markdownlint-disable no-inline-html -->
<p align="center">
  <br><br>
  <img src="https://leafphp.netlify.app/assets/img/leaf3-logo.png" height="100"/>
  <h1 align="center">Leaf Db v4</h1>
  <br><br>
</p>

[![Latest Stable Version](https://poser.pugx.org/leafs/db/v/stable)](https://packagist.org/packages/leafs/db)
[![Total Downloads](https://poser.pugx.org/leafs/db/downloads)](https://packagist.org/packages/leafs/db)
[![License](https://poser.pugx.org/leafs/db/license)](https://packagist.org/packages/leafs/db)

Leaf DB is a simple, lightweight and easy to use database module for PHP. It gives you a simple interface to connect to your database and perform basic operations like creating tables, inserting data, selecting data and more without the need for complex ORMs or database libraries. Leaf DB is built on top of PDO, so it supports all the features of PDO and is compatible with most databases.

## Installation

You can easily install Leaf using [Composer](https://getcomposer.org/).

```bash
leaf install db
# or with composer
composer require leafs/db
```

## Basic usage

After installing leaf db, you need to connect to your database to use any of the db functions.

```php
$db = new Leaf\Db('127.0.0.1', 'dbName', 'user', 'password');

# or

$db = new Leaf\Db();
$db->connect('127.0.0.1', 'dbName', 'user', 'password');
```

If you're using leaf db in a leaf 4 app, you will have access to the `db` global

```php
db()->connect('127.0.0.1', 'dbName', 'user', 'password');
```

*This is done automatically if you are using Leaf MVC.*

----

From there, you can use any db method.

```php
$users = db()->select('users')->all();
```

Checkout [leaf db's complete documentation](https://leafphp.dev/docs/database/).
