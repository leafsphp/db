<?php

it('orders results in ascending order', function () {
    $db = new \Leaf\Db();
    $db->connect('sql7.freemysqlhosting.net', 'sql7600346', 'sql7600346', 'l87WSttrMv');

    $users = $db->select('test')->orderBy("created_at", "asc")->all();

    expect(count($users))->toBe(2);
    expect($users[0]['created_at'])->toBeLessThan($users[1]['created_at']);
});

it('orders results in descending order', function () {
    $db = new \Leaf\Db();
    $db->connect('sql7.freemysqlhosting.net', 'sql7600346', 'sql7600346', 'l87WSttrMv');

    $users = $db->select('test')->orderBy("created_at", "desc")->all();

    expect(count($users))->toBe(2);
    expect($users[1]['created_at'])->toBeLessThan($users[0]['created_at']);
});
