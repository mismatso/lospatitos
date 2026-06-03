<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers.php';

/**
 * Busca un usuario activo por correo o nombre de usuario.
 */
function find_user_by_identifier(string $identifier): ?array
{
    $sql = 'SELECT id, nombre, correo, usuario, password_hash, estado, fecha_creacion
            FROM usuarios
            WHERE (correo = :correo OR usuario = :usuario)
            LIMIT 1';

    $stmt = db()->prepare($sql);
    $stmt->execute([
        'correo' => $identifier,
        'usuario' => $identifier,
    ]);
    $user = $stmt->fetch();

    return $user ?: null;
}

/**
 * Busca un usuario por su ID para mantener la sesión sincronizada con la BD.
 */
function find_user_by_id(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, nombre, correo, usuario, estado, fecha_creacion
         FROM usuarios
         WHERE id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch();

    return $user ?: null;
}

/**
 * Indica si hay una sesión autenticada.
 */
function is_authenticated(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Inicia sesión guardando solo los datos mínimos necesarios.
 */
function login_user(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = $user['nombre'];
    $_SESSION['username'] = $user['usuario'];
}

/**
 * Cierra la sesión actual de forma segura.
 */
function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Devuelve el usuario autenticado actualizado desde la base de datos.
 */
function current_user(): ?array
{
    if (!is_authenticated()) {
        return null;
    }

    $user = find_user_by_id((int) $_SESSION['user_id']);

    if (!$user || $user['estado'] !== 'activo') {
        logout_user();
        return null;
    }

    return $user;
}

/**
 * Evita que usuarios autenticados vuelvan a ver login o registro.
 */
function require_guest(): void
{
    if (is_authenticated()) {
        redirect('dashboard.php');
    }
}

/**
 * Protege rutas internas.
 */
function require_auth(): array
{
    $user = current_user();

    if ($user === null) {
        set_flash('error', 'Debe iniciar sesión para continuar o su sesión ha expirado.');
        redirect('index.php');
    }

    return $user;
}
