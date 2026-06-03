<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/orders.php';

$user = require_auth();
$pedidos = get_pedidos_de_usuario((int) $user['id']);

render_header('Mis pedidos', 'cuenta');
?>
<section class="section">
    <div class="container">
        <p class="breadcrumb"><a href="dashboard.php">Mi cuenta</a> › Pedidos</p>
        <h1 class="page-title">📦 Mis pedidos</h1>

        <?php if ($pedidos === []): ?>
            <div class="card text-center mt-2">
                <p class="muted">No tienes pedidos todavía.</p>
                <a class="button button--primary mt-1" href="productos.php">Ir al catálogo</a>
            </div>
        <?php else: ?>
            <div class="card mt-2" style="padding:0;overflow:hidden;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th><th>Fecha</th><th class="num">Unidades</th>
                            <th>Estado</th><th class="num">Total</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $p): ?>
                            <tr>
                                <td><strong><?= e($p['codigo']) ?></strong></td>
                                <td><?= e(date('d/m/Y H:i', strtotime((string) $p['fecha_creacion']))) ?></td>
                                <td class="num"><?= (int) $p['unidades'] ?></td>
                                <td><span class="badge badge--estado-<?= e($p['estado']) ?>"><?= e(ucfirst($p['estado'])) ?></span></td>
                                <td class="num"><?= e(money($p['total'])) ?></td>
                                <td><a href="pedido.php?id=<?= (int) $p['id'] ?>">Ver</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
render_footer();
