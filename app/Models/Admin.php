<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Admin
{
    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM administrators WHERE id = ? LIMIT 1');
        $statement->execute([$id]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findByMatricule(string $matricule): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM administrators WHERE matricule = ? LIMIT 1');
        $statement->execute([$matricule]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO administrators (matricule, password_hash, first_name, last_name, email)
             VALUES (:matricule, :password_hash, :first_name, :last_name, :email)'
        );
        $statement->execute([
            'matricule' => $data['matricule'],
            'password_hash' => $data['password_hash'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function updateProfile(int $id, array $data): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE administrators
             SET first_name = :first_name, last_name = :last_name, email = :email
             WHERE id = :id'
        );
        $statement->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'id' => $id,
        ]);
    }

    public static function updatePassword(int $id, string $hash): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE administrators SET password_hash = ? WHERE id = ?'
        );
        $statement->execute([$hash, $id]);
    }

    public static function matriculeExists(string $matricule): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM administrators WHERE matricule = ?'
        );
        $statement->execute([$matricule]);

        return (int) $statement->fetchColumn() > 0;
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM administrators WHERE email = ?';
        $params = [$email];

        if ($exceptId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public static function touchLogin(int $id): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE administrators SET last_login_at = NOW() WHERE id = ?'
        );
        $statement->execute([$id]);
    }
}
