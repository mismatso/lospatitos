<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';

$errors = [];
$nombre = '';
$correo = '';
$asunto = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $correo = trim((string) ($_POST['correo'] ?? ''));
    $asunto = trim((string) ($_POST['asunto'] ?? ''));
    $mensaje = trim((string) ($_POST['mensaje'] ?? ''));

    if (!csrf_validate(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
        $errors[] = 'La solicitud no es válida. Recargue la página e intente de nuevo.';
    }

    if (mb_strlen($nombre) < 3) {
        $errors[] = 'Indique su nombre (mínimo 3 caracteres).';
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Indique un correo electrónico válido.';
    }

    if (mb_strlen($asunto) < 3) {
        $errors[] = 'Indique un asunto.';
    }

    if (mb_strlen($mensaje) < 10) {
        $errors[] = 'El mensaje debe tener al menos 10 caracteres.';
    }

    if ($errors === []) {
        $stmt = db()->prepare(
            'INSERT INTO mensajes_contacto (nombre, correo, asunto, mensaje)
             VALUES (:nombre, :correo, :asunto, :mensaje)'
        );
        $stmt->execute([
            'nombre' => $nombre,
            'correo' => $correo,
            'asunto' => $asunto,
            'mensaje' => $mensaje,
        ]);

        set_flash('success', '¡Gracias por escribirnos! Te responderemos pronto. 🦆');
        redirect('contacto.php');
    }
}

render_header('Contacto', 'contacto');
?>
<section class="section">
    <div class="container">
        <div class="section__head">
            <span class="eyebrow">Hablemos</span>
            <h2>Contáctanos</h2>
            <p>¿Dudas, sugerencias o quieres mayoreo de patitos? Escríbenos.</p>
        </div>

        <div class="layout-2col">
            <div class="card">
                <?php if ($errors !== []): ?>
                    <div class="alert alert--error">
                        <strong>Revise lo siguiente:</strong>
                        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="post" class="form" data-validate="contact" novalidate>
                    <?= csrf_input() ?>
                    <div class="form__row">
                        <label class="form__group">
                            <span>Nombre</span>
                            <input type="text" name="nombre" value="<?= e($nombre) ?>" required minlength="3" maxlength="120">
                        </label>
                        <label class="form__group">
                            <span>Correo</span>
                            <input type="email" name="correo" value="<?= e($correo) ?>" required maxlength="150">
                        </label>
                    </div>
                    <label class="form__group">
                        <span>Asunto</span>
                        <input type="text" name="asunto" value="<?= e($asunto) ?>" required minlength="3" maxlength="150">
                    </label>
                    <label class="form__group">
                        <span>Mensaje</span>
                        <textarea name="mensaje" required minlength="10" maxlength="2000"><?= e($mensaje) ?></textarea>
                    </label>
                    <button type="submit" class="button button--primary">Enviar mensaje</button>
                </form>
            </div>

            <aside class="card">
                <h3>Visítanos 🦆</h3>
                <p class="muted">Av. del Estanque 123<br>San José, Costa Rica</p>
                <h3>Escríbenos</h3>
                <p class="muted">hola@lospatitos.com<br>+506 2222 0000</p>
                <h3>Horario</h3>
                <p class="muted">Lun a Vie · 8:00 – 18:00<br>Sáb · 9:00 – 13:00</p>
            </aside>
        </div>
    </div>
</section>
<?php
render_footer();
