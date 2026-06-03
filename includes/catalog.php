<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * Acceso a datos de la tienda: categorías, productos y reseñas.
 * Todas las consultas usan PDO con sentencias preparadas vía db().
 */

/**
 * Devuelve todas las categorías ordenadas por nombre.
 */
function get_categorias(): array
{
    return db()->query('SELECT id, nombre, slug, descripcion FROM categorias ORDER BY id')
        ->fetchAll();
}

/**
 * Busca una categoría por su slug.
 */
function get_categoria_por_slug(string $slug): ?array
{
    $stmt = db()->prepare('SELECT id, nombre, slug, descripcion FROM categorias WHERE slug = :slug LIMIT 1');
    $stmt->execute(['slug' => $slug]);
    return $stmt->fetch() ?: null;
}

/**
 * Lista productos activos con filtros opcionales por categoría (slug) y búsqueda.
 */
function get_productos(?string $categoriaSlug = null, ?string $busqueda = null): array
{
    $sql = "SELECT p.id, p.nombre, p.slug, p.descripcion, p.precio, p.emoji, p.color_hex,
                   p.stock, p.destacado, c.nombre AS categoria, c.slug AS categoria_slug
            FROM productos p
            LEFT JOIN categorias c ON c.id = p.categoria_id
            WHERE p.estado = 'activo'";
    $params = [];

    if ($categoriaSlug !== null && $categoriaSlug !== '') {
        $sql .= ' AND c.slug = :categoria';
        $params['categoria'] = $categoriaSlug;
    }

    if ($busqueda !== null && $busqueda !== '') {
        $sql .= ' AND (p.nombre LIKE :busqueda OR p.descripcion LIKE :busqueda)';
        $params['busqueda'] = '%' . $busqueda . '%';
    }

    $sql .= ' ORDER BY p.destacado DESC, p.nombre ASC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Productos destacados para la home.
 */
function get_destacados(int $limite = 6): array
{
    $sql = "SELECT id, nombre, slug, descripcion, precio, emoji, color_hex, stock
            FROM productos
            WHERE estado = 'activo' AND destacado = 1
            ORDER BY nombre ASC
            LIMIT " . max(1, $limite);
    return db()->query($sql)->fetchAll();
}

/**
 * Busca un producto activo por su slug.
 */
function get_producto_por_slug(string $slug): ?array
{
    $sql = "SELECT p.id, p.nombre, p.slug, p.descripcion, p.precio, p.emoji, p.color_hex,
                   p.stock, p.destacado, c.nombre AS categoria, c.slug AS categoria_slug
            FROM productos p
            LEFT JOIN categorias c ON c.id = p.categoria_id
            WHERE p.slug = :slug AND p.estado = 'activo'
            LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->execute(['slug' => $slug]);
    return $stmt->fetch() ?: null;
}

/**
 * Busca un producto activo por id (usado por el carrito).
 */
function get_producto_por_id(int $id): ?array
{
    $stmt = db()->prepare(
        "SELECT id, nombre, slug, precio, emoji, color_hex, stock
         FROM productos
         WHERE id = :id AND estado = 'activo'
         LIMIT 1"
    );
    $stmt->execute(['id' => $id]);
    return $stmt->fetch() ?: null;
}

/* =====================================================================
 * Reseñas
 * ===================================================================== */

/**
 * Reseñas de un producto junto con el nombre de su autor.
 */
function get_resenas_de_producto(int $productoId): array
{
    $stmt = db()->prepare(
        'SELECT r.id, r.calificacion, r.comentario, r.fecha_creacion,
                r.usuario_id, u.nombre AS autor
         FROM resenas r
         JOIN usuarios u ON u.id = r.usuario_id
         WHERE r.producto_id = :producto_id
         ORDER BY r.fecha_creacion DESC'
    );
    $stmt->execute(['producto_id' => $productoId]);
    return $stmt->fetchAll();
}

/**
 * Calificación promedio y número de reseñas de un producto.
 */
function get_resumen_resenas(int $productoId): array
{
    $stmt = db()->prepare(
        'SELECT COUNT(*) AS total, COALESCE(AVG(calificacion), 0) AS promedio
         FROM resenas WHERE producto_id = :producto_id'
    );
    $stmt->execute(['producto_id' => $productoId]);
    $row = $stmt->fetch();
    return [
        'total' => (int) ($row['total'] ?? 0),
        'promedio' => round((float) ($row['promedio'] ?? 0), 1),
    ];
}

/**
 * Reseñas escritas por un usuario, con el nombre del producto.
 */
function get_resenas_de_usuario(int $usuarioId): array
{
    $stmt = db()->prepare(
        'SELECT r.id, r.calificacion, r.comentario, r.fecha_creacion,
                p.nombre AS producto, p.slug AS producto_slug, p.emoji
         FROM resenas r
         JOIN productos p ON p.id = r.producto_id
         WHERE r.usuario_id = :usuario_id
         ORDER BY r.fecha_creacion DESC'
    );
    $stmt->execute(['usuario_id' => $usuarioId]);
    return $stmt->fetchAll();
}

/**
 * Devuelve la reseña de un usuario sobre un producto, si existe.
 */
function get_resena_de_usuario_para_producto(int $usuarioId, int $productoId): ?array
{
    $stmt = db()->prepare(
        'SELECT id, calificacion, comentario, producto_id, usuario_id
         FROM resenas
         WHERE usuario_id = :usuario_id AND producto_id = :producto_id
         LIMIT 1'
    );
    $stmt->execute(['usuario_id' => $usuarioId, 'producto_id' => $productoId]);
    return $stmt->fetch() ?: null;
}

/**
 * Busca una reseña por id (para editar/eliminar validando dueño).
 */
function get_resena_por_id(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, producto_id, usuario_id, calificacion, comentario
         FROM resenas WHERE id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    return $stmt->fetch() ?: null;
}

/**
 * Crea o actualiza la reseña del usuario sobre un producto (upsert).
 */
function guardar_resena(int $usuarioId, int $productoId, int $calificacion, string $comentario): void
{
    $calificacion = max(1, min(5, $calificacion));
    $stmt = db()->prepare(
        'INSERT INTO resenas (producto_id, usuario_id, calificacion, comentario)
         VALUES (:producto_id, :usuario_id, :calificacion, :comentario)
         ON DUPLICATE KEY UPDATE
            calificacion = VALUES(calificacion),
            comentario = VALUES(comentario)'
    );
    $stmt->execute([
        'producto_id' => $productoId,
        'usuario_id' => $usuarioId,
        'calificacion' => $calificacion,
        'comentario' => $comentario,
    ]);
}

/**
 * Actualiza una reseña existente (usado desde mis-resenas.php).
 */
function actualizar_resena(int $resenaId, int $usuarioId, int $calificacion, string $comentario): void
{
    $calificacion = max(1, min(5, $calificacion));
    $stmt = db()->prepare(
        'UPDATE resenas
         SET calificacion = :calificacion, comentario = :comentario
         WHERE id = :id AND usuario_id = :usuario_id'
    );
    $stmt->execute([
        'calificacion' => $calificacion,
        'comentario' => $comentario,
        'id' => $resenaId,
        'usuario_id' => $usuarioId,
    ]);
}

/**
 * Elimina una reseña validando que pertenezca al usuario.
 */
function eliminar_resena(int $resenaId, int $usuarioId): void
{
    $stmt = db()->prepare('DELETE FROM resenas WHERE id = :id AND usuario_id = :usuario_id');
    $stmt->execute(['id' => $resenaId, 'usuario_id' => $usuarioId]);
}
