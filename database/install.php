<?php

declare(strict_types=1);

/**
 * Database installer.
 *
 * Usage:
 *   php database/install.php
 *   php database/install.php --fresh   (drops and recreates the database)
 *
 * Create config/config.local.php first (copy config/config.example.php) with your
 * MySQL credentials. This script never prints secrets.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This installer can only be run from the command line.');
}

$config = require __DIR__ . '/../config/config.php';
$db = $config['db'];
$fresh = in_array('--fresh', $argv ?? [], true);

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;charset=%s', $db['host'], (int) $db['port'], $db['charset'] ?? 'utf8mb4'),
        $db['user'],
        $db['pass'],
        $options
    );
} catch (PDOException $exception) {
    fwrite(STDERR, "Connexion MySQL impossible : " . $exception->getMessage() . PHP_EOL);
    fwrite(STDERR, "Vérifiez config/config.local.php (utilisateur, mot de passe, port)." . PHP_EOL);
    exit(1);
}

$database = (string) $db['name'];

if (preg_match('/^[A-Za-z0-9_]+$/', $database) !== 1) {
    fwrite(STDERR, "Nom de base invalide : {$database}" . PHP_EOL);
    exit(1);
}

if ($fresh) {
    $pdo->exec("DROP DATABASE IF EXISTS `{$database}`");
    echo "Base « {$database} » supprimée." . PHP_EOL;
}

$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$database}`");

$existingTables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
if ($existingTables !== []) {
    fwrite(STDERR, "La base « {$database} » contient déjà des tables." . PHP_EOL);
    fwrite(STDERR, "Utilisez --fresh pour la recréer (les données seront perdues)." . PHP_EOL);
    exit(1);
}

$files = ['schema.sql', 'seed.sql'];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (!is_file($path)) {
        fwrite(STDERR, "Fichier manquant : {$file}" . PHP_EOL);
        exit(1);
    }

    $sql = file_get_contents($path) ?: '';
    $statements = split_sql($sql);

    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }

    echo "Exécuté : {$file}" . PHP_EOL;
}

$counts = [];
foreach (['administrators', 'filieres', 'groupes', 'stagiaires', 'absences'] as $table) {
    $counts[$table] = (int) $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
}

echo PHP_EOL . "Installation terminée." . PHP_EOL;
foreach ($counts as $table => $count) {
    echo sprintf('  %-16s %d ligne(s)', $table, $count) . PHP_EOL;
}

echo PHP_EOL . "Compte de démonstration :" . PHP_EOL;
echo "  Matricule : ADM-001" . PHP_EOL;
echo "  Mot de passe : inarain2026" . PHP_EOL;
echo PHP_EOL . "Démarrage local : php -S 127.0.0.1:8093 -t public public/index.php" . PHP_EOL;

/**
 * Split a SQL script into individual statements.
 *
 * @return array<int, string>
 */
function split_sql(string $sql): array
{
    $lines = preg_split('/\r\n|\r|\n/', $sql) ?: [];
    $cleaned = [];

    foreach ($lines as $line) {
        if (preg_match('/^\s*--/', $line) === 1) {
            continue;
        }
        $cleaned[] = $line;
    }

    $text = implode("\n", $cleaned);
    $parts = preg_split('/;\s*(?:\n|$)/', $text) ?: [];

    return array_values(array_filter(array_map('trim', $parts), static fn (string $part): bool => $part !== ''));
}
