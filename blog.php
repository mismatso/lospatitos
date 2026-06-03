<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/blog.php';

$articulos = get_articulos();

render_header('Blog', 'blog');
?>
<section class="section">
    <div class="container">
        <div class="section__head">
            <span class="eyebrow">El blog</span>
            <h2>Noticias del estanque</h2>
            <p>Historias, consejos y novedades del mundo de los patitos de hule.</p>
        </div>

        <?php if ($articulos === []): ?>
            <p class="text-center muted">Aún no hay artículos publicados.</p>
        <?php else: ?>
            <div class="grid grid--3">
                <?php foreach ($articulos as $a): ?>
                    <article class="card">
                        <div style="font-size:2.5rem;"><?= e($a['emoji']) ?></div>
                        <p class="muted" style="font-size:0.85rem;margin:0.3rem 0;">
                            <?= e(date('d/m/Y', strtotime((string) $a['fecha_publicacion']))) ?> · <?= e($a['autor']) ?>
                        </p>
                        <h3 style="margin:0.2rem 0;"><a href="articulo.php?slug=<?= e($a['slug']) ?>"><?= e($a['titulo']) ?></a></h3>
                        <p class="muted"><?= e($a['resumen']) ?></p>
                        <a href="articulo.php?slug=<?= e($a['slug']) ?>">Leer más →</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
render_footer();
