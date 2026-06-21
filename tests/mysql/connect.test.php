<?php

beforeAll(function () {
    if (file_exists(__DIR__ . '/../../.env.php')) {
        $_ENV += require __DIR__ . '/../../.env.php';
    }

    $_ENV += require __DIR__ . '/../../.env.example.php';

    $pdo = new \PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']}", $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);

    $query = '
		DROP TABLE IF EXISTS `test`;
		CREATE TABLE IF NOT EXISTS `test` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL,
			`email` varchar(255) NOT NULL,
			`password` varchar(255) NOT NULL,
			`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
	';

    $pdo->exec($query);
    $pdo = null;
});

it('connects to database', function () {
    $success = false;

    try {
        $db = new \Leaf\Db();
        expect($db->connect($_ENV['DB_HOST'], $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']))
            ->toBeInstanceOf(\Leaf\Db::class);
        $db->close();

        $success = true;
    } catch (\Throwable $th) {
    }

    expect($success)->toBeTrue();
});

it('inserts dummy user into `test` table', function () {
    $success = false;
    $db = new \Leaf\Db();
    $db->connect($_ENV['DB_HOST'], $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);

    try {
        $db->insert('test')
            ->params([
                'name' => 'Name',
                'email' => 'mail@mail.com',
                'password' => 'testing123',
            ])
            ->execute();

        sleep(1);

        $db->insert('test')
            ->params([
            'name' => 'Name2',
            'email' => 'mail2@mail.com',
            'password' => 'testing123',
        ])
            ->execute();
        $success = true;
    } catch (\Throwable $th) {
    }

    expect($success)->toBeTrue();
});

it('selects dummy user from `test` table', function () {
    $db = new \Leaf\Db();
    $db->connect($_ENV['DB_HOST'], $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);

    $user = $db->select('test')
        ->where('name', 'Name')
        ->first();

    expect($user['name'])->toBe('Name');
    expect($user['email'])->toBe('mail@mail.com');
});
