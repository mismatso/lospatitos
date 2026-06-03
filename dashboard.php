<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

$user = require_auth();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | lospatitos.com</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <main class="dashboard-layout">
        <section class="card dashboard-card">
            <div class="dashboard-header">
                <div>
                    <span class="brand__badge">Área protegida</span>
                    <h1>Hola, <?= e($user['nombre']) ?></h1>
                    <p>Esta pantalla solo es accesible con una sesión válida.</p>
                </div>

                <form method="post" action="logout.php">
                    <?= csrf_input() ?>
                    <button type="submit" class="button button--ghost">Cerrar sesión</button>
                </form>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert--<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-grid">
                <article class="panel">
                    <h2>Resumen de la sesión</h2>
                    <ul class="list">
                        <li><strong>Nombre:</strong> <?= e($user['nombre']) ?></li>
                        <li><strong>Usuario:</strong> <?= e($user['usuario']) ?></li>
                        <li><strong>Correo:</strong> <?= e($user['correo']) ?></li>
                        <li><strong>Estado:</strong> <?= e($user['estado']) ?></li>
                        <li><strong>Alta:</strong> <?= e($user['fecha_creacion']) ?></li>
                    </ul>
                </article>

                <article class="panel">
                    <h2>Notas de seguridad</h2>
                    <ul class="list">
                        <li>Las contraseñas se validan con <code>password_verify()</code>.</li>
                        <li>Los formularios incluyen token CSRF de sesión.</li>
                        <li>Las consultas usan PDO con sentencias preparadas.</li>
                        <li>El acceso directo sin sesión redirige al login.</li>
                    </ul>
                </article>
            </div>
        </section>
    </main>
</body>
</html>
