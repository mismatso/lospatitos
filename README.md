# lospatitos.com

Aplicación demo sencilla en PHP 8 + MySQL 8 + HTML/CSS/JavaScript vanilla.

## Requisitos
- PHP 8 o superior con extensión `pdo_mysql`
- MySQL 8 o superior
- Navegador web moderno

## Estructura
- `config.php`: configuración central y conexión PDO reutilizable
- `database.sql`: script completo de creación, usuario SQL, tablas y datos semilla
- `index.php`: login
- `registro.php`: registro de usuarios
- `dashboard.php`: página protegida
- `logout.php`: cierre de sesión
- `includes/`: utilidades de autenticación, helpers y CSRF
- `assets/`: estilos y validación cliente

## Importar la base de datos
1. Acceda a MySQL con un usuario administrador.
2. Importe el archivo `database.sql`.
3. El script crea:
   - La base de datos `lospatitos`
   - El usuario `lospatitos_app`
   - La tabla `usuarios`
   - Dos usuarios de prueba

Ejemplo:

```bash
mysql -u root -p < database.sql
```

## Configurar `config.php`
Revise estas constantes y ajústelas si su entorno usa otros valores:
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

Por defecto coinciden con lo definido en `database.sql`.

## Levantar la aplicación
Desde la carpeta del proyecto ejecute:

```bash
php -S localhost:8000
```

Luego abra:
- `http://localhost:8000/index.php`

## Usuarios semilla
- Usuario/correo: `analopez` o `ana@lospatitos.com`
  - Contraseña: `Patito123!`
- Usuario/correo: `cperez` o `carlos@lospatitos.com`
  - Contraseña: `Demo1234!`

## Comportamiento esperado
- Si inicia sesión con un usuario válido, accederá al dashboard.
- Si intenta entrar a `dashboard.php` sin sesión, será redirigido a `index.php`.
- Las contraseñas se almacenan con hash BCRYPT, nunca en texto plano.
