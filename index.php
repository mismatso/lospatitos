<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/catalog.php';

$destacados = get_destacados(6);
$categorias = get_categorias();

render_header('Inicio', 'inicio');
?>
<section class="hero">
    <div class="container hero__inner">
        <div>
            <span class="eyebrow">🦆 Tienda de patitos de hule</span>
            <h1>Los patitos más felices para tu hogar</h1>
            <p>Clásicos amarillos, ediciones especiales de colección, packs familiares y accesorios. Llevamos alegría flotante a tu bañera desde 2018.</p>
            <div class="hero__actions">
                <a class="button button--primary" href="productos.php">Ver catálogo</a>
                <a class="button button--ghost" href="nosotros.php">Conócenos</a>
            </div>
        </div>
        <div class="hero__art" aria-hidden="true">🦆</div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__head">
            <span class="eyebrow">Lo más querido</span>
            <h2>Patitos destacados</h2>
            <p>Una selección de nuestros favoritos para empezar tu colección.</p>
        </div>

        <div class="grid grid--products">
            <?php foreach ($destacados as $p): ?>
                <article class="product">
                    <div class="product__media" style="background: <?= e($p['color_hex']) ?>22;">
                        <span class="product__tag">Destacado</span>
                        <?= e($p['emoji']) ?>
                    </div>
                    <div class="product__body">
                        <h3 class="product__name"><a href="producto.php?slug=<?= e($p['slug']) ?>"><?= e($p['nombre']) ?></a></h3>
                        <div class="product__price"><?= e(money($p['precio'])) ?></div>
                        <div class="product__foot">
                            <a class="button button--ghost button--sm" href="producto.php?slug=<?= e($p['slug']) ?>">Ver</a>
                            <form method="post" action="carrito.php" style="margin:0;">
                                <?= csrf_input() ?>
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="producto_id" value="<?= (int) $p['id'] ?>">
                                <input type="hidden" name="redirect" value="index.php">
                                <button type="submit" class="button button--primary button--sm">🛒 Agregar</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" style="background: #fff;">
    <div class="container">
        <div class="section__head">
            <span class="eyebrow">Explora por estilo</span>
            <h2>Nuestras categorías</h2>
        </div>
        <div class="grid grid--3">
            <?php foreach ($categorias as $c): ?>
                <a class="card feature" href="productos.php?categoria=<?= e($c['slug']) ?>" style="text-decoration:none;">
                    <span class="feature__icon">🦆</span>
                    <h3><?= e($c['nombre']) ?></h3>
                    <p><?= e($c['descripcion']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid grid--3">
            <div class="card feature">
                <span class="feature__icon">🚚</span>
                <h3>Envío flotante</h3>
                <p>Entregamos en todo el país en 24-48 horas. Empaque cuidado para que tu patito llegue impecable.</p>
            </div>
            <div class="card feature">
                <span class="feature__icon">♻️</span>
                <h3>Materiales seguros</h3>
                <p>Todos nuestros patitos son libres de BPA y aptos para los más pequeños de la familia.</p>
            </div>
            <div class="card feature">
                <span class="feature__icon">💛</span>
                <h3>Garantía feliz</h3>
                <p>¿No quedaste satisfecho? Te devolvemos tu dinero sin preguntas durante 30 días.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container text-center">
        <div class="card" style="background: linear-gradient(160deg,#fff6d6,#ffe9b8);">
            <h2>Únete a la bandada 🦆</h2>
            <p class="muted">Crea tu cuenta para guardar tu carrito, seguir tus pedidos y dejar reseñas.</p>
            <a class="button button--secondary mt-1" href="registro.php">Crear cuenta gratis</a>
        </div>
    </div>
</section>
<?php
render_footer();
