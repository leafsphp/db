<?php

use Leaf\Db;

test('connects to sqlite and reports the table exists', function () {
    $db = testDb();

    expect($db->tableExists('users'))->toBeTrue();
    expect($db->tableExists('ghosts'))->toBeFalse();
});

test('select fetches all rows', function () {
    $users = testDb()->select('users')->all();

    expect($users)->toHaveCount(3);
    expect($users[0]['name'])->toBe('Mika');
});

test('where filters rows with bound values', function () {
    $admins = testDb()->select('users')->where('role', 'admin')->all();

    expect($admins)->toHaveCount(1);
    expect($admins[0]['name'])->toBe('Mika');
});

test('where accepts an array of conditions', function () {
    $result = testDb()->select('users')->where(['name' => 'Darko', 'role' => 'member'])->all();

    expect($result)->toHaveCount(1);
    expect($result[0]['email'])->toBe('darko@leafphp.dev');
});

test('where with comparators and orWhere combine correctly', function () {
    $db = testDb();
    $result = $db->select('users')->where('role', '=', 'admin')->orWhere('name', 'Ama')->all();

    expect(array_column($result, 'name'))->toBe(['Mika', 'Ama']);
});

test('where in, not in and between work with bindings', function () {
    $db = testDb();

    expect($db->select('users')->where('name', 'in', ['Mika', 'Ama'])->all())->toHaveCount(2);
    expect($db->select('users')->where('name', 'not in', ['Mika'])->all())->toHaveCount(2);
    expect($db->select('users')->where('id', 'between', [2, 3])->all())->toHaveCount(2);
});

test('find, first and last fetch single rows', function () {
    $db = testDb();

    expect($db->select('users')->first()['name'])->toBe('Mika');
    expect($db->select('users')->last()['name'])->toBe('Ama');
    expect($db->select('users')->find(2)['name'])->toBe('Darko');
});

test('fetching a missing row returns an empty result instead of crashing', function () {
    $db = testDb();

    $missing = $db->select('users')->where('name', 'Nobody')->hidden('email')->first();

    expect($missing)->toBeFalse();
});

test('orderBy, limit and offset shape the result set', function () {
    $db = testDb();

    $names = array_column($db->select('users')->orderBy('name', 'asc')->all(), 'name');
    expect($names)->toBe(['Ama', 'Darko', 'Mika']);

    $paged = $db->select('users')->orderBy('id', 'asc')->limit(1)->offset(1)->all();
    expect($paged)->toHaveCount(1);
    expect($paged[0]['name'])->toBe('Darko');
});

test('insert adds rows and lastInsertId reports them', function () {
    $db = testDb();

    $db->insert('users')->params(['name' => 'Kofi', 'email' => 'kofi@leafphp.dev'])->execute();

    expect((int) $db->lastInsertId())->toBe(4);
    expect($db->select('users')->all())->toHaveCount(4);
});

test('update modifies matching rows and delete removes them', function () {
    $db = testDb();

    $db->update('users')->params(['role' => 'editor'])->where('name', 'Darko')->execute();
    expect($db->select('users')->where('name', 'Darko')->first()['role'])->toBe('editor');

    $db->delete('users')->where('name', 'Darko')->execute();
    expect($db->select('users')->all())->toHaveCount(2);
});

test('unique blocks duplicate inserts and reports errors', function () {
    $db = testDb();

    $result = $db->insert('users')
        ->params(['name' => 'Mika2', 'email' => 'mika@leafphp.dev'])
        ->unique('email')
        ->execute();

    expect($result)->toBeNull();
    expect($db->errors())->toHaveKey('email');
    expect($db->select('users')->all())->toHaveCount(3);
});

test('hidden removes fields and add appends fields to results', function () {
    $db = testDb();

    $user = $db->select('users')->where('name', 'Mika')->hidden('email')->first();
    expect($user)->not->toHaveKey('email');

    $user = $db->select('users')->where('name', 'Mika')->add('greeting', 'hello')->first();
    expect($user['greeting'])->toBe('hello');
});

test('search finds rows by partial match', function () {
    $db = testDb();

    $results = $db->table('users')->search('name', 'ik');

    expect($results)->toHaveCount(1);
    expect($results[0]['name'])->toBe('Mika');
});

test('with eager-loads related rows both ways', function () {
    $db = testDb();

    $post = $db->select('posts')->where('id', 1)->with('users', 'user_id')->first();
    expect($post['user']['name'])->toBe('Mika');

    $user = $db->select('users')->where('id', 1)->with('posts')->first();
    expect($user['posts'])->toHaveCount(2);
});

test('transactions commit on success and roll back on failure', function () {
    $db = testDb();

    $ok = $db->transaction(function ($db) {
        $db->insert('users')->params(['name' => 'Kojo', 'email' => 'kojo@leafphp.dev'])->execute();
    });

    expect($ok)->toBeTrue();
    expect($db->select('users')->all())->toHaveCount(4);

    $failed = $db->transaction(function ($db) {
        $db->insert('users')->params(['name' => 'Yaa', 'email' => 'yaa@leafphp.dev'])->execute();
        throw new Exception('something exploded');
    });

    expect($failed)->toBeFalse();
    expect($db->errors())->toHaveKey('transaction');
    expect($db->select('users')->all())->toHaveCount(4);
});

test('addConnections registers named connections and use() switches between them', function () {
    $db = new Db();

    $db->addConnections([
        'main' => ['dbtype' => 'sqlite', 'dbname' => ':memory:'],
        'analytics' => ['dbtype' => 'sqlite', 'dbname' => ':memory:'],
    ], 'main');

    $db->query('CREATE TABLE items (id INTEGER PRIMARY KEY, name TEXT)')->execute();
    $db->insert('items')->params(['name' => 'on default'])->execute();

    $db->use('analytics');
    $db->query('CREATE TABLE events (id INTEGER PRIMARY KEY, label TEXT)')->execute();
    $db->insert('events')->params(['label' => 'on analytics'])->execute();

    expect($db->use('analytics')->select('events')->all())->toHaveCount(1);
    expect($db->use(null)->select('items')->all())->toHaveCount(1);
});

test('raw queries with bind execute safely', function () {
    $db = testDb();

    $result = $db->query('SELECT * FROM users WHERE name = ?')->bind('Mika')->fetchAssoc();

    expect($result['email'])->toBe('mika@leafphp.dev');
});

test('sql injection through values is neutralised by bindings', function () {
    $db = testDb();

    $sneaky = "' OR '1'='1";
    $result = $db->select('users')->where('name', $sneaky)->all();

    expect($result)->toHaveCount(0);
    expect($db->select('users')->all())->toHaveCount(3);
});

test('unique check is safe against malicious values', function () {
    $db = testDb();

    // a value crafted to escape the old string-interpolated unique check
    $db->insert('users')
        ->params(['name' => 'Evil', 'email' => "x' OR '1'='1"])
        ->unique('email')
        ->execute();

    expect($db->select('users')->all())->toHaveCount(4);
});
