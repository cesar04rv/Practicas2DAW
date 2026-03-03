<?php
// ============================================================
// backend/controllers/UserController.php
// ============================================================

class UserController {

    /** GET /users — solo admin */
    public static function index(): never {
        requireAdmin();
        $db   = Database::getInstance();
        $stmt = $db->query('SELECT id, name, email, role, active, created_at FROM users ORDER BY name');
        respondOk($stmt->fetchAll());
    }

    /** GET /users/:id */
    public static function show(int $id): never {
        requireAuth();
        $db   = Database::getInstance();
        $stmt = $db->prepare('SELECT id, name, email, role, active, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) respondNotFound('Usuario no encontrado');
        respondOk($user);
    }

    /** POST /users — solo admin */
    public static function store(): never {
        requireAuth();
        $body = getJsonBody();

        $name  = sanitize($body['name']  ?? '');
        $email = sanitize($body['email'] ?? '');
        $pass  = (string)($body['password'] ?? '');
        $role  = in_array($body['role'] ?? '', ['admin','viewer']) ? $body['role'] : 'viewer';

        if (!$name || !$email || !$pass) respondError('name, email y password son requeridos');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respondError('Email inválido');
        if (strlen($pass) < 8) respondError('La contraseña debe tener al menos 8 caracteres');

        $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
        $db   = Database::getInstance();

        try {
            $stmt = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)');
            $stmt->execute([$name, $email, $hash, $role]);
            $id = (int)$db->lastInsertId();
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) respondError('El email ya está registrado', 409);
            respondError('Error al crear usuario', 500);
        }

        respondCreated(['id' => $id], 'Usuario creado');
    }

    /** PUT /users/:id — solo admin */
    public static function update(int $id): never {
        requireAdmin();
        $body  = getJsonBody();
        $db    = Database::getInstance();

        $fields = [];
        $params = [];

        if (isset($body['name'])) {
            $fields[] = 'name = ?';
            $params[] = sanitize($body['name']);
        }
        if (isset($body['email'])) {
            if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) respondError('Email inválido');
            $fields[] = 'email = ?';
            $params[] = sanitize($body['email']);
        }
        if (isset($body['password']) && $body['password'] !== '') {
            if (strlen($body['password']) < 8) respondError('La contraseña debe tener al menos 8 caracteres');
            $fields[] = 'password = ?';
            $params[] = password_hash($body['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }
        if (isset($body['role']) && in_array($body['role'], ['admin','viewer'])) {
            $fields[] = 'role = ?';
            $params[] = $body['role'];
        }
        if (isset($body['active'])) {
            $fields[] = 'active = ?';
            $params[] = (int)(bool)$body['active'];
        }

        if (!$fields) respondError('Nada que actualizar');

        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        try {
            $db->prepare($sql)->execute($params);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) respondError('El email ya está en uso', 409);
            respondError('Error al actualizar', 500);
        }
        respondOk(null, 'Usuario actualizado');
    }

    /** DELETE /users/:id — solo admin */
    public static function destroy(int $id): never {
        $current = requireAdmin();
        if ($current['id'] === $id) respondError('No puedes eliminar tu propio usuario');

        $db   = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        try {
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            respondError('No se puede eliminar: el usuario tiene proyectos asignados', 409);
        }
        if ($stmt->rowCount() === 0) respondNotFound('Usuario no encontrado');
        respondOk(null, 'Usuario eliminado');
    }
}