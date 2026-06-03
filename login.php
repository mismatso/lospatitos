<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';

require_guest();

$errors = [];
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim((string) ($_POST['identifier'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_validate(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
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
            set_flash('success', '¡Bienvenido de nuevo, ' . $user['nombre'] . '! 🦆');
            redirect('dashboard.php');
        }
    }
}

render_header('Iniciar sesión', 'cuenta');
?>
<section class="section">
    <div class="container">
        <div class="auth-wrap">
            <div class="brand">
                <div class="brand__art">🦆</div>
                <h1>Iniciar sesión</h1>
                <p>Accede a tu cuenta para comprar y seguir tus pedidos.</p>
            </div>

            <div class="card">
                <?php if ($errors !== []): ?>
                    <div class="alert alert--error">
                        <strong>Se encontraron errores:</strong>
                        <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="post" class="form" data-validate="login" novalidate>
                    <?= csrf_input() ?>
                    <label class="form__group">
                        <span>Usuario o correo</span>
                        <input type="text" name="identifier" value="<?= e($identifier) ?>" placeholder="analopez o ana@lospatitos.com" required maxlength="150">
                    </label>
                    <label class="form__group">
                        <span>Contraseña</span>
                        <input type="password" name="password" placeholder="Ingrese su contraseña" required minlength="8">
                    </label>
                    <button type="submit" class="button button--primary button--block">Entrar</button>
                </form>

                <div class="auth-footer">
                    <p>Usuarios de prueba: <strong>analopez / Patito123!</strong></p>
                    <a href="registro.php">¿No tienes cuenta? Regístrate</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
render_footer();
