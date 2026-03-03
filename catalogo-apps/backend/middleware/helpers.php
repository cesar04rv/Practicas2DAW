<?php
// ============================================================
// backend/middleware/helpers.php
// Funciones de respuesta JSON estandarizada
// ============================================================

/**
 * Respuesta JSON unificada.
 * Estructura: { success, message?, data?, meta? }
 */
function respond(bool $success, mixed $data = null, string $message = '', int $code = 200, array $meta = []): never {
    http_response_code($code);
    $payload = ['success' => $success];
    if ($message !== '')    $payload['message'] = $message;
    if ($data   !== null)   $payload['data']    = $data;
    if (!empty($meta))      $payload['meta']    = $meta;
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function respondOk(mixed $data = null, string $message = '', array $meta = []): never {
    respond(true, $data, $message, 200, $meta);
}

function respondCreated(mixed $data = null, string $message = 'Recurso creado'): never {
    respond(true, $data, $message, 201);
}

function respondError(string $message, int $code = 400): never {
    respond(false, null, $message, $code);
}

function respondUnauthorized(string $message = 'No autorizado'): never {
    respondError($message, 401);
}

function respondForbidden(string $message = 'Acceso denegado'): never {
    respondError($message, 403);
}

function respondNotFound(string $message = 'No encontrado'): never {
    respondError($message, 404);
}

/** Devuelve el body JSON parseado o lanza error */
function getJsonBody(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        respondError('JSON inválido en el cuerpo de la petición');
    }
    return $data ?? [];
}

/** Sanitiza una cadena básica */
function sanitize(mixed $value): string {
    return trim(strip_tags((string)($value ?? '')));
}