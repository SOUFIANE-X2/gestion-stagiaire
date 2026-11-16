<?php
declare(strict_types=1);

/*
 * Local configuration — ignored by Git.
 *
 * Copy config.example.php to config.local.php and adjust the values
 * for your machine. Never commit real credentials.
 */
return [
    'app' => [
        'debug' => true,
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'inarain',
        'user' => 'root',
        'pass' => 'your-mysql-password',
    ],
];
