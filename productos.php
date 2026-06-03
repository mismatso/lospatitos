<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/catalog.php';

$categoriaSlug = trim((string) ($_GET['categoria'] ?? ''));
$busqueda = trim((string) ($_GET['q'] ?? ''));

$categorias = get_categorias();
$categoriaActual = $categoriaSlug !== '' ? get_categoria_por_slug($categoriaSlug) : null;
$productos = get_productos($categoriaActual ? $categoriaSlug : null, $busqueda !== '' ? $busqueda : null);

render_header('Catálogo', 'productos');
?>
<section class="section">
    <div class="container">
        <div class="section__head">
            <span class="eyebrow">Catálogo</span>
            <h2><?= $categoriaActual ? e($categoriaActual['nombre']) : 'Todos los patitos' ?></h2>
            <p><?= $categoriaActual ? e($categoriaActual['descripcion']) : 'Encuentra el patito perfecto para ti o para regalar.' ?></p>
        </div>

        <form method="get" class="form" style="max-width:520px;margin:0 auto 1.5rem;">
            <div class="form__group">
                <input type="search" name="q" value="<?= e($busqueda) ?>" placeholder="Buscar patitos…" aria-label="Buscar">
            </div>
        </form>

        <div class="chips">
            <a class="chip<?= $categoriaSlug === '' ? ' is-active' : '' ?>" href="productos.php">Todos</a>
            <?php foreach ($categorias as $c): ?>
                <a class="chip<?= $categoriaSlug === $c['slug'] ? ' is-active' : '' ?>" href="productos.php?categoria=<?= e($c['slug']) ?>"><?= e($c['nombre']) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if ($productos === []): ?>
            <p class="text-center muted">No encontramos patitos que coincidan con tu búsqueda. 🦆</p>
        <?php else: ?>
            <div class="grid grid--products">
                <?php foreach ($productos as $p): ?>
                    <article class="product">
                        <div class="product__media" style="background: <?= e($p['color_hex']) ?>22;">
                            <?php if ((int) $p['destacado'] === 1): ?><span class="product__tag">Destacado</span><?php endif; ?>
                            <?= producto_img($p, 'media-img') ?>
                        </div>
                        <div class="product__body">
                            <span class="product__cat"><?= e($p['categoria'] ?? 'General') ?></span>
                            <h3 class="product__name"><a href="producto.php?slug=<?= e($p['slug']) ?>"><?= e($p['nombre']) ?></a></h3>
                            <div class="product__price"><?= e(money($p['precio'])) ?></div>
                            <div class="product__foot">
                                <a class="button button--ghost button--sm" href="producto.php?slug=<?= e($p['slug']) ?>">Ver</a>
                                <form method="post" action="carrito.php" style="margin:0;">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="producto_id" value="<?= (int) $p['id'] ?>">
                                    <input type="hidden" name="redirect" value="productos.php<?= $categoriaSlug !== '' ? '?categoria=' . urlencode($categoriaSlug) : '' ?>">
                                    <button type="submit" class="button button--primary button--sm">🛒 Agregar</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
render_footer();
