<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/catalog.php';

$user = require_auth();
$editId = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
        set_flash('error', 'La solicitud no es válida. Intente de nuevo.');
        redirect('mis-resenas.php');
    }

    $accion = (string) ($_POST['action'] ?? '');
    $resenaId = (int) ($_POST['resena_id'] ?? 0);
    $resena = get_resena_por_id($resenaId);

    // Validar pertenencia.
    if ($resena === null || (int) $resena['usuario_id'] !== (int) $user['id']) {
        set_flash('error', 'Esa reseña no existe o no te pertenece.');
        redirect('mis-resenas.php');
    }

    if ($accion === 'editar') {
        $calificacion = (int) ($_POST['calificacion'] ?? 5);
        $comentario = trim((string) ($_POST['comentario'] ?? ''));
        actualizar_resena($resenaId, (int) $user['id'], $calificacion, $comentario);
        set_flash('success', 'Reseña actualizada. ⭐');
        redirect('mis-resenas.php');
    }

    if ($accion === 'eliminar') {
        eliminar_resena($resenaId, (int) $user['id']);
        set_flash('success', 'Reseña eliminada.');
        redirect('mis-resenas.php');
    }

    redirect('mis-resenas.php');
}

$editId = (int) ($_GET['editar'] ?? 0);
$resenas = get_resenas_de_usuario((int) $user['id']);

render_header('Mis reseñas', 'cuenta');
?>
<section class="section">
    <div class="container">
        <p class="breadcrumb"><a href="dashboard.php">Mi cuenta</a> › Reseñas</p>
        <h1 class="page-title">⭐ Mis reseñas</h1>

        <?php if ($resenas === []): ?>
            <div class="card text-center mt-2">
                <p class="muted">Aún no has escrito reseñas. Cuéntanos qué piensas de tus patitos.</p>
                <a class="button button--primary mt-1" href="productos.php">Ver catálogo</a>
            </div>
        <?php else: ?>
            <div class="grid grid--2 mt-2">
                <?php foreach ($resenas as $r): ?>
                    <div class="card">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <h3 style="margin:0;display:flex;align-items:center;gap:0.5rem;">
                                <?= producto_img(['imagen' => $r['imagen'], 'emoji' => $r['emoji'], 'nombre' => $r['producto']], 'thumb-img') ?>
                                <a href="producto.php?slug=<?= e($r['producto_slug']) ?>"><?= e($r['producto']) ?></a>
                            </h3>
                            <span class="muted" style="font-size:0.85rem;"><?= e(date('d/m/Y', strtotime((string) $r['fecha_creacion']))) ?></span>
                        </div>

                        <?php if ($editId === (int) $r['id']): ?>
                            <form method="post" class="form mt-1">
                                <?= csrf_input() ?>
                                <input type="hidden" name="action" value="editar">
                                <input type="hidden" name="resena_id" value="<?= (int) $r['id'] ?>">
                                <label class="form__group">
                                    <span>Calificación</span>
                                    <select name="calificacion">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <option value="<?= $i ?>"<?= (int) $r['calificacion'] === $i ? ' selected' : '' ?>><?= str_repeat('★', $i) ?> (<?= $i ?>)</option>
                                        <?php endfor; ?>
                                    </select>
                                </label>
                                <label class="form__group">
                                    <textarea name="comentario" maxlength="500"><?= e($r['comentario'] ?? '') ?></textarea>
                                </label>
                                <div style="display:flex;gap:0.5rem;">
                                    <button type="submit" class="button button--primary button--sm">Guardar</button>
                                    <a class="button button--ghost button--sm" href="mis-resenas.php">Cancelar</a>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="review__stars mt-1"><?= e(stars((int) $r['calificacion'])) ?></div>
                            <?php if (!empty($r['comentario'])): ?><p><?= nl2br(e($r['comentario'])) ?></p><?php endif; ?>
                            <div style="display:flex;gap:0.5rem;">
                                <a class="button button--ghost button--sm" href="mis-resenas.php?editar=<?= (int) $r['id'] ?>">Editar</a>
                                <form method="post" data-confirm="¿Eliminar esta reseña?" style="margin:0;">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="action" value="eliminar">
                                    <input type="hidden" name="resena_id" value="<?= (int) $r['id'] ?>">
                                    <button type="submit" class="button button--danger button--sm">Eliminar</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
render_footer();
