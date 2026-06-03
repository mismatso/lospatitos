<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/catalog.php';
require_once __DIR__ . '/includes/orders.php';

// Acciones del carrito (POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if (!csrf_validate(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
        set_flash('error', 'La solicitud no es válida. Intente de nuevo.');
        redirect('carrito.php');
    }

    if ($action === 'add') {
        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1));
        $producto = get_producto_por_id($productoId);

        if ($producto === null) {
            set_flash('error', 'El producto seleccionado no está disponible.');
        } else {
            cart_add($productoId, $cantidad);
            set_flash('success', $producto['emoji'] . ' "' . $producto['nombre'] . '" se agregó al carrito.');
        }

        $redirect = (string) ($_POST['redirect'] ?? 'carrito.php');
        // Solo permitir redirecciones internas relativas.
        if (preg_match('#^[a-z0-9_\-./?=&%]+$#i', $redirect) !== 1) {
            $redirect = 'carrito.php';
        }
        redirect($redirect);
    }

    if ($action === 'update') {
        cart_update((int) ($_POST['producto_id'] ?? 0), (int) ($_POST['cantidad'] ?? 0));
        redirect('carrito.php');
    }

    if ($action === 'remove') {
        cart_remove((int) ($_POST['producto_id'] ?? 0));
        set_flash('success', 'Producto eliminado del carrito.');
        redirect('carrito.php');
    }

    if ($action === 'checkout') {
        $user = current_user();
        if ($user === null) {
            set_flash('error', 'Inicie sesión para finalizar su compra.');
            redirect('login.php');
        }

        try {
            $pedidoId = crear_pedido_desde_carrito((int) $user['id']);
            set_flash('success', '¡Pedido confirmado! 🦆 Gracias por tu compra.');
            redirect('pedido.php?id=' . $pedidoId);
        } catch (RuntimeException $e) {
            set_flash('error', 'Tu carrito está vacío.');
            redirect('carrito.php');
        }
    }

    redirect('carrito.php');
}

$items = cart_items();
$total = cart_total();
$user = current_user();

render_header('Carrito', 'carrito');
?>
<section class="section">
    <div class="container">
        <div class="section__head">
            <span class="eyebrow">Tu carrito</span>
            <h2>🛒 Carrito de compras</h2>
        </div>

        <?php if ($items === []): ?>
            <div class="card text-center">
                <p class="muted">Tu carrito está vacío. ¡Vamos a llenarlo de patitos! 🦆</p>
                <a class="button button--primary mt-1" href="productos.php">Ir al catálogo</a>
            </div>
        <?php else: ?>
            <div class="layout-2col">
                <div class="card" style="padding:0;overflow:hidden;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="num">Precio</th>
                                <th>Cantidad</th>
                                <th class="num">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): $p = $item['producto']; ?>
                                <tr>
                                    <td>
                                        <?= producto_img($p, 'thumb-img') ?>
                                        <a href="producto.php?slug=<?= e($p['slug']) ?>"><?= e($p['nombre']) ?></a>
                                    </td>
                                    <td class="num"><?= e(money($p['precio'])) ?></td>
                                    <td>
                                        <form method="post" style="margin:0;">
                                            <?= csrf_input() ?>
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="producto_id" value="<?= (int) $p['id'] ?>">
                                            <input class="qty" type="number" name="cantidad" value="<?= (int) $item['cantidad'] ?>" min="0" data-cart-qty>
                                        </form>
                                    </td>
                                    <td class="num"><?= e(money($item['subtotal'])) ?></td>
                                    <td>
                                        <form method="post" style="margin:0;">
                                            <?= csrf_input() ?>
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="producto_id" value="<?= (int) $p['id'] ?>">
                                            <button type="submit" class="button button--danger button--sm">✕</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <aside class="card">
                    <h3>Resumen</h3>
                    <p class="muted">Unidades: <strong><?= (int) cart_count() ?></strong></p>
                    <p style="font-size:1.5rem;font-weight:800;color:var(--beak-dark);">Total: <?= e(money($total)) ?></p>

                    <form method="post" style="margin:0;">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action" value="checkout">
                        <button type="submit" class="button button--primary button--block">
                            <?= $user ? 'Finalizar compra' : 'Inicia sesión para comprar' ?>
                        </button>
                    </form>
                    <a class="button button--ghost button--block mt-1" href="productos.php">Seguir comprando</a>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
render_footer();
