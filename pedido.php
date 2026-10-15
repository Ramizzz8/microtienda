<?php
require_once __DIR__ . '/includes/db.php';

$consulta = db()->prepare('SELECT * FROM pedidos WHERE numero = ?');
$consulta->execute([$_GET['numero'] ?? '']);
$pedido = $consulta->fetch();

$lineas = [];
if ($pedido) {
    $consulta = db()->prepare('SELECT nombre, precio_unitario, cantidad FROM lineas_pedido WHERE pedido_id = ? ORDER BY id');
    $consulta->execute([$pedido['id']]);
    $lineas = $consulta->fetchAll();
}
http_response_code($pedido ? 200 : 404);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $pedido ? 'Pedido ' . e($pedido['numero']) : 'Pedido no encontrado' ?> · Microtienda</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/tienda.css">
</head>
<body>
  <header class="cabecera cabecera-pequena">
    <h1><a href="index.php">Microtienda</a></h1>
  </header>

  <main class="ticket-envoltorio">
    <?php if (!$pedido): ?>
      <section class="ticket">
        <h2>¡Ese pedido no existe!</h2>
        <p>Igual se lo ha llevado un correcaminos. <a href="index.php">Volver a la tienda</a></p>
      </section>
    <?php else: ?>
      <section class="ticket">
        <p class="sello">¡PEDIDO RECIBIDO!</p>
        <h2>Gracias, <?= e($pedido['nombre']) ?> 🎉</h2>
        <p>Te enviaremos la confirmación a <strong><?= e($pedido['email']) ?></strong>.</p>

        <dl class="datos-pedido">
          <div><dt>Nº de pedido</dt><dd><?= e($pedido['numero']) ?></dd></div>
          <div><dt>Fecha</dt><dd><?= date('d/m/Y H:i', strtotime($pedido['creado_en'])) ?></dd></div>
        </dl>

        <table>
          <thead><tr><th>Producto</th><th>Uds.</th><th>Precio</th><th>Subtotal</th></tr></thead>
          <tbody>
            <?php foreach ($lineas as $l): ?>
              <tr>
                <td><?= e($l['nombre']) ?></td>
                <td><?= (int) $l['cantidad'] ?></td>
                <td><?= euros((float) $l['precio_unitario']) ?></td>
                <td><?= euros($l['precio_unitario'] * $l['cantidad']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot><tr><td colspan="3">Total</td><td><?= euros((float) $pedido['total']) ?></td></tr></tfoot>
        </table>

        <a class="enviar volver-tienda" href="index.php">Seguir comprando</a>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
