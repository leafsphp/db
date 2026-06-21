<?php

beforeAll(function () {
    if (file_exists(__DIR__ . '/../../.env.php')) {
        $_ENV += require __DIR__ . '/../../.env.php';
    }

    $_ENV += require __DIR__ . '/../../.env.example.php';
});

it('orders results in ascending order', function () {
    $db = new \Leaf\Db();
    $db->connect($_ENV['DB_HOST'], $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);

    $users = $db->select('test')->orderBy("created_at", "asc")->all();

    expect($users)->toBeArray();
    expect($users[0]['created_at'])->toBeLessThan($users[1]['created_at']);
});

it('orders results in descending order', function () {
    $db = new \Leaf\Db();
    $db->connect($_ENV['DB_HOST'], $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);

    $users = $db->select('test')->orderBy("created_at", "desc")->all();

    expect($users)->toBeArray();
    expect($users[1]['created_at'])->toBeLessThan($users[0]['created_at']);
});

it('orders by dummy name and count', function () {
    $db = new \Leaf\Db();
    $db->connect($_ENV['DB_HOST'], $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);

    $data = $db->select('test', 'name, COUNT(*)')->groupBy("created_at")->all();

    expect(count($data))->toBe(2);
});

it('orders by dummy name and count with limit and offset', function () {
    $db = new \Leaf\Db();
    $db->connect($_ENV['DB_HOST'], $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);

    $data = $db->select('test', 'name, COUNT(*)')->groupBy("created_at")->limit(1)->offset(1)->all();

    expect(count($data))->toBe(1);
});
