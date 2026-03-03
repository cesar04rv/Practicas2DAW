<?php
// ============================================================
// backend/config/Database.php
// Singleton PDO — una única conexión por request
// ============================================================

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    /** Devuelve la instancia PDO compartida */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,  // prepared statements reales
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // En producción nunca mostrar el mensaje real
                $msg = APP_DEBUG ? $e->getMessage() : 'Database connection error';
                http_response_code(503);
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
        }
        return self::$instance;
    }

    // Evitar clonación e instanciación directa
    private function __construct() {}
    private function __clone()    {}
}