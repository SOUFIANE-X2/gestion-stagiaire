<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

final class Absence
{
    private const SELECT_BASE = "
        SELECT a.*,
               s.code AS stagiaire_code,
               s.first_name,
               s.last_name,
               s.note AS stagiaire_note,
               g.code AS group_code,
               f.label AS filiere_label
        FROM absences a
        JOIN stagiaires s ON s.id = a.stagiaire_id
        JOIN groupes g ON g.id = s.group_id
        JOIN filieres f ON f.id = g.filiere_id
    ";

    /**
     * @return array{rows: array, total: int, page: int, pages: int, per_page: int}
     */
    public static function paginate(array $filters): array
    {
        [$where, $params] = self::buildFilters($filters);

        $connection = Database::connection();
        $count = $connection->prepare(
            'SELECT COUNT(*) FROM absences a
             JOIN stagiaires s ON s.id = a.stagiaire_id
             JOIN groupes g ON g.id = s.group_id
             JOIN filieres f ON f.id = g.filiere_id
             WHERE ' . $where
        );
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $perPage = max(1, (int) ($filters['per_page'] ?? 12));
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) ($filters['page'] ?? 1)), $pages);
        $offset = ($page - 1) * $perPage;

        $statement = $connection->prepare(
            self::SELECT_BASE . ' WHERE ' . $where . ' ORDER BY a.date DESC, a.slot DESC LIMIT :limit OFFSET :offset'
        );
        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'rows' => $statement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'per_page' => $perPage,
        ];
    }

    /**
     * @return array<int, array>
     */
    public static function all(array $filters = []): array
    {
        [$where, $params] = self::buildFilters($filters);

        $statement = Database::connection()->prepare(
            self::SELECT_BASE . ' WHERE ' . $where . ' ORDER BY a.date DESC, a.slot DESC'
        );
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(self::SELECT_BASE . ' WHERE a.id = ? LIMIT 1');
        $statement->execute([$id]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function exists(int $stagiaireId, string $date, int $slot): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM absences WHERE stagiaire_id = ? AND date = ? AND slot = ?'
        );
        $statement->execute([$stagiaireId, $date, $slot]);

        return (int) $statement->fetchColumn() > 0;
    }

    /**
     * Record an unjustified absence and apply the academic penalty.
     */
    public static function createWithPenalty(int $stagiaireId, string $date, int $slot, string $reason = ''): int
    {
        $connection = Database::connection();
        $penalty = (float) config('academic.absence_penalty', 0.25);

        $connection->beginTransaction();

        try {
            $insert = $connection->prepare(
                'INSERT INTO absences (stagiaire_id, date, slot, justified, reason) VALUES (?, ?, ?, 0, ?)'
            );
            $insert->execute([$stagiaireId, $date, $slot, $reason !== '' ? $reason : null]);
            $id = (int) $connection->lastInsertId();

            $update = $connection->prepare(
                'UPDATE stagiaires SET note = GREATEST(0, note - ?) WHERE id = ?'
            );
            $update->execute([$penalty, $stagiaireId]);

            $connection->commit();

            return $id;
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    /**
     * Justify an absence and restore the penalty to the stagiaire's note.
     */
    public static function justify(int $id, string $justification): bool
    {
        $connection = Database::connection();
        $penalty = (float) config('academic.absence_penalty', 0.25);

        $connection->beginTransaction();

        try {
            $find = $connection->prepare('SELECT * FROM absences WHERE id = ? FOR UPDATE');
            $find->execute([$id]);
            $absence = $find->fetch(PDO::FETCH_ASSOC);

            if (!$absence) {
                $connection->rollBack();
                return false;
            }

            if ((int) $absence['justified'] === 1) {
                $connection->rollBack();
                return false;
            }

            $update = $connection->prepare(
                'UPDATE absences SET justified = 1, justification = ? WHERE id = ?'
            );
            $update->execute([$justification !== '' ? $justification : null, $id]);

            $reward = $connection->prepare(
                'UPDATE stagiaires SET note = LEAST(20, note + ?) WHERE id = ?'
            );
            $reward->execute([$penalty, (int) $absence['stagiaire_id']]);

            $connection->commit();

            return true;
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    /**
     * Delete an absence and undo its penalty when it was still unjustified.
     */
    public static function deleteWithRestore(int $id): bool
    {
        $connection = Database::connection();
        $penalty = (float) config('academic.absence_penalty', 0.25);

        $connection->beginTransaction();

        try {
            $find = $connection->prepare('SELECT * FROM absences WHERE id = ? FOR UPDATE');
            $find->execute([$id]);
            $absence = $find->fetch(PDO::FETCH_ASSOC);

            if (!$absence) {
                $connection->rollBack();
                return false;
            }

            if ((int) $absence['justified'] === 0) {
                $restore = $connection->prepare(
                    'UPDATE stagiaires SET note = LEAST(20, note + ?) WHERE id = ?'
                );
                $restore->execute([$penalty, (int) $absence['stagiaire_id']]);
            }

            $delete = $connection->prepare('DELETE FROM absences WHERE id = ?');
            $delete->execute([$id]);

            $connection->commit();

            return true;
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    /**
     * @return array{total: int, open: int, justified: int, month_total: int, month_open: int, today: int}
     */
    public static function stats(): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    COALESCE(SUM(justified = 0), 0) AS open_count,
                    COALESCE(SUM(justified = 1), 0) AS justified_count,
                    COALESCE(SUM(DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')), 0) AS month_total,
                    COALESCE(SUM(DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') AND justified = 0), 0) AS month_open,
                    COALESCE(SUM(date = CURDATE()), 0) AS today
                FROM absences";
        $row = Database::connection()->query($sql)->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'open' => (int) ($row['open_count'] ?? 0),
            'justified' => (int) ($row['justified_count'] ?? 0),
            'month_total' => (int) ($row['month_total'] ?? 0),
            'month_open' => (int) ($row['month_open'] ?? 0),
            'today' => (int) ($row['today'] ?? 0),
        ];
    }

    /**
     * @return array<int, array>
     */
    public static function recent(int $limit = 6): array
    {
        $statement = Database::connection()->prepare(
            self::SELECT_BASE . ' ORDER BY a.created_at DESC, a.id DESC LIMIT :limit'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<int, array>
     */
    public static function forStagiaire(int $stagiaireId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT * FROM absences WHERE stagiaire_id = ? ORDER BY date DESC, slot DESC'
        );
        $statement->execute([$stagiaireId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Monthly totals for the last N months, oldest first.
     *
     * @return array<int, array{month: string, label: string, total: int, open: int}>
     */
    public static function monthlyTrend(int $months = 6): array
    {
        $start = (new \DateTimeImmutable('first day of this month'))->modify('-' . ($months - 1) . ' months');

        $statement = Database::connection()->prepare(
            "SELECT DATE_FORMAT(date, '%Y-%m') AS month,
                    COUNT(*) AS total,
                    COALESCE(SUM(justified = 0), 0) AS open_count
             FROM absences
             WHERE date >= ?
             GROUP BY month"
        );
        $statement->execute([$start->format('Y-m-d')]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['month']] = $row;
        }

        $trend = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $start->modify('+' . $i . ' months');
            $key = $month->format('Y-m');
            $trend[] = [
                'month' => $key,
                'label' => month_fr((int) $month->format('n'), true),
                'total' => (int) ($indexed[$key]['total'] ?? 0),
                'open' => (int) ($indexed[$key]['open_count'] ?? 0),
            ];
        }

        return $trend;
    }

    /**
     * @return array<int, array{slot: int, total: int}>
     */
    public static function bySlot(): array
    {
        $rows = Database::connection()
            ->query('SELECT slot, COUNT(*) AS total FROM absences GROUP BY slot ORDER BY slot')
            ->fetchAll(PDO::FETCH_ASSOC);
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int) $row['slot']] = (int) $row['total'];
        }

        $slots = [];
        foreach (array_keys(config('academic.slots', [])) as $slot) {
            $slots[] = ['slot' => (int) $slot, 'total' => $indexed[(int) $slot] ?? 0];
        }

        return $slots;
    }

    private static function buildFilters(array $filters): array
    {
        $where = ['1 = 1'];
        $params = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $where[] = '(s.first_name LIKE :search1 OR s.last_name LIKE :search2 OR s.code LIKE :search3)';
            $like = '%' . $search . '%';
            $params[':search1'] = $like;
            $params[':search2'] = $like;
            $params[':search3'] = $like;
        }

        if (!empty($filters['filiere'])) {
            $where[] = 'f.id = :filiere';
            $params[':filiere'] = (int) $filters['filiere'];
        }

        if (($filters['state'] ?? '') === 'open') {
            $where[] = 'a.justified = 0';
        } elseif (($filters['state'] ?? '') === 'justified') {
            $where[] = 'a.justified = 1';
        }

        $month = (string) ($filters['month'] ?? '');
        if (preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            $where[] = "DATE_FORMAT(a.date, '%Y-%m') = :month";
            $params[':month'] = $month;
        }

        return [implode(' AND ', $where), $params];
    }
}
