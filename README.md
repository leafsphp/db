<!-- markdownlint-disable no-inline-html -->
<p align="center">
  <br><br>
  <img src="https://leafphp.netlify.app/assets/img/leaf3-logo.png" height="100"/>
  <h1 align="center">Leaf Db v3</h1>
  <br><br>
</p>

[![Latest Stable Version](https://poser.pugx.org/leafs/db/v/stable)](https://packagist.org/packages/leafs/db)
[![Total Downloads](https://poser.pugx.org/leafs/db/downloads)](https://packagist.org/packages/leafs/db)
[![License](https://poser.pugx.org/leafs/db/license)](https://packagist.org/packages/leafs/db)

Leaf DB has gone through yet another re-write. This time, Leaf DB focuses on maintaining a cleaner structure with more usable and grounded code. v3 supports more databases like postgres and sqlite, comes with some performance gains and is far more efficient than v1 and v2. It is also independent of the leaf core which makes it suitable for any project you run.

## What's new?

### DB Support

Leaf DB now supports connections with other databases like postgresql, sqlite, oracle and more.

### Deep syncing with leaf 3

Leaf DB is now detached from leaf, however, as a leaf 3 module, there's additional functionality you can get from using leaf db in a leaf 3 app. Deep syncing config, instances and functional mode all become available to you.

### PDO rewrite

Under the hood, Leaf DB has been rewritten to fully support PDO, both internally and user instantiated PDO instances. This makes leaf db more flexible and more compatible with most systems and applications.

### Performance Improvements

After a series of benchmarks with ApacheBench, apps using leaf db v3 were almost twice as fast as apps using the prior version. These small performance wins can go a long way to improve the overall perfomance of your app drastically.

### Methods

- `create`
- `drop`
- `insert` with multiple fields
- Connections with pgsql, oracle, sqlite and many more db types
- Functional mode

## Installation

You can easily install Leaf using [Composer](https://getcomposer.org/).

```bash
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

If you're using leaf db in a leaf 3 app, you will have access to the `db` global

```php
db()->connect('127.0.0.1', 'dbName', 'user', 'password');
```

From there, you can use any db method.

```php
$users = db()->select('users')->all();
```

You can find leaf db's complete documentation [here](https://leafphp.dev/modules/db/). **The docs are still being updated.**
