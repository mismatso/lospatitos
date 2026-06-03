<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/catalog.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$producto = $slug !== '' ? get_producto_por_slug($slug) : null;

if ($producto === null) {
    http_response_code(404);
    render_header('Producto no encontrado', 'productos');
    echo '<section class="section"><div class="container text-center">';
    echo '<h2>Patito no encontrado 🦆</h2><p class="muted">El producto que buscas no existe o ya no está disponible.</p>';
    echo '<a class="button button--primary mt-1" href="productos.php">Volver al catálogo</a>';
    echo '</div></section>';
    render_footer();
    exit;
}

$productoId = (int) $producto['id'];
$user = current_user();
$errors = [];

// Crear / actualizar reseña (solo autenticado).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'resena') {
    if ($user === null) {
        set_flash('error', 'Debe iniciar sesión para dejar una reseña.');
        redirect('login.php');
    }

    if (!csrf_validate(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
        $errors[] = 'La solicitud no es válida. Recargue la página e intente de nuevo.';
    }

    $calificacion = (int) ($_POST['calificacion'] ?? 0);
    $comentario = trim((string) ($_POST['comentario'] ?? ''));

    if ($calificacion < 1 || $calificacion > 5) {
        $errors[] = 'Seleccione una calificación entre 1 y 5 estrellas.';
    }

    if ($errors === []) {
        guardar_resena((int) $user['id'], $productoId, $calificacion, $comentario);
        set_flash('success', '¡Gracias por tu reseña! 🦆');
        redirect('producto.php?slug=' . urlencode($slug));
    }
}

$resenas = get_resenas_de_producto($productoId);
$resumen = get_resumen_resenas($productoId);
$miResena = $user ? get_resena_de_usuario_para_producto((int) $user['id'], $productoId) : null;

render_header($producto['nombre'], 'productos');
?>
<section class="section">
    <div class="container">
        <p class="breadcrumb"><a href="productos.php">Catálogo</a> › <?= e($producto['categoria'] ?? 'General') ?> › <?= e($producto['nombre']) ?></p>

        <?php if ($errors !== []): ?>
            <div class="alert alert--error">
                <strong>Revise lo siguiente:</strong>
                <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <div class="detail">
            <div class="detail__media" style="background: linear-gradient(160deg, <?= e($producto['color_hex']) ?>33, <?= e($producto['color_hex']) ?>11);">
                <?= e($producto['emoji']) ?>
            </div>
            <div>
                <span class="product__cat"><?= e($producto['categoria'] ?? 'General') ?></span>
                <h1 class="page-title"><?= e($producto['nombre']) ?></h1>
                <p class="muted">
                    <span class="review__stars"><?= e(stars((int) round($resumen['promedio']))) ?></span>
                    <?= $resumen['total'] > 0 ? e((string) $resumen['promedio']) . ' · ' . (int) $resumen['total'] . ' reseña(s)' : 'Sin reseñas todavía' ?>
                </p>
                <p class="detail__price"><?= e(money($producto['precio'])) ?></p>
                <p><?= nl2br(e($producto['descripcion'] ?? '')) ?></p>
                <p class="muted"><?= (int) $producto['stock'] > 0 ? '✅ En stock (' . (int) $producto['stock'] . ' disponibles)' : '❌ Agotado' ?></p>

                <form method="post" action="carrito.php" class="form" style="max-width:320px;">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="producto_id" value="<?= $productoId ?>">
                    <input type="hidden" name="redirect" value="producto.php?slug=<?= e(urlencode($slug)) ?>">
                    <div class="form__group">
                        <span>Cantidad</span>
                        <input class="qty" type="number" name="cantidad" value="1" min="1" max="<?= max(1, (int) $producto['stock']) ?>">
                    </div>
                    <button type="submit" class="button button--primary button--block"<?= (int) $producto['stock'] <= 0 ? ' disabled' : '' ?>>🛒 Agregar al carrito</button>
                </form>
            </div>
        </div>

        <div class="card mt-2">
            <h2>Reseñas de clientes</h2>

            <?php if ($user): ?>
                <form method="post" class="form mb-2" style="max-width:520px;">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="resena">
                    <div class="form__group">
                        <span><?= $miResena ? 'Actualiza tu reseña' : 'Deja tu reseña' ?></span>
                        <select name="calificacion">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>"<?= ($miResena && (int) $miResena['calificacion'] === $i) || (!$miResena && $i === 5) ? ' selected' : '' ?>>
                                    <?= str_repeat('★', $i) ?> (<?= $i ?>)
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form__group">
                        <textarea name="comentario" maxlength="500" placeholder="Cuéntanos qué te pareció…"><?= e($miResena['comentario'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="button button--secondary"><?= $miResena ? 'Actualizar reseña' : 'Publicar reseña' ?></button>
                </form>
            <?php else: ?>
                <p class="muted"><a href="login.php">Inicia sesión</a> para dejar tu reseña.</p>
            <?php endif; ?>

            <?php if ($resenas === []): ?>
                <p class="muted">Sé el primero en reseñar este patito.</p>
            <?php else: ?>
                <?php foreach ($resenas as $r): ?>
                    <div class="review">
                        <div class="review__stars"><?= e(stars((int) $r['calificacion'])) ?></div>
                        <strong><?= e($r['autor']) ?></strong>
                        <span class="muted"> · <?= e(date('d/m/Y', strtotime((string) $r['fecha_creacion']))) ?></span>
                        <?php if (!empty($r['comentario'])): ?><p><?= nl2br(e($r['comentario'])) ?></p><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
render_footer();
