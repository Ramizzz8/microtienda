<?php
require_once __DIR__ . '/includes/db.php';

$productos = db()->query('SELECT id, nombre, descripcion, precio, icono, color FROM productos ORDER BY id')->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Microtienda · Inventos para cazar correcaminos</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/tienda.css">
</head>
<body>
  <header class="cabecera">
    <h1>Microtienda</h1>
    <p>Inventos de dudosa eficacia para cazar correcaminos</p>
    <button class="boton-carrito" id="abrir-carrito" aria-label="Abrir carrito">
      🛒 <span id="contador-carrito">0</span>
    </button>
  </header>

  <main>
    <section class="productos" aria-label="Productos">
      <?php foreach ($productos as $i => $p): ?>
        <article class="producto" style="--fondo: <?= e($p['color']) ?>; --giro: <?= [-2, 1.5, -1, 2, -1.5, 1][$i % 6] ?>deg">
          <div class="icono" aria-hidden="true"><?= e($p['icono']) ?></div>
          <h2><?= e($p['nombre']) ?></h2>
          <p><?= e($p['descripcion']) ?></p>
          <div class="pie-producto">
            <span class="precio"><?= euros((float) $p['precio']) ?></span>
            <button class="anadir"
                    data-id="<?= (int) $p['id'] ?>"
                    data-nombre="<?= e($p['nombre']) ?>"
                    data-precio="<?= e($p['precio']) ?>"
                    data-icono="<?= e($p['icono']) ?>">¡Lo quiero!</button>
          </div>
        </article>
      <?php endforeach; ?>
    </section>
  </main>

  <!-- Carrito lateral -->
  <div class="velo" id="velo"></div>
  <aside class="carrito" id="carrito" aria-label="Carrito">
    <div class="carrito-cabecera">
      <h2>Tu carrito</h2>
      <button class="cerrar" id="cerrar-carrito" aria-label="Cerrar">✕</button>
    </div>

    <ul class="lineas" id="lineas"></ul>
    <p class="vacio" id="vacio">Tu carrito está más vacío que el desierto. 🌵</p>

    <div class="total"><span>Total</span><strong id="total">0,00 €</strong></div>

    <form id="formulario" novalidate>
      <h3>Tus datos</h3>
      <label><input name="nombre" placeholder="Nombre" autocomplete="given-name"><small data-error="nombre"></small></label>
      <label><input name="apellidos" placeholder="Apellidos" autocomplete="family-name"><small data-error="apellidos"></small></label>
      <label><input name="email" type="email" placeholder="Email" autocomplete="email"><small data-error="email"></small></label>
      <small class="error-general" data-error="carrito"></small>
      <small class="error-general" data-error="general"></small>
      <button type="submit" class="enviar" id="enviar">Enviar pedido 🚀</button>
    </form>
  </aside>

  <div class="toast" id="toast" role="status"></div>

  <footer>(c) <?= date('Y') ?> Andrés Ramírez · Ningún coyote resultó herido en el desarrollo de esta tienda</footer>

  <script src="assets/tienda.js"></script>
</body>
</html>
