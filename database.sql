-- =========================================================
-- Base de datos demo para lospatitos.com
-- Requisitos: MySQL 8+
-- =========================================================

-- 1) Crear la base de datos si no existe.
CREATE DATABASE IF NOT EXISTS lospatitos
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- 2) Crear un usuario dedicado para la aplicación.
--    IMPORTANTE: cambie esta contraseña en entornos reales.
CREATE USER IF NOT EXISTS 'lospatitos_app'@'localhost'
IDENTIFIED BY 'L0sPat1t0sApp!';

-- 3) Asignar privilegios mínimos necesarios sobre la base de datos.
GRANT SELECT, INSERT, UPDATE, DELETE
ON lospatitos.*
TO 'lospatitos_app'@'localhost';

FLUSH PRIVILEGES;

USE lospatitos;

-- 4) Crear tabla principal de usuarios.
CREATE TABLE IF NOT EXISTS usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    usuario VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_usuarios_correo (correo),
    UNIQUE KEY uk_usuarios_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Datos semilla.
-- Usuario semilla 1:
--   nombre: Ana López
--   correo: ana@lospatitos.com
--   usuario: analopez
--   contraseña en claro: Patito123!
--   hash BCRYPT: $2y$12$sgB26XB7./IOSK9MAQKBJ.OwMM8JCTNJMXOAIZB1bTauyQBHsmtBi
--
-- Usuario semilla 2:
--   nombre: Carlos Pérez
--   correo: carlos@lospatitos.com
--   usuario: cperez
--   contraseña en claro: Demo1234!
--   hash BCRYPT: $2y$12$LQGfWXcUY54iyD.efq6KyuARzBX9hWyeo6oPbdtm6ihbUT3tizfQ6
INSERT INTO usuarios (nombre, correo, usuario, password_hash, estado)
VALUES
    (
        'Ana López',
        'ana@lospatitos.com',
        'analopez',
        '$2y$12$sgB26XB7./IOSK9MAQKBJ.OwMM8JCTNJMXOAIZB1bTauyQBHsmtBi',
        'activo'
    ),
    (
        'Carlos Pérez',
        'carlos@lospatitos.com',
        'cperez',
        '$2y$12$LQGfWXcUY54iyD.efq6KyuARzBX9hWyeo6oPbdtm6ihbUT3tizfQ6',
        'activo'
    )
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    correo = VALUES(correo),
    usuario = VALUES(usuario),
    password_hash = VALUES(password_hash),
    estado = VALUES(estado);
