<?php
// ============================================================
// backend/controllers/AuthController.php
// ============================================================

class AuthController {

    /** POST /auth/login */
    public static function login(): never {
        $body  = getJsonBody();
        $email = sanitize($body['email'] ?? '');
        $pass  = (string)($body['password'] ?? '');

        if (!$email || !$pass) {
            respondError('Email y contraseña requeridos');
        }

        $db   = Database::getInstance();
        $stmt = $db->prepare('SELECT id, name, email, password, role, active FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verificar usuario, contraseña y cuenta activa
        if (!$user || !password_verify($pass, $user['password']) || !$user['active']) {
            respondError('Credenciales incorrectas', 401);
        }

        loginUser($user);
        unset($user['password'], $user['active']);  // nunca devolver hash
        respondOk($user, 'Sesión iniciada');
    }

    /** POST /auth/logout */
    public static function logout(): never {
        requireAuth();
        logoutUser();
        respondOk(null, 'Sesión cerrada');
    }

    /** GET /auth/me — devuelve el usuario autenticado actual */
    public static function me(): never {
        $user = requireAuth();
        respondOk($user);
    }
}