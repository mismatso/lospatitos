<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('error', 'La solicitud de cierre de sesión no es válida.');
    redirect('dashboard.php');
}

$csrfToken = $_POST['csrf_token'] ?? null;

if (!csrf_validate(is_string($csrfToken) ? $csrfToken : null)) {
    set_flash('error', 'No se pudo cerrar la sesión porque el token CSRF no es válido.');
    redirect('dashboard.php');
}

logout_user();

session_start();
set_flash('success', 'La sesión se cerró correctamente. ¡Vuelve pronto! 🦆');
redirect('login.php');
