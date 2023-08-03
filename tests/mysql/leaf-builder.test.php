<?php

it('orders results in ascending order', function () {
    $db = new \Leaf\Db();
    $db->connect('eu-cdbr-west-03.cleardb.net', 'heroku_fb1311a639bb407', 'b9607a8a6d5ebb', 'cc589b17');

    $users = $db->select('test')->orderBy("created_at", "asc")->all();

    expect($users)->toBeArray();
    expect($users[0]['created_at'])->toBeLessThan($users[1]['created_at']);
});

it('orders results in descending order', function () {
    $db = new \Leaf\Db();
    $db->connect('eu-cdbr-west-03.cleardb.net', 'heroku_fb1311a639bb407', 'b9607a8a6d5ebb', 'cc589b17');

    $users = $db->select('test')->orderBy("created_at", "desc")->all();

    expect($users)->toBeArray();
    expect($users[1]['created_at'])->toBeLessThan($users[0]['created_at']);
});

it('orders by dummy name and count', function () {
    $db = new \Leaf\Db();
    $db->connect('eu-cdbr-west-03.cleardb.net', 'heroku_fb1311a639bb407', 'b9607a8a6d5ebb', 'cc589b17');

    $data = $db->select('test', 'name, COUNT(*)')->groupBy("created_at")->all();

    expect(count($data))->toBe(2);
});
