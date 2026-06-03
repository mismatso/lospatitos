<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';

require_guest();

$errors = [];
$nombre = '';
$correo = '';
$usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $correo = trim((string) ($_POST['correo'] ?? ''));
    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if (!csrf_validate(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
        $errors[] = 'La solicitud no es válida. Recargue la página e intente nuevamente.';
    }

    if ($nombre === '' || mb_strlen($nombre) < 3) {
        $errors[] = 'El nombre debe tener al menos 3 caracteres.';
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Debe indicar un correo electrónico válido.';
    }

    if (!preg_match('/^[a-zA-Z0-9._-]{4,50}$/', $usuario)) {
        $errors[] = 'El usuario debe tener entre 4 y 50 caracteres y solo puede incluir letras, números, punto, guion o guion bajo.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = 'La confirmación de la contraseña no coincide.';
    }

    if ($errors === []) {
        $sql = 'INSERT INTO usuarios (nombre, correo, usuario, password_hash, estado)
                VALUES (:nombre, :correo, :usuario, :password_hash, :estado)';

        try {
            $stmt = db()->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'correo' => $correo,
                'usuario' => $usuario,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                'estado' => 'activo',
            ]);

            set_flash('success', 'Registro completado. Ya puede iniciar sesión con su nueva cuenta.');
            redirect('login.php');
        } catch (PDOException $exception) {
            if ((int) $exception->getCode() === 23000) {
                $errors[] = 'El correo o el nombre de usuario ya existen. Pruebe con otros valores.';
            } else {
                $errors[] = 'No fue posible completar el registro en este momento.';
            }
        }
    }
}

render_header('Crear cuenta', 'cuenta');
?>
<section class="section">
    <div class="container">
        <div class="auth-wrap">
            <div class="brand">
                <div class="brand__art">🐤</div>
                <h1>Únete a la bandada</h1>
                <p>Crea tu cuenta para comprar, seguir pedidos y dejar reseñas.</p>
            </div>

            <div class="card">
                <?php if ($errors !== []): ?>
                    <div class="alert alert--error">
                        <strong>Se encontraron errores:</strong>
                        <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="post" class="form" data-validate="register" novalidate>
                    <?= csrf_input() ?>
                    <label class="form__group">
                        <span>Nombre completo</span>
                        <input type="text" name="nombre" value="<?= e($nombre) ?>" required minlength="3" maxlength="120">
                    </label>
                    <label class="form__group">
                        <span>Correo electrónico</span>
                        <input type="email" name="correo" value="<?= e($correo) ?>" required maxlength="150">
                    </label>
                    <label class="form__group">
                        <span>Nombre de usuario</span>
                        <input type="text" name="usuario" value="<?= e($usuario) ?>" required minlength="4" maxlength="50" pattern="[a-zA-Z0-9._-]{4,50}">
                    </label>
                    <div class="form__row">
                        <label class="form__group">
                            <span>Contraseña</span>
                            <input type="password" name="password" required minlength="8">
                        </label>
                        <label class="form__group">
                            <span>Confirmar contraseña</span>
                            <input type="password" name="password_confirm" required minlength="8">
                        </label>
                    </div>
                    <button type="submit" class="button button--primary button--block">Registrar cuenta</button>
                </form>

                <div class="auth-footer">
                    <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
render_footer();
