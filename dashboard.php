<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/orders.php';
require_once __DIR__ . '/includes/catalog.php';

$user = require_auth();

$pedidos = get_pedidos_de_usuario((int) $user['id']);
$resenas = get_resenas_de_usuario((int) $user['id']);

$totalGastado = 0.0;
foreach ($pedidos as $p) {
    if ($p['estado'] !== 'cancelado') {
        $totalGastado += (float) $p['total'];
    }
}

render_header('Mi cuenta', 'cuenta');
?>
<section class="section">
    <div class="container">
        <span class="eyebrow">Área de cliente</span>
        <h1 class="page-title">Hola, <?= e($user['nombre']) ?> 🦆</h1>
        <p class="muted">Este es tu panel personal. Solo tú puedes verlo.</p>

        <div class="account-grid mt-2">
            <div class="stat"><div class="stat__value"><?= count($pedidos) ?></div><div class="stat__label">Pedidos</div></div>
            <div class="stat"><div class="stat__value"><?= e(money($totalGastado)) ?></div><div class="stat__label">Total comprado</div></div>
            <div class="stat"><div class="stat__value"><?= count($resenas) ?></div><div class="stat__label">Reseñas escritas</div></div>
            <div class="stat"><div class="stat__value">🛒 <?= (int) cart_count() ?></div><div class="stat__label">En el carrito</div></div>
        </div>

        <div class="layout-2col mt-2">
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <h2 style="margin:0;">Pedidos recientes</h2>
                    <a href="pedidos.php">Ver todos →</a>
                </div>

                <?php if ($pedidos === []): ?>
                    <p class="muted mt-1">Todavía no has hecho ningún pedido. <a href="productos.php">¡Empieza a comprar!</a></p>
                <?php else: ?>
                    <table class="table mt-1">
                        <thead>
                            <tr><th>Código</th><th>Fecha</th><th>Estado</th><th class="num">Total</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($pedidos, 0, 5) as $p): ?>
                                <tr>
                                    <td><a href="pedido.php?id=<?= (int) $p['id'] ?>"><?= e($p['codigo']) ?></a></td>
                                    <td><?= e(date('d/m/Y', strtotime((string) $p['fecha_creacion']))) ?></td>
                                    <td><span class="badge badge--estado-<?= e($p['estado']) ?>"><?= e(ucfirst($p['estado'])) ?></span></td>
                                    <td class="num"><?= e(money($p['total'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <aside>
                <div class="card mb-2">
                    <h3>Tus datos</h3>
                    <ul class="list">
                        <li><strong>Usuario:</strong> <?= e($user['usuario']) ?></li>
                        <li><strong>Correo:</strong> <?= e($user['correo']) ?></li>
                        <li><strong>Teléfono:</strong> <?= e($user['telefono'] ?? '—') ?></li>
                        <li><strong>Dirección:</strong> <?= e($user['direccion'] ?? '—') ?></li>
                    </ul>
                    <a class="button button--ghost button--sm mt-1" href="perfil.php">Editar perfil</a>
                </div>

                <div class="card">
                    <h3>Accesos rápidos</h3>
                    <p><a href="productos.php">🦆 Seguir comprando</a></p>
                    <p><a href="pedidos.php">📦 Mis pedidos</a></p>
                    <p><a href="mis-resenas.php">⭐ Mis reseñas</a></p>
                    <p><a href="carrito.php">🛒 Ver carrito</a></p>
                </div>
            </aside>
        </div>
    </div>
</section>
<?php
render_footer();
