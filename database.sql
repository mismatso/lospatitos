-- =========================================================
-- Base de datos demo para lospatitos.com
-- Tienda ficticia de patitos de hule "Los Patitos"
-- Requisitos: MySQL 8+ o MariaDB 10.5+
-- =========================================================

-- 0) Forzar utf8mb4 en la sesión de importación.
--    Necesario para que los emojis (caracteres de 4 bytes, p. ej. 🦆) se
--    interpreten correctamente tanto en los DEFAULT como en los INSERT.
--    Sin esto, MariaDB/MySQL puede lanzar "ERROR 1067 Invalid default value".
SET NAMES utf8mb4;

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

-- =========================================================
-- 4) Tabla principal de usuarios (clientes de la tienda).
-- =========================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    usuario VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(30) NULL,
    direccion VARCHAR(255) NULL,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_usuarios_correo (correo),
    UNIQUE KEY uk_usuarios_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Si la tabla usuarios ya existía de una versión anterior, añadir las
-- columnas de perfil nuevas (ignore los errores si ya existen).
-- ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(30) NULL AFTER password_hash;
-- ALTER TABLE usuarios ADD COLUMN direccion VARCHAR(255) NULL AFTER telefono;

-- Si la tabla productos ya existía sin la columna de imagen, añádala:
-- ALTER TABLE productos ADD COLUMN imagen VARCHAR(255) NULL AFTER emoji;

-- =========================================================
-- 5) Catálogo: categorías y productos.
-- =========================================================
CREATE TABLE IF NOT EXISTS categorias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    slug VARCHAR(80) NOT NULL,
    descripcion VARCHAR(255) NULL,
    UNIQUE KEY uk_categorias_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id BIGINT UNSIGNED NULL,
    nombre VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    descripcion TEXT NULL,
    precio DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    emoji VARCHAR(16) NOT NULL DEFAULT '🦆',
    imagen VARCHAR(255) NULL,
    color_hex VARCHAR(7) NOT NULL DEFAULT '#FFD23F',
    stock INT NOT NULL DEFAULT 0,
    destacado TINYINT(1) NOT NULL DEFAULT 0,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_productos_slug (slug),
    KEY idx_productos_categoria (categoria_id),
    CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id)
        REFERENCES categorias (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 6) Pedidos y sus líneas de detalle.
-- =========================================================
CREATE TABLE IF NOT EXISTS pedidos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    estado ENUM('pendiente', 'pagado', 'enviado', 'cancelado') NOT NULL DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_pedidos_codigo (codigo),
    KEY idx_pedidos_usuario (usuario_id),
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedido_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id BIGINT UNSIGNED NOT NULL,
    producto_id BIGINT UNSIGNED NULL,
    nombre_producto VARCHAR(120) NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    KEY idx_items_pedido (pedido_id),
    KEY idx_items_producto (producto_id),
    CONSTRAINT fk_items_pedido FOREIGN KEY (pedido_id)
        REFERENCES pedidos (id) ON DELETE CASCADE,
    CONSTRAINT fk_items_producto FOREIGN KEY (producto_id)
        REFERENCES productos (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 7) Reseñas de productos.
-- =========================================================
CREATE TABLE IF NOT EXISTS resenas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    producto_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    calificacion TINYINT UNSIGNED NOT NULL DEFAULT 5,
    comentario VARCHAR(500) NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_resenas_producto (producto_id),
    KEY idx_resenas_usuario (usuario_id),
    UNIQUE KEY uk_resena_usuario_producto (producto_id, usuario_id),
    CONSTRAINT fk_resenas_producto FOREIGN KEY (producto_id)
        REFERENCES productos (id) ON DELETE CASCADE,
    CONSTRAINT fk_resenas_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 8) Blog / noticias.
-- =========================================================
CREATE TABLE IF NOT EXISTS articulos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    resumen VARCHAR(300) NULL,
    contenido TEXT NULL,
    autor VARCHAR(120) NOT NULL DEFAULT 'Equipo Los Patitos',
    emoji VARCHAR(16) NOT NULL DEFAULT '📰',
    fecha_publicacion DATE NOT NULL,
    UNIQUE KEY uk_articulos_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 9) Mensajes del formulario de contacto.
-- =========================================================
CREATE TABLE IF NOT EXISTS mensajes_contacto (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    asunto VARCHAR(150) NOT NULL,
    mensaje VARCHAR(2000) NOT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 10) Datos semilla.
-- =========================================================

-- Usuarios demo.
-- Usuario 1: analopez / Patito123!
-- Usuario 2: cperez   / Demo1234!
INSERT INTO usuarios (nombre, correo, usuario, password_hash, telefono, direccion, estado)
VALUES
    (
        'Ana López',
        'ana@lospatitos.com',
        'analopez',
        '$2y$12$sgB26XB7./IOSK9MAQKBJ.OwMM8JCTNJMXOAIZB1bTauyQBHsmtBi',
        '+506 8888 1234',
        'Av. del Estanque 123, San José',
        'activo'
    ),
    (
        'Carlos Pérez',
        'carlos@lospatitos.com',
        'cperez',
        '$2y$12$LQGfWXcUY54iyD.efq6KyuARzBX9hWyeo6oPbdtm6ihbUT3tizfQ6',
        '+506 8777 5678',
        'Calle Charco 45, Heredia',
        'activo'
    )
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    password_hash = VALUES(password_hash);

-- Categorías.
INSERT INTO categorias (id, nombre, slug, descripcion)
VALUES
    (1, 'Clásicos', 'clasicos', 'Los patitos de toda la vida, amarillos y entrañables.'),
    (2, 'Edición Especial', 'edicion-especial', 'Patitos temáticos de colección, en tiradas limitadas.'),
    (3, 'Packs', 'packs', 'Familias y combos de patitos a precio especial.'),
    (4, 'Accesorios', 'accesorios', 'Todo lo que tu patito necesita para brillar.')
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion);

-- Productos.
INSERT INTO productos (categoria_id, nombre, slug, descripcion, precio, emoji, imagen, color_hex, stock, destacado)
VALUES
    (1, 'Patito Clásico Amarillo', 'patito-clasico-amarillo',
     'El original e inconfundible. Pico naranja, mirada tierna y un "cuac" que alegra cualquier bañera. Material flotante y libre de BPA.',
     4.99, '🦆', 'assets/img/productos/patito-clasico-amarillo.png', '#ffffff', 120, 1),
    (1, 'Patito Bebé Mini', 'patito-bebe-mini',
     'Versión pequeñita del clásico, ideal para los más peques de la familia. Se vende en trío.',
     3.50, '🐤', 'assets/img/productos/patito-bebe-mini.png', '#ffffff', 80, 0),
    (2, 'Patito Pirata', 'patito-pirata',
     'Con parche, sombrero y espíritu aventurero. Surca los mares de tu lavabo en busca del tesoro de jabón.',
     7.99, '🏴‍☠️', 'assets/img/productos/patito-pirata.png', '#ffffff', 45, 1),
    (2, 'Patito Astronauta', 'patito-astronauta',
     'Listo para el despegue. Casco transparente y traje plateado para explorar la galaxia de la espuma.',
     8.50, '🚀', 'assets/img/productos/patito-astronauta.png', '#ffffff', 30, 1),
    (2, 'Patito Unicornio', 'patito-unicornio',
     'Mágico, brillante y con cuerno arcoíris. El favorito indiscutible de quienes aman la fantasía.',
     8.99, '🦄', 'assets/img/productos/patito-unicornio.png', '#ffffff', 38, 1),
    (2, 'Patito Detective', 'patito-detective',
     'Lupa en ala y gabardina puesta: ningún misterio del baño se le resiste. Elemental, querido patito.',
     7.50, '🔍', 'assets/img/productos/patito-detective.png', '#ffffff', 25, 0),
    (2, 'Patito Ninja', 'patito-ninja',
     'Sigiloso y veloz. Aparece y desaparece entre burbujas sin hacer el menor "cuac".',
     7.99, '🥷', 'assets/img/productos/patito-ninja.png', '#ffffff', 28, 0),
    (2, 'Patito Princesa', 'patito-princesa',
     'Con corona dorada y vestido de gala. Reina absoluta del reino de la tina.',
     8.25, '👑', 'assets/img/productos/patito-princesa.png', '#ffffff', 33, 0),
    (2, 'Patito Superhéroe', 'patito-superheroe',
     'Capa al viento y antifaz puesto. Siempre listo para salvar el día… y la hora del baño.',
     8.75, '🦸', 'assets/img/productos/patito-superheroe.png', '#ffffff', 40, 1),
    (3, 'Pack Familia Patito', 'pack-familia-patito',
     'Mamá, papá y dos patitos bebé. La familia completa para que nadie nade en soledad. ¡Ahorra comprando el set!',
     16.99, '👨‍👩‍👧‍👦', 'assets/img/productos/pack-familia-patito.png', '#ffffff', 22, 1),
    (1, 'Patito Gigante XL', 'patito-gigante-xl',
     'Tamaño descomunal para piscinas y decoración. Imposible que pase desapercibido.',
     24.99, '🦆', 'assets/img/productos/patito-gigante-xl.png', '#ffffff', 12, 0),
    (4, 'Patito LED Brillante', 'patito-led-brillante',
     'Cambia de color al contacto con el agua y crea un espectáculo de luces en tu baño nocturno.',
     11.99, '💡', 'assets/img/productos/patito-led-brillante.png', '#ffffff', 18, 1)
ON DUPLICATE KEY UPDATE
    descripcion = VALUES(descripcion),
    precio = VALUES(precio),
    emoji = VALUES(emoji),
    imagen = VALUES(imagen),
    stock = VALUES(stock),
    destacado = VALUES(destacado);

-- Reseñas semilla (usuarios 1 y 2 sobre algunos productos).
INSERT INTO resenas (producto_id, usuario_id, calificacion, comentario)
VALUES
    (1, 1, 5, 'Un clásico que nunca falla. Mi bañera no es la misma sin él. 🦆'),
    (1, 2, 4, 'Muy buen patito, flota perfecto. El "cuac" podría ser más fuerte.'),
    (5, 1, 5, '¡El unicornio es precioso! Brilla muchísimo y a mi hija le encanta.'),
    (3, 2, 5, 'El pirata es todo un personaje. Excelente acabado del parche.')
ON DUPLICATE KEY UPDATE
    calificacion = VALUES(calificacion),
    comentario = VALUES(comentario);

-- Artículos del blog.
INSERT INTO articulos (titulo, slug, resumen, contenido, autor, emoji, fecha_publicacion)
VALUES
    ('5 razones para tener un patito de hule en casa',
     '5-razones-patito-de-hule',
     'Más que un juguete: descubre por qué un patito de hule alegra el día de grandes y chicos.',
     'El patito de hule es mucho más que un accesorio de baño. Primero, es un compañero de juego que estimula la imaginación de los más pequeños. Segundo, su flotabilidad lo convierte en una herramienta perfecta para enseñar a perder el miedo al agua. Tercero, ¡decora! Un patito sobre el lavabo aporta un toque de color y simpatía. Cuarto, es un excelente regalo: económico, universal y siempre bien recibido. Y quinto, coleccionarlos engancha: con tantas ediciones especiales, nunca tendrás suficientes. En Los Patitos creemos que la felicidad cabe en un pequeño pato amarillo. 🦆',
     'Ana López', '🛁', '2026-01-15'),
    ('La curiosa historia del patito de goma',
     'historia-del-patito-de-goma',
     'Del caucho duro del siglo XIX al ícono pop que conocemos hoy. Un viaje lleno de "cuacs".',
     'El patito de goma nació a finales del siglo XIX, cuando los primeros fabricantes de caucho empezaron a moldear figuras de animales. Curiosamente, los primeros patitos no flotaban: eran de caucho macizo y servían como mordedores. Fue en la década de 1940 cuando se popularizó el patito hueco y flotante que conocemos. Su salto a la fama mundial llegó en 1970, cuando un personaje de un famoso programa infantil le cantó una canción que lo convirtió en ícono. Desde entonces, el patito de hule es sinónimo de infancia, ternura y diversión. En Los Patitos honramos esa historia con cada modelo que fabricamos.',
     'Equipo Los Patitos', '📜', '2026-02-10'),
    ('Cómo cuidar tu colección de patitos',
     'como-cuidar-tu-coleccion-de-patitos',
     'Consejos sencillos para que tus patitos luzcan impecables y duren muchos años.',
     'Mantener tu colección de patitos en buen estado es muy fácil. Lo más importante es evitar el moho: vacía el agua del interior después de cada uso apretando suavemente el patito y déjalo secar boca abajo. Una vez al mes, dale un baño con agua tibia y un poco de vinagre blanco para eliminar bacterias. Guarda los patitos de edición especial lejos de la luz solar directa para que sus colores no se desvanezcan. Y si coleccionas los modelos LED, retira el agua del compartimento para proteger el circuito. Con estos cuidados, tus patitos te acompañarán felices durante años.',
     'Carlos Pérez', '🧼', '2026-03-05'),
    ('¡Novedad! Llega la edición Astronauta',
     'novedad-edicion-astronauta',
     'Despega con nuestro nuevo patito espacial: casco transparente, traje plateado y mucha aventura.',
     'Estamos emocionados de presentar la última incorporación a nuestra familia: el Patito Astronauta. Diseñado para los soñadores que miran a las estrellas, este modelo cuenta con un casco transparente desmontable, un traje plateado con detalles reflectantes y una base con forma de cohete para exhibirlo. Es parte de nuestra línea de Edición Especial y, como todas, se fabrica en tiradas limitadas. Ya está disponible en nuestro catálogo. ¿Listo para el despegue? 3… 2… 1… ¡cuac!',
     'Equipo Los Patitos', '🚀', '2026-05-20')
ON DUPLICATE KEY UPDATE
    resumen = VALUES(resumen),
    contenido = VALUES(contenido);
