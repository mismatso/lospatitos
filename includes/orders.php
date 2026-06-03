<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/cart.php';

/**
 * Acceso a datos y operaciones de pedidos.
 */

/**
 * Genera un código de pedido legible y único-ish (validado por UNIQUE en BD).
 */
function generar_codigo_pedido(): string
{
    return 'LP-' . strtoupper(bin2hex(random_bytes(3)));
}

/**
 * Crea un pedido a partir del carrito en sesión dentro de una transacción.
 * Descuenta stock y vacía el carrito. Devuelve el id del pedido creado.
 *
 * @throws RuntimeException si el carrito está vacío.
 */
function crear_pedido_desde_carrito(int $usuarioId): int
{
    $items = cart_items();
    if ($items === []) {
        throw new RuntimeException('El carrito está vacío.');
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $total = 0.0;
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }

        $stmt = $pdo->prepare(
            'INSERT INTO pedidos (usuario_id, codigo, total, estado)
             VALUES (:usuario_id, :codigo, :total, :estado)'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'codigo' => generar_codigo_pedido(),
            'total' => $total,
            'estado' => 'pagado',
        ]);
        $pedidoId = (int) $pdo->lastInsertId();

        $itemStmt = $pdo->prepare(
            'INSERT INTO pedido_items (pedido_id, producto_id, nombre_producto, cantidad, precio_unitario)
             VALUES (:pedido_id, :producto_id, :nombre, :cantidad, :precio)'
        );
        $stockStmt = $pdo->prepare(
            'UPDATE productos SET stock = GREATEST(stock - :cantidad, 0) WHERE id = :id'
        );

        foreach ($items as $item) {
            $producto = $item['producto'];
            $itemStmt->execute([
                'pedido_id' => $pedidoId,
                'producto_id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'cantidad' => $item['cantidad'],
                'precio' => $producto['precio'],
            ]);
            $stockStmt->execute([
                'cantidad' => $item['cantidad'],
                'id' => $producto['id'],
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    cart_clear();
    return $pedidoId;
}

/**
 * Lista los pedidos de un usuario con un resumen de número de artículos.
 */
function get_pedidos_de_usuario(int $usuarioId): array
{
    $stmt = db()->prepare(
        'SELECT p.id, p.codigo, p.total, p.estado, p.fecha_creacion,
                COALESCE(SUM(i.cantidad), 0) AS unidades
         FROM pedidos p
         LEFT JOIN pedido_items i ON i.pedido_id = p.id
         WHERE p.usuario_id = :usuario_id
         GROUP BY p.id
         ORDER BY p.fecha_creacion DESC'
    );
    $stmt->execute(['usuario_id' => $usuarioId]);
    return $stmt->fetchAll();
}

/**
 * Devuelve un pedido del usuario (o null si no existe / no le pertenece).
 */
function get_pedido(int $pedidoId, int $usuarioId): ?array
{
    $stmt = db()->prepare(
        'SELECT id, codigo, total, estado, fecha_creacion
         FROM pedidos
         WHERE id = :id AND usuario_id = :usuario_id
         LIMIT 1'
    );
    $stmt->execute(['id' => $pedidoId, 'usuario_id' => $usuarioId]);
    return $stmt->fetch() ?: null;
}

/**
 * Líneas de detalle de un pedido.
 */
function get_items_de_pedido(int $pedidoId): array
{
    $stmt = db()->prepare(
        'SELECT i.nombre_producto, i.cantidad, i.precio_unitario,
                (i.cantidad * i.precio_unitario) AS subtotal,
                p.slug, p.emoji
         FROM pedido_items i
         LEFT JOIN productos p ON p.id = i.producto_id
         WHERE i.pedido_id = :pedido_id'
    );
    $stmt->execute(['pedido_id' => $pedidoId]);
    return $stmt->fetchAll();
}

/**
 * Marca un pedido como cancelado (validando pertenencia).
 */
function cancelar_pedido(int $pedidoId, int $usuarioId): void
{
    $stmt = db()->prepare(
        "UPDATE pedidos SET estado = 'cancelado'
         WHERE id = :id AND usuario_id = :usuario_id AND estado <> 'cancelado'"
    );
    $stmt->execute(['id' => $pedidoId, 'usuario_id' => $usuarioId]);
}

/**
 * Elimina un pedido del historial (validando pertenencia).
 * Las líneas se borran en cascada por la FK.
 */
function eliminar_pedido(int $pedidoId, int $usuarioId): void
{
    $stmt = db()->prepare('DELETE FROM pedidos WHERE id = :id AND usuario_id = :usuario_id');
    $stmt->execute(['id' => $pedidoId, 'usuario_id' => $usuarioId]);
}
