<?php
declare(strict_types=1);

// Habilitar impresion de errores en desarrollo
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

require_guest();

$flash = get_flash();
$errors = [];
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? null;

    if (!csrf_validate(is_string($csrfToken) ? $csrfToken : null)) {
        $errors[] = 'La solicitud no es válida. Recargue la página e intente de nuevo.';
    }

    if ($identifier === '') {
        $errors[] = 'Debe indicar su usuario o correo electrónico.';
    }

    if ($password === '') {
        $errors[] = 'Debe indicar su contraseña.';
    }

    if ($errors === []) {
        $user = find_user_by_identifier($identifier);

        if (!$user || $user['estado'] !== 'activo' || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Credenciales inválidas. Verifique sus datos e intente nuevamente.';
        } else {
            login_user($user);
            set_flash('success', 'Inicio de sesión correcto. Bienvenido de nuevo.');
            redirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | lospatitos.com</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <main class="auth-layout">
        <section class="card auth-card">
            <div class="brand">
                <span class="brand__badge">Demo</span>
                <h1>lospatitos.com</h1>
                <p>Inicie sesión con su usuario o correo para acceder al dashboard protegido.</p>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert--<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="alert alert--error">
                    <strong>Se encontraron errores:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="form" data-validate="login" novalidate>
                <?= csrf_input() ?>

                <label class="form__group">
                    <span>Usuario o correo</span>
                    <input
                        type="text"
                        name="identifier"
                        value="<?= e($identifier) ?>"
                        placeholder="analopez o ana@lospatitos.com"
                        required
                        maxlength="150"
                    >
                </label>

                <label class="form__group">
                    <span>Contraseña</span>
                    <input
                        type="password"
                        name="password"
                        placeholder="Ingrese su contraseña"
                        required
                        minlength="8"
                    >
                </label>

                <button type="submit" class="button button--primary">Entrar</button>
            </form>

            <div class="auth-footer">
                <p>Usuarios de prueba incluidos en la base de datos semilla.</p>
                <a href="registro.php">Crear una cuenta nueva</a>
            </div>
        </section>
    </main>

    <script src="assets/js/app.js"></script>
</body>
</html>
