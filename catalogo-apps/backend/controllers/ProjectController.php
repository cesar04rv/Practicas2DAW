<?php
// ============================================================
// backend/controllers/ProjectController.php
// ============================================================

class ProjectController {

    public static function index(): never {
        requireAuth();
        $db = Database::getInstance();

        $conditions = [];
        $params     = [];

        $status = sanitize($_GET['status'] ?? '');
        if ($status && in_array($status, ['production','dev','stopped'])) {
            $conditions[] = 'p.status = ?';
            $params[]     = $status;
        }

        $ownerId = isset($_GET['owner_id']) && is_numeric($_GET['owner_id']) ? (int)$_GET['owner_id'] : null;
        if ($ownerId) {
            $conditions[] = '(p.owner_id = ? OR p.secondary_owner_id = ?)';
            $params[]     = $ownerId;
            $params[]     = $ownerId;
        }

        $techId = isset($_GET['technology_id']) && is_numeric($_GET['technology_id']) ? (int)$_GET['technology_id'] : null;
        if ($techId) {
            $conditions[] = 'EXISTS (SELECT 1 FROM project_technologies pt WHERE pt.project_id = p.id AND pt.technology_id = ?)';
            $params[]     = $techId;
        }

        $search = sanitize($_GET['search'] ?? '');
        if ($search !== '') {
            $conditions[] = 'MATCH(p.name, p.description) AGAINST (? IN BOOLEAN MODE)';
            $params[]     = $search . '*';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = PAGE_SIZE;
        $offset = ($page - 1) * $limit;

        $countStmt = $db->prepare("SELECT COUNT(*) FROM projects p $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "
            SELECT p.id, p.name, p.subtitle, p.description, p.status,
                p.location, p.dev_environment, p.url, p.credentials_location,
                p.created_at, p.updated_at,
                u1.id AS owner_id, u1.name AS owner_name,
                u2.id AS sec_owner_id, u2.name AS sec_owner_name
            FROM projects p
            LEFT JOIN users u1 ON u1.id = p.owner_id
            LEFT JOIN users u2 ON u2.id = p.secondary_owner_id
            $where
            ORDER BY p.updated_at DESC
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $projects = $stmt->fetchAll();

        if ($projects) {
            $ids          = array_column($projects, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $techStmt     = $db->prepare("
                SELECT pt.project_id, t.id, t.name, t.color
                FROM project_technologies pt
                JOIN technologies t ON t.id = pt.technology_id
                WHERE pt.project_id IN ($placeholders)
            ");
            $techStmt->execute($ids);
            $techRows = $techStmt->fetchAll();

            $techMap = [];
            foreach ($techRows as $row) {
                $techMap[$row['project_id']][] = ['id' => $row['id'], 'name' => $row['name'], 'color' => $row['color']];
            }
            foreach ($projects as &$proj) {
                $proj['technologies'] = $techMap[$proj['id']] ?? [];
            }
            unset($proj);
        }

        respondOk($projects, '', [
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $limit,
            'total_pages' => (int)ceil($total / $limit),
        ]);
    }

    public static function show(int $id): never {
        requireAuth();
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT p.*,
                u1.id AS owner_id, u1.name AS owner_name, u1.email AS owner_email,
                u2.id AS sec_owner_id, u2.name AS sec_owner_name, u2.email AS sec_owner_email
            FROM projects p
            LEFT JOIN users u1 ON u1.id = p.owner_id
            LEFT JOIN users u2 ON u2.id = p.secondary_owner_id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        if (!$project) respondNotFound('Proyecto no encontrado');

        $techStmt = $db->prepare("
            SELECT t.id, t.name, t.color
            FROM project_technologies pt
            JOIN technologies t ON t.id = pt.technology_id
            WHERE pt.project_id = ?
        ");
        $techStmt->execute([$id]);
        $project['technologies'] = $techStmt->fetchAll();

        respondOk($project);
    }

    public static function store(): never {
        requireAdmin();
        $data = self::validate(getJsonBody());
        $db   = Database::getInstance();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO projects
                    (name, subtitle, description, owner_id, secondary_owner_id,
                     status, location, dev_environment, url, credentials_location)
                VALUES (?,?,?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                $data['name'], $data['subtitle'], $data['description'],
                $data['owner_id'], $data['secondary_owner_id'],
                $data['status'], $data['location'], $data['dev_environment'],
                $data['url'], $data['credentials_location'],
            ]);
            $id = (int)$db->lastInsertId();
            self::syncTechnologies($db, $id, $data['technology_ids']);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            respondError(APP_DEBUG ? $e->getMessage() : 'Error al crear proyecto', 500);
        }
        respondCreated(['id' => $id], 'Proyecto creado');
    }

    public static function update(int $id): never {
        requireAdmin();
        $data = self::validate(getJsonBody());
        $db   = Database::getInstance();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                UPDATE projects SET
                    name = ?, subtitle = ?, description = ?,
                    owner_id = ?, secondary_owner_id = ?,
                    status = ?, location = ?, dev_environment = ?,
                    url = ?, credentials_location = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['name'], $data['subtitle'], $data['description'],
                $data['owner_id'], $data['secondary_owner_id'],
                $data['status'], $data['location'], $data['dev_environment'],
                $data['url'], $data['credentials_location'],
                $id,
            ]);
            self::syncTechnologies($db, $id, $data['technology_ids']);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            respondError(APP_DEBUG ? $e->getMessage() : 'Error al actualizar proyecto', 500);
        }
        respondOk(null, 'Proyecto actualizado');
    }

    public static function destroy(int $id): never {
        requireAdmin();
        $db   = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) respondNotFound('Proyecto no encontrado');
        respondOk(null, 'Proyecto eliminado');
    }

    private static function validate(array $body): array {
        $name    = sanitize($body['name'] ?? '');
        $ownerId = isset($body['owner_id']) && is_numeric($body['owner_id']) ? (int)$body['owner_id'] : 0;
        $status  = $body['status'] ?? 'dev';

        if (!$name)    respondError('El nombre es requerido');
        if (!$ownerId) respondError('El responsable principal es requerido');
        if (!in_array($status, ['production','dev','stopped'])) respondError('Estado inválido');

        return [
            'name'                 => $name,
            'subtitle'             => sanitize($body['subtitle']             ?? ''),
            'description'          => sanitize($body['description']          ?? ''),
            'owner_id'             => $ownerId,
            'secondary_owner_id'   => (isset($body['secondary_owner_id']) && is_numeric($body['secondary_owner_id']))
                                       ? (int)$body['secondary_owner_id'] : null,
            'status'               => $status,
            'location'             => sanitize($body['location']             ?? ''),
            'dev_environment'      => sanitize($body['dev_environment']      ?? ''),
            'url'                  => sanitize($body['url']                  ?? ''),
            'credentials_location' => sanitize($body['credentials_location'] ?? ''),
            'technology_ids'       => array_filter(
                                       array_map('intval', (array)($body['technology_ids'] ?? [])),
                                       fn($v) => $v > 0
                                      ),
        ];
    }

    private static function syncTechnologies(PDO $db, int $projectId, array $techIds): void {
        $db->prepare('DELETE FROM project_technologies WHERE project_id = ?')->execute([$projectId]);
        if (!$techIds) return;
        $stmt = $db->prepare('INSERT INTO project_technologies (project_id, technology_id) VALUES (?, ?)');
        foreach ($techIds as $techId) {
            $stmt->execute([$projectId, $techId]);
        }
    }
}