<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';

$equipo = [
    ['Ana López', 'Fundadora & CEO', '👩‍💼', 'Coleccionista número uno. Empezó vendiendo patitos en una feria y nunca paró.'],
    ['Carlos Pérez', 'Director de Diseño', '🎨', 'El cerebro creativo detrás de cada edición especial.'],
    ['Marta Ríos', 'Jefa de Felicidad', '😄', 'Se asegura de que cada cliente termine con una sonrisa.'],
    ['Diego Soto', 'Logística', '🚚', 'Hace que cada patito llegue a tiempo y a salvo.'],
];

$valores = [
    ['💛', 'Alegría', 'Creemos que las cosas pequeñas hacen grandes sonrisas.'],
    ['♻️', 'Responsabilidad', 'Materiales seguros y procesos cuidadosos con el ambiente.'],
    ['✨', 'Calidad', 'Cada patito pasa por un control de "ternura" antes de salir.'],
    ['🤝', 'Cercanía', 'Tratamos a cada cliente como parte de la bandada.'],
];

render_header('Nosotros', 'nosotros');
?>
<section class="hero">
    <div class="container hero__inner">
        <div>
            <span class="eyebrow">Nuestra historia</span>
            <h1>Hacemos flotar la felicidad</h1>
            <p>Los Patitos nació en 2018 en una pequeña feria de artesanías. Hoy somos la tienda de patitos de hule favorita de miles de hogares, pero seguimos con la misma misión: alegrar el día, un "cuac" a la vez.</p>
        </div>
        <div class="hero__art" aria-hidden="true">🛁</div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid grid--2">
            <div class="card">
                <h2>Nuestra misión</h2>
                <p>Llevar momentos de alegría sencilla a cada hogar a través de productos entrañables, seguros y llenos de personalidad. Queremos que abrir una caja de Los Patitos se sienta como recibir un abrazo.</p>
            </div>
            <div class="card">
                <h2>Nuestra visión</h2>
                <p>Ser la comunidad de coleccionistas de patitos más grande y feliz del mundo, reconocida por su creatividad, su calidad y su compromiso con un planeta más limpio.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background:#fff;">
    <div class="container">
        <div class="section__head">
            <span class="eyebrow">Lo que nos mueve</span>
            <h2>Nuestros valores</h2>
        </div>
        <div class="grid grid--3">
            <?php foreach ($valores as [$icono, $titulo, $texto]): ?>
                <div class="card feature">
                    <span class="feature__icon"><?= e($icono) ?></span>
                    <h3><?= e($titulo) ?></h3>
                    <p><?= e($texto) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__head">
            <span class="eyebrow">La bandada</span>
            <h2>Conoce al equipo</h2>
        </div>
        <div class="grid grid--3">
            <?php foreach ($equipo as [$nombre, $rol, $emoji, $bio]): ?>
                <div class="card feature">
                    <span class="feature__icon"><?= e($emoji) ?></span>
                    <h3><?= e($nombre) ?></h3>
                    <p class="muted" style="font-weight:700;color:var(--beak-dark);"><?= e($rol) ?></p>
                    <p><?= e($bio) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="account-grid">
            <div class="stat text-center"><div class="stat__value">8</div><div class="stat__label">años flotando</div></div>
            <div class="stat text-center"><div class="stat__value">120+</div><div class="stat__label">modelos de patitos</div></div>
            <div class="stat text-center"><div class="stat__value">45k</div><div class="stat__label">hogares felices</div></div>
            <div class="stat text-center"><div class="stat__value">99%</div><div class="stat__label">clientes que vuelven</div></div>
        </div>
    </div>
</section>
<?php
render_footer();
