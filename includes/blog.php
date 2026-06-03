<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * Acceso a datos del blog / noticias.
 */

/**
 * Lista los artículos del blog, del más reciente al más antiguo.
 */
function get_articulos(): array
{
    return db()->query(
        'SELECT id, titulo, slug, resumen, autor, emoji, fecha_publicacion
         FROM articulos
         ORDER BY fecha_publicacion DESC'
    )->fetchAll();
}

/**
 * Busca un artículo por su slug.
 */
function get_articulo_por_slug(string $slug): ?array
{
    $stmt = db()->prepare(
        'SELECT id, titulo, slug, resumen, contenido, autor, emoji, fecha_publicacion
         FROM articulos WHERE slug = :slug LIMIT 1'
    );
    $stmt->execute(['slug' => $slug]);
    return $stmt->fetch() ?: null;
}
