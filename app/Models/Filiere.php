<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

final class Filiere
{
    /**
     * @return array<int, array>
     */
    public static function all(): array
    {
        return Database::connection()
            ->query('SELECT * FROM filieres ORDER BY label')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<int, array>
     */
    public static function allWithStats(): array
    {
        $sql = 'SELECT f.*,
                       COUNT(DISTINCT g.id) AS group_count,
                       COUNT(DISTINCT s.id) AS stagiaire_count,
                       COALESCE(AVG(s.note), 0) AS average_note
                FROM filieres f
                LEFT JOIN groupes g ON g.filiere_id = f.id
                LEFT JOIN stagiaires s ON s.group_id = g.id
                GROUP BY f.id
                ORDER BY f.label';

        return Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM filieres WHERE id = ? LIMIT 1');
        $statement->execute([$id]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findByCode(string $code): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM filieres WHERE code = ? LIMIT 1');
        $statement->execute([$code]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(string $code, string $label): int
    {
        $statement = Database::connection()->prepare('INSERT INTO filieres (code, label) VALUES (?, ?)');
        $statement->execute([$code, $label]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function delete(int $id): bool
    {
        $connection = Database::connection();

        $check = $connection->prepare(
            'SELECT COUNT(*) FROM groupes g JOIN stagiaires s ON s.group_id = g.id WHERE g.filiere_id = ?'
        );
        $check->execute([$id]);
        if ((int) $check->fetchColumn() > 0) {
            return false;
        }

        $connection->beginTransaction();

        try {
            $groups = $connection->prepare('DELETE FROM groupes WHERE filiere_id = ?');
            $groups->execute([$id]);

            $delete = $connection->prepare('DELETE FROM filieres WHERE id = ?');
            $delete->execute([$id]);

            $connection->commit();

            return true;
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    /**
     * @return array<int, array>
     */
    public static function groups(): array
    {
        $sql = 'SELECT g.*, f.label AS filiere_label, f.code AS filiere_code,
                       COUNT(s.id) AS stagiaire_count,
                       COALESCE(AVG(s.note), 0) AS average_note
                FROM groupes g
                JOIN filieres f ON f.id = g.filiere_id
                LEFT JOIN stagiaires s ON s.group_id = g.id
                GROUP BY g.id
                ORDER BY f.label, g.label';

        return Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Groups formatted for select inputs.
     *
     * @return array<int, array>
     */
    public static function groupOptions(): array
    {
        $sql = 'SELECT g.id, g.code, g.label, g.academic_year, g.filiere_id, f.label AS filiere_label
                FROM groupes g
                JOIN filieres f ON f.id = g.filiere_id
                ORDER BY f.label, g.label';

        return Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<int, array>
     */
    public static function groupsForFiliere(int $filiereId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT g.*, COUNT(s.id) AS stagiaire_count
             FROM groupes g
             LEFT JOIN stagiaires s ON s.group_id = g.id
             WHERE g.filiere_id = ?
             GROUP BY g.id
             ORDER BY g.label'
        );
        $statement->execute([$filiereId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findGroup(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT g.*, f.label AS filiere_label FROM groupes g JOIN filieres f ON f.id = g.filiere_id WHERE g.id = ? LIMIT 1'
        );
        $statement->execute([$id]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function createGroup(string $code, string $label, int $filiereId, string $academicYear): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO groupes (code, label, filiere_id, academic_year) VALUES (?, ?, ?, ?)'
        );
        $statement->execute([$code, $label, $filiereId, $academicYear]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function deleteGroup(int $id): bool
    {
        $check = Database::connection()->prepare('SELECT COUNT(*) FROM stagiaires WHERE group_id = ?');
        $check->execute([$id]);

        if ((int) $check->fetchColumn() > 0) {
            return false;
        }

        $delete = Database::connection()->prepare('DELETE FROM groupes WHERE id = ?');
        $delete->execute([$id]);

        return true;
    }

    public static function codeExists(string $code): bool
    {
        $statement = Database::connection()->prepare('SELECT COUNT(*) FROM filieres WHERE code = ?');
        $statement->execute([$code]);

        return (int) $statement->fetchColumn() > 0;
    }

    public static function groupCodeExists(string $code): bool
    {
        $statement = Database::connection()->prepare('SELECT COUNT(*) FROM groupes WHERE code = ?');
        $statement->execute([$code]);

        return (int) $statement->fetchColumn() > 0;
    }
}
