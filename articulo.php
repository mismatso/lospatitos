<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/blog.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$articulo = $slug !== '' ? get_articulo_por_slug($slug) : null;

if ($articulo === null) {
    http_response_code(404);
    render_header('Artículo no encontrado', 'blog');
    echo '<section class="section"><div class="container text-center">';
    echo '<h2>Artículo no encontrado 📰</h2><p class="muted">La entrada que buscas no existe.</p>';
    echo '<a class="button button--primary mt-1" href="blog.php">Volver al blog</a>';
    echo '</div></section>';
    render_footer();
    exit;
}

render_header($articulo['titulo'], 'blog');
?>
<section class="section">
    <div class="container">
        <article class="article">
            <p class="breadcrumb"><a href="blog.php">Blog</a> › <?= e($articulo['titulo']) ?></p>
            <div style="font-size:3rem;"><?= e($articulo['emoji']) ?></div>
            <h1 class="page-title"><?= e($articulo['titulo']) ?></h1>
            <p class="article__meta">
                <?= e(date('d/m/Y', strtotime((string) $articulo['fecha_publicacion']))) ?> · por <?= e($articulo['autor']) ?>
            </p>
            <div class="article__body">
                <?php foreach (preg_split('/\n+/', (string) $articulo['contenido']) as $parrafo): ?>
                    <?php if (trim($parrafo) !== ''): ?><p><?= e(trim($parrafo)) ?></p><?php endif; ?>
                <?php endforeach; ?>
            </div>
            <a class="button button--ghost mt-2" href="blog.php">← Volver al blog</a>
        </article>
    </div>
</section>
<?php
render_footer();
