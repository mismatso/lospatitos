<?php
declare(strict_types=1);

require_once __DIR__ . '/catalog.php';

/**
 * Carrito de compras guardado en sesión.
 * Estructura: $_SESSION['cart'] = [ producto_id => cantidad ].
 */

/**
 * Devuelve el carrito crudo (id => cantidad).
 */
function cart_raw(): array
{
    return $_SESSION['cart'] ?? [];
}

/**
 * Agrega una cantidad de un producto al carrito (acumulando).
 */
function cart_add(int $productoId, int $cantidad = 1): void
{
    $cantidad = max(1, $cantidad);
    $cart = cart_raw();
    $cart[$productoId] = ($cart[$productoId] ?? 0) + $cantidad;
    $_SESSION['cart'] = $cart;
}

/**
 * Fija la cantidad exacta de un producto. Cantidad 0 lo elimina.
 */
function cart_update(int $productoId, int $cantidad): void
{
    $cart = cart_raw();
    if ($cantidad <= 0) {
        unset($cart[$productoId]);
    } else {
        $cart[$productoId] = $cantidad;
    }
    $_SESSION['cart'] = $cart;
}

/**
 * Quita un producto del carrito.
 */
function cart_remove(int $productoId): void
{
    $cart = cart_raw();
    unset($cart[$productoId]);
    $_SESSION['cart'] = $cart;
}

/**
 * Vacía el carrito.
 */
function cart_clear(): void
{
    $_SESSION['cart'] = [];
}

/**
 * Número total de unidades en el carrito.
 */
function cart_count(): int
{
    return array_sum(cart_raw());
}

/**
 * Devuelve las líneas del carrito enriquecidas con datos del producto.
 * Ignora silenciosamente productos que ya no existen.
 */
function cart_items(): array
{
    $items = [];
    foreach (cart_raw() as $id => $cantidad) {
        $producto = get_producto_por_id((int) $id);
        if ($producto === null) {
            continue;
        }
        $cantidad = max(1, (int) $cantidad);
        $items[] = [
            'producto' => $producto,
            'cantidad' => $cantidad,
            'subtotal' => (float) $producto['precio'] * $cantidad,
        ];
    }
    return $items;
}

/**
 * Total monetario del carrito.
 */
function cart_total(): float
{
    $total = 0.0;
    foreach (cart_items() as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}
