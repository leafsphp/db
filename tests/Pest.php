<?php

use Leaf\Db;

/**
 * A fresh sqlite-backed Db instance with a seeded users table.
 * SQLite runs everywhere (mac/linux/windows CI) with zero setup,
 * unlike the old suite that pointed at a long-dead remote mysql box.
 */
function testDb(): Db
{
    $db = new Db();
    $db->connect([
        'dbtype' => 'sqlite',
        'dbname' => ':memory:',
    ]);

    $db->query('CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        role TEXT DEFAULT \'member\'
    )')->execute();

    $db->query('CREATE TABLE posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL
    )')->execute();

    foreach ([
        ['name' => 'Mika', 'email' => 'mika@leafphp.dev', 'role' => 'admin'],
        ['name' => 'Darko', 'email' => 'darko@leafphp.dev', 'role' => 'member'],
        ['name' => 'Ama', 'email' => 'ama@leafphp.dev', 'role' => 'member'],
    ] as $user) {
        $db->insert('users')->params($user)->execute();
    }

    $db->insert('posts')->params(['user_id' => 1, 'title' => 'Hello Leaf'])->execute();
    $db->insert('posts')->params(['user_id' => 1, 'title' => 'Leaf 5 is here'])->execute();
    $db->insert('posts')->params(['user_id' => 2, 'title' => 'My first post'])->execute();

    return $db;
}
