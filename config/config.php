<?php
declare(strict_types=1);

$config = [
    'app' => [
        'name' => 'INARAIN',
        'tagline' => 'Gestion des stagiaires & absences',
        'timezone' => 'Africa/Casablanca',
        'debug' => false,
        'allow_registration' => true,
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'inarain',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'academic' => [
        'pass_mark' => 10.0,
        'absence_penalty' => 0.25,
        'page_size' => 12,
        'slots' => [
            1 => '08:30 – 11:00',
            2 => '11:15 – 13:45',
            3 => '14:30 – 17:00',
            4 => '17:15 – 19:45',
        ],
    ],
];

$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    $config = array_replace_recursive($config, require $local);
}

return $config;
