<?php

beforeAll(function () {
	// using mysqli just for wider support
	$conn = mysqli_connect('sql7.freemysqlhosting.net', 'sql7600346', 'l87WSttrMv', 'sql7600346');

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

	mysqli_multi_query($conn, $query);
	mysqli_close($conn);
});

test('connects to database', function () {
	$success = 'new';

	try {
		$db = new \Leaf\Db();
		$db->connect('sql7.freemysqlhosting.net', 'sql7600346', 'sql7600346', 'l87WSttrMv');
		$success = true;
	} catch (\Throwable $th) {
		echo $th->getMessage();
		$success = false;
	}

	expect($success)->toBeTrue();
});
