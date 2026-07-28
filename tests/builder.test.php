<?php

use Leaf\Db\Builder;
use Leaf\Db\Utils;

beforeEach(function () {
    Builder::$bindings = [];
});

test('where builds bound conditions', function () {
    expect(Builder::where('SELECT * FROM users', 'name', 'Mika'))
        ->toBe('SELECT * FROM users WHERE name = ?');
    expect(Builder::$bindings)->toBe(['Mika']);
});

test('whereAdvanced builds in, not in and between with placeholders', function () {
    expect(Builder::whereAdvanced('SELECT * FROM users', 'id', 'in', [1, 2]))
        ->toBe('SELECT * FROM users WHERE id IN (?, ?)');

    Builder::$bindings = [];
    expect(Builder::whereAdvanced('SELECT * FROM users', 'id', 'between', [1, 9]))
        ->toBe('SELECT * FROM users WHERE id BETWEEN ? AND ?');
});

test('whereAdvanced rejects malformed input', function () {
    Builder::whereAdvanced('SELECT * FROM users', 'id', 'between', [1, 2, 3]);
})->throws(InvalidArgumentException::class);

test('params builds insert and update fragments', function () {
    expect(Builder::params('INSERT INTO users', ['name' => 'Mika', 'role' => 'admin']))
        ->toBe('INSERT INTO users (name,role) VALUES (?,?)');

    Builder::$bindings = [];
    expect(Builder::params('UPDATE users', ['name' => 'Mika']))
        ->toBe('UPDATE users SET name=?');
});

test('params supports multi-row inserts', function () {
    expect(Builder::params('INSERT INTO users', [
        ['name' => 'Mika', 'role' => 'admin'],
        ['name' => 'Ama', 'role' => 'member'],
    ]))->toBe('INSERT INTO users (name,role) VALUES (?,?),(?,?)');
    expect(Builder::$bindings)->toBe(['Mika', 'admin', 'Ama', 'member']);
});

test('orderBy, groupBy, limit and offset append clauses', function () {
    expect(Builder::orderBy('SELECT * FROM users', 'name', 'asc'))
        ->toBe('SELECT * FROM users ORDER BY name ASC');
    expect(Builder::groupBy('SELECT * FROM users', 'role'))
        ->toBe('SELECT * FROM users GROUP BY role');
    expect(Builder::limit('SELECT * FROM users', 5))
        ->toBe('SELECT * FROM users LIMIT 5');
    expect(Builder::offset('SELECT * FROM users LIMIT 5', 10))
        ->toBe('SELECT * FROM users LIMIT 5 OFFSET 10');
});

test('utils build search phrases and singularize table names', function () {
    expect(Utils::includes('leaf'))->toBe('%leaf%');
    expect(Utils::beginsWith('leaf'))->toBe('leaf%');
    expect(Utils::endsWith('leaf'))->toBe('%leaf');

    expect(Utils::basicSingularize('users'))->toBe('user');
    expect(Utils::basicSingularize('people'))->toBe('person');
    expect(Utils::basicPluralize('user'))->toBe('users');
});

test('whereJson query strings target json fields', function () {
    $db = new Leaf\Db();
    $db->connect(['dbtype' => 'sqlite', 'dbname' => ':memory:']);

    $db->select('users')->whereJson('meta', 'plan', 'pro');

    expect($db->debug()['query'])
        ->toBe("SELECT * FROM users WHERE JSON_EXTRACT(meta, '$.plan') = ?");
});
