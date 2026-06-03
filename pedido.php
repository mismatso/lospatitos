<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/orders.php';

$user = require_auth();

// Acciones sobre el pedido (cancelar / eliminar).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
        set_flash('error', 'La solicitud no es válida. Intente de nuevo.');
        redirect('pedidos.php');
    }

    $accion = (string) ($_POST['action'] ?? '');
    $pedidoId = (int) ($_POST['pedido_id'] ?? 0);

    if ($accion === 'cancelar') {
        cancelar_pedido($pedidoId, (int) $user['id']);
        set_flash('success', 'El pedido se canceló correctamente.');
        redirect('pedido.php?id=' . $pedidoId);
    }

    if ($accion === 'eliminar') {
        eliminar_pedido($pedidoId, (int) $user['id']);
        set_flash('success', 'El pedido se eliminó de tu historial.');
        redirect('pedidos.php');
    }

    redirect('pedidos.php');
}

$pedidoId = (int) ($_GET['id'] ?? 0);
$pedido = get_pedido($pedidoId, (int) $user['id']);

if ($pedido === null) {
    http_response_code(404);
    render_header('Pedido no encontrado', 'cuenta');
    echo '<section class="section"><div class="container text-center">';
    echo '<h2>Pedido no encontrado 📦</h2><p class="muted">No existe o no pertenece a tu cuenta.</p>';
    echo '<a class="button button--primary mt-1" href="pedidos.php">Volver a mis pedidos</a>';
    echo '</div></section>';
    render_footer();
    exit;
}

$items = get_items_de_pedido($pedidoId);

render_header('Pedido ' . $pedido['codigo'], 'cuenta');
?>
<section class="section">
    <div class="container">
        <p class="breadcrumb"><a href="dashboard.php">Mi cuenta</a> › <a href="pedidos.php">Pedidos</a> › <?= e($pedido['codigo']) ?></p>

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
            <div>
                <h1 class="page-title">Pedido <?= e($pedido['codigo']) ?></h1>
                <p class="muted">
                    <?= e(date('d/m/Y H:i', strtotime((string) $pedido['fecha_creacion']))) ?> ·
                    <span class="badge badge--estado-<?= e($pedido['estado']) ?>"><?= e(ucfirst($pedido['estado'])) ?></span>
                </p>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <?php if ($pedido['estado'] !== 'cancelado'): ?>
                    <form method="post" data-confirm="¿Cancelar este pedido?" style="margin:0;">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action" value="cancelar">
                        <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                        <button type="submit" class="button button--ghost button--sm">Cancelar pedido</button>
                    </form>
                <?php endif; ?>
                <form method="post" data-confirm="¿Eliminar este pedido de tu historial? Esta acción no se puede deshacer." style="margin:0;">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="eliminar">
                    <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                    <button type="submit" class="button button--danger button--sm">Eliminar</button>
                </form>
            </div>
        </div>

        <div class="card mt-2" style="padding:0;overflow:hidden;">
            <table class="table">
                <thead>
                    <tr><th>Producto</th><th class="num">Precio</th><th class="num">Cantidad</th><th class="num">Subtotal</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?= producto_img($it, 'thumb-img') ?>
                                <?php if (!empty($it['slug'])): ?>
                                    <a href="producto.php?slug=<?= e($it['slug']) ?>"><?= e($it['nombre_producto']) ?></a>
                                <?php else: ?>
                                    <?= e($it['nombre_producto']) ?>
                                <?php endif; ?>
                            </td>
                            <td class="num"><?= e(money($it['precio_unitario'])) ?></td>
                            <td class="num"><?= (int) $it['cantidad'] ?></td>
                            <td class="num"><?= e(money($it['subtotal'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="num">Total</th>
                        <th class="num"><?= e(money($pedido['total'])) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</section>
<?php
render_footer();
