<?php
// ============================================================
// backend/controllers/TechnologyController.php
// ============================================================

class TechnologyController {

    /** GET /technologies */
    public static function index(): never {
        requireAuth();
        $db   = Database::getInstance();
        $stmt = $db->query('SELECT id, name, color FROM technologies ORDER BY name');
        respondOk($stmt->fetchAll());
    }

    /** GET /technologies/:id */
    public static function show(int $id): never {
        requireAuth();
        $db   = Database::getInstance();
        $stmt = $db->prepare('SELECT id, name, color FROM technologies WHERE id = ?');
        $stmt->execute([$id]);
        $tech = $stmt->fetch();
        if (!$tech) respondNotFound('Tecnología no encontrada');
        respondOk($tech);
    }

    /** POST /technologies — solo admin */
    public static function store(): never {
        requireAdmin();
        $body  = getJsonBody();
        $name  = sanitize($body['name'] ?? '');
        $color = sanitize($body['color'] ?? '#6366f1');

        if (!$name) respondError('El nombre es requerido');
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) $color = '#6366f1';

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare('INSERT INTO technologies (name, color) VALUES (?, ?)');
            $stmt->execute([$name, $color]);
            $id = (int)$db->lastInsertId();
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) respondError('Ya existe esa tecnología', 409);
            respondError('Error al crear tecnología', 500);
        }
        respondCreated(['id' => $id, 'name' => $name, 'color' => $color], 'Tecnología creada');
    }

    /** PUT /technologies/:id — solo admin */
    public static function update(int $id): never {
        requireAdmin();
        $body  = getJsonBody();
        $name  = sanitize($body['name'] ?? '');
        $color = sanitize($body['color'] ?? '');

        $fields = [];
        $params = [];
        if ($name)  { $fields[] = 'name = ?';  $params[] = $name;  }
        if ($color) {
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) respondError('Color hex inválido');
            $fields[] = 'color = ?'; $params[] = $color;
        }
        if (!$fields) respondError('Nada que actualizar');

        $params[] = $id;
        try {
            $db = Database::getInstance();
            $db->prepare('UPDATE technologies SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) respondError('Ese nombre ya existe', 409);
            respondError('Error al actualizar', 500);
        }
        respondOk(null, 'Tecnología actualizada');
    }

    /** DELETE /technologies/:id — solo admin */
    public static function destroy(int $id): never {
        requireAdmin();
        $db   = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM technologies WHERE id = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) respondNotFound('Tecnología no encontrada');
        respondOk(null, 'Tecnología eliminada');
    }
}