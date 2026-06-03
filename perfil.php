<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';

$user = require_auth();
$errors = [];
$passwordErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if (!csrf_validate(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
        set_flash('error', 'La solicitud no es válida. Intente de nuevo.');
        redirect('perfil.php');
    }

    if ($action === 'perfil') {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $correo = trim((string) ($_POST['correo'] ?? ''));
        $telefono = trim((string) ($_POST['telefono'] ?? ''));
        $direccion = trim((string) ($_POST['direccion'] ?? ''));

        if (mb_strlen($nombre) < 3) {
            $errors[] = 'El nombre debe tener al menos 3 caracteres.';
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Indique un correo electrónico válido.';
        }

        if ($errors === []) {
            try {
                update_user_profile((int) $user['id'], $nombre, $correo, $telefono, $direccion);
                set_flash('success', 'Perfil actualizado correctamente. 🦆');
                redirect('perfil.php');
            } catch (PDOException $e) {
                if ((int) $e->getCode() === 23000) {
                    $errors[] = 'Ese correo ya está en uso por otra cuenta.';
                } else {
                    $errors[] = 'No fue posible actualizar el perfil.';
                }
            }
        }
        // Reflejar valores enviados en el formulario.
        $user = array_merge($user, [
            'nombre' => $nombre,
            'correo' => $correo,
            'telefono' => $telefono,
            'direccion' => $direccion,
        ]);
    }

    if ($action === 'password') {
        $actual = (string) ($_POST['password_actual'] ?? '');
        $nueva = (string) ($_POST['password_nueva'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');

        $hash = get_user_password_hash((int) $user['id']);
        if ($hash === null || !password_verify($actual, $hash)) {
            $passwordErrors[] = 'La contraseña actual no es correcta.';
        }
        if (strlen($nueva) < 8) {
            $passwordErrors[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        }
        if ($nueva !== $confirm) {
            $passwordErrors[] = 'La confirmación de la contraseña no coincide.';
        }

        if ($passwordErrors === []) {
            update_user_password((int) $user['id'], $nueva);
            set_flash('success', 'Contraseña actualizada correctamente.');
            redirect('perfil.php');
        }
    }
}

render_header('Editar perfil', 'cuenta');
?>
<section class="section">
    <div class="container">
        <p class="breadcrumb"><a href="dashboard.php">Mi cuenta</a> › Perfil</p>
        <h1 class="page-title">Editar perfil</h1>

        <div class="grid grid--2 mt-2">
            <div class="card">
                <h2>Datos personales</h2>
                <?php if ($errors !== []): ?>
                    <div class="alert alert--error"><ul><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form method="post" class="form">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="perfil">
                    <label class="form__group">
                        <span>Nombre completo</span>
                        <input type="text" name="nombre" value="<?= e($user['nombre']) ?>" required minlength="3" maxlength="120">
                    </label>
                    <label class="form__group">
                        <span>Correo electrónico</span>
                        <input type="email" name="correo" value="<?= e($user['correo']) ?>" required maxlength="150">
                    </label>
                    <label class="form__group">
                        <span>Teléfono</span>
                        <input type="text" name="telefono" value="<?= e($user['telefono'] ?? '') ?>" maxlength="30" placeholder="+506 0000 0000">
                    </label>
                    <label class="form__group">
                        <span>Dirección</span>
                        <input type="text" name="direccion" value="<?= e($user['direccion'] ?? '') ?>" maxlength="255" placeholder="Calle, ciudad…">
                    </label>
                    <button type="submit" class="button button--primary">Guardar cambios</button>
                </form>
            </div>

            <div class="card">
                <h2>Cambiar contraseña</h2>
                <?php if ($passwordErrors !== []): ?>
                    <div class="alert alert--error"><ul><?php foreach ($passwordErrors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form method="post" class="form">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="password">
                    <label class="form__group">
                        <span>Contraseña actual</span>
                        <input type="password" name="password_actual" required>
                    </label>
                    <label class="form__group">
                        <span>Nueva contraseña</span>
                        <input type="password" name="password_nueva" required minlength="8">
                    </label>
                    <label class="form__group">
                        <span>Confirmar nueva contraseña</span>
                        <input type="password" name="password_confirm" required minlength="8">
                    </label>
                    <button type="submit" class="button button--secondary">Actualizar contraseña</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php
render_footer();
