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
        'SELECT id, nombre, correo, usuario, telefono, direccion, estado, fecha_creacion
         FROM usuarios
         WHERE id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch();

    return $user ?: null;
}

/**
 * Actualiza los datos de perfil del usuario.
 */
function update_user_profile(int $id, string $nombre, string $correo, ?string $telefono, ?string $direccion): void
{
    $stmt = db()->prepare(
        'UPDATE usuarios
         SET nombre = :nombre, correo = :correo, telefono = :telefono, direccion = :direccion
         WHERE id = :id'
    );
    $stmt->execute([
        'nombre' => $nombre,
        'correo' => $correo,
        'telefono' => ($telefono === '' ? null : $telefono),
        'direccion' => ($direccion === '' ? null : $direccion),
        'id' => $id,
    ]);

    $_SESSION['user_name'] = $nombre;
}

/**
 * Devuelve el hash de contraseña vigente del usuario (para verificarla).
 */
function get_user_password_hash(int $id): ?string
{
    $stmt = db()->prepare('SELECT password_hash FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row['password_hash'] ?? null;
}

/**
 * Cambia la contraseña del usuario (espera el texto plano nuevo).
 */
function update_user_password(int $id, string $nuevaPassword): void
{
    $stmt = db()->prepare('UPDATE usuarios SET password_hash = :hash WHERE id = :id');
    $stmt->execute([
        'hash' => password_hash($nuevaPassword, PASSWORD_BCRYPT),
        'id' => $id,
    ]);
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
        redirect('login.php');
    }

    return $user;
}
