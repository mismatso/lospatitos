<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cart.php';

/**
 * Enlaces de la navegación pública.
 * El estado activo se resalta comparando con $active.
 */
function nav_links(): array
{
    return [
        'inicio'    => ['Inicio', 'index.php'],
        'productos' => ['Catálogo', 'productos.php'],
        'nosotros'  => ['Nosotros', 'nosotros.php'],
        'blog'      => ['Blog', 'blog.php'],
        'contacto'  => ['Contacto', 'contacto.php'],
    ];
}

/**
 * Imprime el inicio del documento: <head>, navbar y apertura del <main>.
 * Reutiliza is_authenticated(), current_user(), cart_count(), e().
 */
function render_header(string $title, string $active = ''): void
{
    $fullTitle = $title === '' ? 'Los Patitos' : $title . ' | Los Patitos';
    $user = current_user();
    $cartCount = cart_count();
    $flash = get_flash();
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($fullTitle) ?></title>
    <meta name="description" content="Los Patitos: la tienda de patitos de hule más feliz. Clásicos, ediciones especiales, packs y accesorios.">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header class="navbar">
        <div class="navbar__inner">
            <a class="navbar__brand" href="index.php">
                <span class="navbar__logo">🦆</span>
                <span class="navbar__name">Los Patitos</span>
            </a>

            <input type="checkbox" id="nav-toggle" class="navbar__toggle-input">
            <label for="nav-toggle" class="navbar__toggle" aria-label="Abrir menú">☰</label>

            <nav class="navbar__nav">
                <?php foreach (nav_links() as $key => [$label, $href]): ?>
                    <a
                        class="navbar__link<?= $active === $key ? ' is-active' : '' ?>"
                        href="<?= e($href) ?>"
                    ><?= e($label) ?></a>
                <?php endforeach; ?>

                <span class="navbar__sep"></span>

                <?php if ($user): ?>
                    <a class="navbar__link<?= $active === 'carrito' ? ' is-active' : '' ?>" href="carrito.php">
                        🛒 Carrito<?php if ($cartCount > 0): ?> <span class="navbar__badge"><?= (int) $cartCount ?></span><?php endif; ?>
                    </a>
                    <a class="navbar__link<?= $active === 'cuenta' ? ' is-active' : '' ?>" href="dashboard.php">Mi cuenta</a>
                    <form method="post" action="logout.php" class="navbar__logout">
                        <?= csrf_input() ?>
                        <button type="submit" class="button button--ghost button--sm">Salir</button>
                    </form>
                <?php else: ?>
                    <a class="navbar__link<?= $active === 'carrito' ? ' is-active' : '' ?>" href="carrito.php">
                        🛒 Carrito<?php if ($cartCount > 0): ?> <span class="navbar__badge"><?= (int) $cartCount ?></span><?php endif; ?>
                    </a>
                    <a class="navbar__link" href="login.php">Entrar</a>
                    <a class="button button--primary button--sm" href="registro.php">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="site-main">
        <?php if ($flash): ?>
            <div class="container">
                <div class="alert alert--<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            </div>
        <?php endif; ?>
<?php
}

/**
 * Cierra el <main>, imprime el footer y los scripts.
 */
function render_footer(): void
{
    $year = '2026';
    ?>
    </main>

    <footer class="footer">
        <div class="footer__inner">
            <div class="footer__col">
                <div class="footer__brand"><span>🦆</span> Los Patitos</div>
                <p class="footer__tagline">Los patitos más felices para tu hogar desde 2018.</p>
            </div>
            <div class="footer__col">
                <h4>Tienda</h4>
                <a href="productos.php">Catálogo</a>
                <a href="productos.php?categoria=edicion-especial">Edición Especial</a>
                <a href="productos.php?categoria=packs">Packs</a>
                <a href="productos.php?categoria=accesorios">Accesorios</a>
            </div>
            <div class="footer__col">
                <h4>Compañía</h4>
                <a href="nosotros.php">Nosotros</a>
                <a href="blog.php">Blog</a>
                <a href="contacto.php">Contacto</a>
            </div>
            <div class="footer__col">
                <h4>Síguenos</h4>
                <p class="footer__social">🐦 🦆 📸 🎵</p>
                <p class="footer__muted">hola@lospatitos.com<br>+506 2222 0000</p>
            </div>
        </div>
        <div class="footer__bottom">
            <p>© <?= e($year) ?> Los Patitos S.A. · Sitio demo ficticio para fines educativos. | By. Mizaq Screencasts</p>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
<?php
}

/**
 * Formatea un precio en colones para mostrarlo de forma consistente.
 */
function money(float|string|int $amount): string
{
    return '$' . number_format((float) $amount, 2, '.', ',');
}

/**
 * Imprime estrellas para una calificación de 1 a 5.
 */
function stars(int $rating): string
{
    $rating = max(0, min(5, $rating));
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}

/**
 * Devuelve la etiqueta <img> de un producto a partir de la ruta guardada en BD.
 * Si el registro no tiene imagen, cae de vuelta al emoji como respaldo.
 *
 * @param array $datos  Fila con al menos 'imagen' y/o 'emoji' y 'nombre'.
 * @param string $class Clase CSS para dimensionar la imagen según el contexto.
 */
function producto_img(array $datos, string $class = 'media-img'): string
{
    $src = trim((string) ($datos['imagen'] ?? ''));
    $nombre = e((string) ($datos['nombre'] ?? $datos['nombre_producto'] ?? 'Producto'));

    if ($src !== '') {
        return '<img class="' . e($class) . '" src="' . e($src) . '" alt="' . $nombre . '" loading="lazy">';
    }

    return '<span class="' . e($class) . ' media-img--emoji">' . e((string) ($datos['emoji'] ?? '🦆')) . '</span>';
}
