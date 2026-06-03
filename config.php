<?php
declare(strict_types=1);

/**
 * Configuración principal de la aplicación.
 *
 * Aquí se centraliza la conexión a MySQL y el arranque de sesión.
 * Mantener este archivo separado facilita cambiar credenciales sin tocar
 * la lógica del resto de pantallas.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'lospatitos';
const DB_USER = 'lospatitos_app';
const DB_PASS = 'L0sPat1t0sApp!';

/**
 * Devuelve una instancia PDO reutilizable.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    return $pdo;
}
