<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Stagiaire
{
    private const SELECT_BASE = "
        SELECT s.*,
               g.code AS group_code,
               g.label AS group_label,
               g.academic_year,
               f.id AS filiere_id,
               f.code AS filiere_code,
               f.label AS filiere_label,
               (SELECT COUNT(*) FROM absences a WHERE a.stagiaire_id = s.id) AS absence_total,
               (SELECT COUNT(*) FROM absences a WHERE a.stagiaire_id = s.id AND a.justified = 0) AS absence_open
        FROM stagiaires s
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
        $count = $connection->prepare('SELECT COUNT(*) FROM stagiaires s JOIN groupes g ON g.id = s.group_id JOIN filieres f ON f.id = g.filiere_id WHERE ' . $where);
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $perPage = max(1, (int) ($filters['per_page'] ?? 10));
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) ($filters['page'] ?? 1)), $pages);
        $offset = ($page - 1) * $perPage;

        $sql = self::SELECT_BASE . ' WHERE ' . $where . ' ORDER BY ' . self::orderBy($filters['sort'] ?? 'name')
            . ' LIMIT :limit OFFSET :offset';

        $statement = $connection->prepare($sql);
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
            self::SELECT_BASE . ' WHERE ' . $where . ' ORDER BY s.last_name, s.first_name'
        );
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(self::SELECT_BASE . ' WHERE s.id = ? LIMIT 1');
        $statement->execute([$id]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO stagiaires (code, first_name, last_name, gender, email, phone, group_id, note, status)
             VALUES (:code, :first_name, :last_name, :gender, :email, :phone, :group_id, :note, :status)'
        );
        $statement->execute([
            'code' => $data['code'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'gender' => $data['gender'],
            'email' => $data['email'] !== '' ? $data['email'] : null,
            'phone' => $data['phone'] !== '' ? $data['phone'] : null,
            'group_id' => (int) $data['group_id'],
            'note' => $data['note'],
            'status' => $data['status'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE stagiaires
             SET first_name = :first_name, last_name = :last_name, gender = :gender,
                 email = :email, phone = :phone, group_id = :group_id, note = :note, status = :status
             WHERE id = :id'
        );
        $statement->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'gender' => $data['gender'],
            'email' => $data['email'] !== '' ? $data['email'] : null,
            'phone' => $data['phone'] !== '' ? $data['phone'] : null,
            'group_id' => (int) $data['group_id'],
            'note' => $data['note'],
            'status' => $data['status'],
            'id' => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $statement = Database::connection()->prepare('DELETE FROM stagiaires WHERE id = ?');
        $statement->execute([$id]);
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM stagiaires WHERE email = ?';
        $params = [$email];

        if ($exceptId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public static function nextCode(): string
    {
        $statement = Database::connection()->query(
            "SELECT MAX(CAST(SUBSTRING(code, 5) AS UNSIGNED)) FROM stagiaires WHERE code LIKE 'STG-%'"
        );
        $next = ((int) $statement->fetchColumn()) + 1;

        return 'STG-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public static function countAll(?string $status = null): int
    {
        if ($status === null) {
            return (int) Database::connection()->query('SELECT COUNT(*) FROM stagiaires')->fetchColumn();
        }

        $statement = Database::connection()->prepare('SELECT COUNT(*) FROM stagiaires WHERE status = ?');
        $statement->execute([$status]);

        return (int) $statement->fetchColumn();
    }

    public static function averageNote(): float
    {
        return (float) Database::connection()->query('SELECT COALESCE(AVG(note), 0) FROM stagiaires')->fetchColumn();
    }

    /**
     * @return array<int, array>
     */
    public static function byFiliere(): array
    {
        $sql = 'SELECT f.id, f.code, f.label,
                       COUNT(s.id) AS total,
                       COALESCE(AVG(s.note), 0) AS average_note
                FROM filieres f
                LEFT JOIN groupes g ON g.filiere_id = f.id
                LEFT JOIN stagiaires s ON s.group_id = g.id
                GROUP BY f.id
                ORDER BY f.label';

        return Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<int, array>
     */
    public static function top(int $limit = 5): array
    {
        $statement = Database::connection()->prepare(
            self::SELECT_BASE . ' WHERE s.status = \'active\' ORDER BY s.note DESC, s.last_name LIMIT :limit'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array{active: int, graduated: int, archived: int}
     */
    public static function statusCounts(): array
    {
        $rows = Database::connection()
            ->query('SELECT status, COUNT(*) AS total FROM stagiaires GROUP BY status')
            ->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'active' => (int) ($rows['active'] ?? 0),
            'graduated' => (int) ($rows['graduated'] ?? 0),
            'archived' => (int) ($rows['archived'] ?? 0),
        ];
    }

    private static function buildFilters(array $filters): array
    {
        $where = ['1 = 1'];
        $params = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $where[] = '(s.first_name LIKE :search1 OR s.last_name LIKE :search2 OR s.code LIKE :search3 OR s.email LIKE :search4)';
            $like = '%' . $search . '%';
            $params[':search1'] = $like;
            $params[':search2'] = $like;
            $params[':search3'] = $like;
            $params[':search4'] = $like;
        }

        if (!empty($filters['filiere'])) {
            $where[] = 'f.id = :filiere';
            $params[':filiere'] = (int) $filters['filiere'];
        }

        if (!empty($filters['group'])) {
            $where[] = 'g.id = :group';
            $params[':group'] = (int) $filters['group'];
        }

        if (!empty($filters['status'])) {
            $where[] = 's.status = :status';
            $params[':status'] = $filters['status'];
        }

        return [implode(' AND ', $where), $params];
    }

    private static function orderBy(string $sort): string
    {
        return match ($sort) {
            'name_desc' => 's.last_name DESC, s.first_name DESC',
            'note' => 's.note DESC, s.last_name',
            'note_asc' => 's.note ASC, s.last_name',
            'recent' => 's.created_at DESC',
            'code' => 's.code ASC',
            default => 's.last_name ASC, s.first_name ASC',
        };
    }
}
