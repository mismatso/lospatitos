<?php
declare(strict_types=1);

/**
 * Escapa texto para imprimirlo de forma segura en HTML.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Guarda un mensaje flash en sesión para mostrarlo una sola vez.
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

/**
 * Recupera y elimina el mensaje flash actual.
 */
function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

/**
 * Redirección simple para centralizar cambios futuros.
 */
function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}
