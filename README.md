# Los Patitos 🦆

Tienda demo de patitos de hule **«Los Patitos»** en PHP 8 + MySQL 8 + HTML/CSS/JavaScript vanilla.

Aplicación de ejemplo para un curso de programación. Incluye secciones públicas
(navegables como invitado) y un área de cliente protegida con funcionalidad CRUD
real: carrito de compras, pedidos, reseñas y edición de perfil.

## Requisitos
- PHP 8 o superior con extensión `pdo_mysql`
- MySQL 8 o superior
- Navegador web moderno

## Estructura
- `config.php`: configuración central y conexión PDO reutilizable
- `database.sql`: script completo de base de datos, usuario SQL, tablas y datos semilla
- **Páginas públicas (invitado):**
  - `index.php`: home / landing con productos destacados
  - `productos.php`: catálogo con filtro por categoría y búsqueda
  - `producto.php`: detalle de producto y reseñas
  - `nosotros.php`: historia, valores y equipo de la empresa
  - `blog.php` / `articulo.php`: blog de noticias
  - `contacto.php`: formulario de contacto (guarda en BD)
  - `login.php` / `registro.php`: autenticación
- **Páginas privadas (autenticado):**
  - `dashboard.php`: panel del cliente
  - `perfil.php`: editar datos y cambiar contraseña
  - `carrito.php`: carrito y checkout
  - `pedidos.php` / `pedido.php`: historial y detalle de pedidos
  - `mis-resenas.php`: gestión de reseñas propias
  - `logout.php`: cierre de sesión
- `includes/`: utilidades reutilizables
  - `auth.php`, `helpers.php`, `csrf.php`: autenticación, helpers y CSRF
  - `layout.php`: header/navbar/footer compartidos (`render_header`, `render_footer`)
  - `catalog.php`: consultas de productos, categorías y reseñas
  - `cart.php`: carrito en sesión
  - `orders.php`: creación y gestión de pedidos
  - `blog.php`: consultas del blog
- `assets/`: estilos (`css/styles.css`) y JavaScript (`js/app.js`)

## Importar la base de datos
1. Acceda a MySQL con un usuario administrador.
2. Importe el archivo `database.sql`.
3. El script crea la base `lospatitos`, el usuario `lospatitos_app`, todas las
   tablas y datos semilla (categorías, productos, artículos y reseñas de ejemplo).

En Debian/Ubuntu (root por socket):

```bash
sudo mysql < database.sql
```

O con un usuario administrador con contraseña:

```bash
mysql -u root -p < database.sql
```

> **Nota:** si ya tenía una versión anterior con la tabla `usuarios`, descomente
> los `ALTER TABLE` del script para añadir las columnas `telefono` y `direccion`,
> o vuelva a importar desde cero (`DROP DATABASE lospatitos;` primero).

### Tablas creadas
`usuarios`, `categorias`, `productos`, `pedidos`, `pedido_items`, `resenas`,
`articulos`, `mensajes_contacto`.

## Configurar `config.php`
Revise estas constantes y ajústelas si su entorno usa otros valores:
`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
Por defecto coinciden con lo definido en `database.sql`.

## Levantar la aplicación
Desde la carpeta del proyecto:

```bash
php -S localhost:8000
```

Luego abra `http://localhost:8000/index.php`.

## Usuarios semilla
- `analopez` / `ana@lospatitos.com` — contraseña: `Patito123!`
- `cperez` / `carlos@lospatitos.com` — contraseña: `Demo1234!`

## Comportamiento esperado
- Como **invitado** puede navegar inicio, catálogo, producto, nosotros, blog y
  contacto, y agregar productos al carrito.
- Para **finalizar la compra**, dejar reseñas o ver el área de cliente debe
  iniciar sesión.
- El acceso directo a páginas protegidas (`dashboard.php`, `perfil.php`, etc.)
  sin sesión redirige a `login.php`.
- Las contraseñas se almacenan con hash BCRYPT; las consultas usan PDO con
  sentencias preparadas y los formularios incluyen token CSRF.

## 📄 Licencia

[LosPatitos](https://github.com/mismatso/lospatitos) © 2026 by [Misael Matamoros](https://t.me/mismatso) está licenciado bajo la **GNU General Public License, version 3 (GPLv3)**. Para más detalles, consulta el archivo [LICENSE](/LICENSE).

!["GPLv3"](https://www.gnu.org/graphics/gplv3-with-text-136x68.png)