<?php
class ProjectController {

    public static function index(): never {
        requireAuth();
        $db = Database::getInstance();
        $conditions = []; $params = [];

        $status = sanitize($_GET['status'] ?? '');
        if ($status && in_array($status, ['production','dev','stopped'])) {
            $conditions[] = 'p.status = ?'; $params[] = $status;
        }
        $userId = isset($_GET['user_id']) && is_numeric($_GET['user_id']) ? (int)$_GET['user_id'] : null;
        if ($userId) {
            $conditions[] = 'EXISTS (SELECT 1 FROM project_users pu WHERE pu.project_id = p.id AND pu.user_id = ?)';
            $params[] = $userId;
        }
        $techId = isset($_GET['technology_id']) && is_numeric($_GET['technology_id']) ? (int)$_GET['technology_id'] : null;
        if ($techId) {
            $conditions[] = 'EXISTS (SELECT 1 FROM project_technologies pt WHERE pt.project_id = p.id AND pt.technology_id = ?)';
            $params[] = $techId;
        }
        $search = sanitize($_GET['search'] ?? '');
        if ($search !== '') {
            $conditions[] = 'MATCH(p.name, p.description) AGAINST (? IN BOOLEAN MODE)';
            $params[] = $search . '*';
        }

        $where  = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = PAGE_SIZE;
        $offset = ($page - 1) * $limit;

        $countStmt = $db->prepare("SELECT COUNT(*) FROM projects p $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT p.id, p.name, p.subtitle, p.description, p.status,
                   p.location, p.dev_environment, p.url, p.credentials_location,
                   p.created_at, p.updated_at
            FROM projects p $where
            ORDER BY p.updated_at DESC LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        $projects = $stmt->fetchAll();

        if ($projects) {
            $ids = array_column($projects, 'id');
            $ph  = implode(',', array_fill(0, count($ids), '?'));

            $userStmt = $db->prepare("
                SELECT pu.project_id, u.id, u.name, u.email, pu.role
                FROM project_users pu JOIN users u ON u.id = pu.user_id
                WHERE pu.project_id IN ($ph)
                ORDER BY FIELD(pu.role,'owner','collaborator')
            ");
            $userStmt->execute($ids);
            $userMap = [];
            foreach ($userStmt->fetchAll() as $r) {
                $userMap[$r['project_id']][] = ['id'=>$r['id'],'name'=>$r['name'],'email'=>$r['email'],'role'=>$r['role']];
            }

            $techStmt = $db->prepare("
                SELECT pt.project_id, t.id, t.name, t.color
                FROM project_technologies pt JOIN technologies t ON t.id = pt.technology_id
                WHERE pt.project_id IN ($ph)
            ");
            $techStmt->execute($ids);
            $techMap = [];
            foreach ($techStmt->fetchAll() as $r) {
                $techMap[$r['project_id']][] = ['id'=>$r['id'],'name'=>$r['name'],'color'=>$r['color']];
            }

            foreach ($projects as &$proj) {
                $proj['users']        = $userMap[$proj['id']] ?? [];
                $proj['technologies'] = $techMap[$proj['id']] ?? [];
            }
            unset($proj);
        }

        respondOk($projects, '', ['total'=>$total,'page'=>$page,'per_page'=>$limit,'total_pages'=>(int)ceil($total/$limit)]);
    }

    public static function show(int $id): never {
        requireAuth();
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        if (!$project) respondNotFound('Proyecto no encontrado');

        $userStmt = $db->prepare("
            SELECT u.id, u.name, u.email, pu.role
            FROM project_users pu JOIN users u ON u.id = pu.user_id
            WHERE pu.project_id = ? ORDER BY FIELD(pu.role,'owner','collaborator')
        ");
        $userStmt->execute([$id]);
        $project['users'] = $userStmt->fetchAll();

        $techStmt = $db->prepare("
            SELECT t.id, t.name, t.color
            FROM project_technologies pt JOIN technologies t ON t.id = pt.technology_id
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
            $stmt = $db->prepare("INSERT INTO projects (name,subtitle,description,status,location,dev_environment,url,credentials_location) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$data['name'],$data['subtitle'],$data['description'],$data['status'],$data['location'],$data['dev_environment'],$data['url'],$data['credentials_location']]);
            $id = (int)$db->lastInsertId();
            self::syncUsers($db, $id, $data['project_users']);
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
            $stmt = $db->prepare("UPDATE projects SET name=?,subtitle=?,description=?,status=?,location=?,dev_environment=?,url=?,credentials_location=? WHERE id=?");
            $stmt->execute([$data['name'],$data['subtitle'],$data['description'],$data['status'],$data['location'],$data['dev_environment'],$data['url'],$data['credentials_location'],$id]);
            self::syncUsers($db, $id, $data['project_users']);
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
        $name   = sanitize($body['name'] ?? '');
        $status = $body['status'] ?? 'dev';
        if (!$name) respondError('El nombre es requerido');
        if (!in_array($status, ['production','dev','stopped'])) respondError('Estado inválido');

        $projectUsers = [];
        foreach ((array)($body['project_users'] ?? []) as $pu) {
            if (isset($pu['user_id']) && is_numeric($pu['user_id'])) {
                $role = in_array($pu['role'] ?? '', ['owner','collaborator']) ? $pu['role'] : 'collaborator';
                $projectUsers[] = ['user_id' => (int)$pu['user_id'], 'role' => $role];
            }
        }

        return [
            'name'                 => $name,
            'subtitle'             => sanitize($body['subtitle']             ?? ''),
            'description'          => sanitize($body['description']          ?? ''),
            'status'               => $status,
            'location'             => sanitize($body['location']             ?? ''),
            'dev_environment'      => sanitize($body['dev_environment']      ?? ''),
            'url'                  => sanitize($body['url']                  ?? ''),
            'credentials_location' => sanitize($body['credentials_location'] ?? ''),
            'project_users'        => $projectUsers,
            'technology_ids'       => array_filter(array_map('intval', (array)($body['technology_ids'] ?? [])), fn($v) => $v > 0),
        ];
    }

    private static function syncUsers(PDO $db, int $projectId, array $projectUsers): void {
        $db->prepare('DELETE FROM project_users WHERE project_id = ?')->execute([$projectId]);
        if (!$projectUsers) return;
        $stmt = $db->prepare('INSERT INTO project_users (project_id, user_id, role) VALUES (?, ?, ?)');
        foreach ($projectUsers as $pu) $stmt->execute([$projectId, $pu['user_id'], $pu['role']]);
    }

    private static function syncTechnologies(PDO $db, int $projectId, array $techIds): void {
        $db->prepare('DELETE FROM project_technologies WHERE project_id = ?')->execute([$projectId]);
        if (!$techIds) return;
        $stmt = $db->prepare('INSERT INTO project_technologies (project_id, technology_id) VALUES (?, ?)');
        foreach ($techIds as $techId) $stmt->execute([$projectId, $techId]);
    }
}