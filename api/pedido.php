<?php
// Recibe un pedido en JSON y lo guarda. Los precios SIEMPRE se toman de la base de datos:
// lo que envía el navegador solo indica qué productos y cuántos.
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

function responder(int $codigo, array $datos): never
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['error' => 'Método no permitido']);
}

$datos = json_decode(file_get_contents('php://input'), true);
$cliente = $datos['cliente'] ?? [];
$lineas = $datos['productos'] ?? [];

// ---------- Validación ----------
$errores = [];
$nombre = trim($cliente['nombre'] ?? '');
$apellidos = trim($cliente['apellidos'] ?? '');
$email = trim($cliente['email'] ?? '');

if ($nombre === '' || mb_strlen($nombre) > 80) $errores['nombre'] = 'Escribe tu nombre.';
if ($apellidos === '' || mb_strlen($apellidos) > 120) $errores['apellidos'] = 'Escribe tus apellidos.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores['email'] = 'El email no es válido.';

// Agrupa cantidades por producto y descarta valores raros
$cantidades = [];
foreach (is_array($lineas) ? $lineas : [] as $linea) {
    $id = filter_var($linea['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $cantidad = filter_var($linea['cantidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);
    if ($id && $cantidad) $cantidades[$id] = min(99, ($cantidades[$id] ?? 0) + $cantidad);
}
if (!$cantidades) $errores['carrito'] = 'El carrito está vacío.';

if ($errores) {
    responder(422, ['errores' => $errores]);
}

// ---------- Precios reales desde la base de datos ----------
$marcadores = implode(',', array_fill(0, count($cantidades), '?'));
$consulta = db()->prepare("SELECT id, nombre, precio FROM productos WHERE id IN ($marcadores)");
$consulta->execute(array_keys($cantidades));
$productos = $consulta->fetchAll(PDO::FETCH_UNIQUE);

if (count($productos) !== count($cantidades)) {
    responder(422, ['errores' => ['carrito' => 'Algún producto ya no está disponible. Recarga la página.']]);
}

$total = 0;
foreach ($cantidades as $id => $cantidad) {
    $total += $productos[$id]['precio'] * $cantidad;
}

// ---------- Guardado en una transacción: o se guarda todo o nada ----------
$pdo = db();
$pdo->beginTransaction();
try {
    $numero = 'MT-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 7));
    $pdo->prepare('INSERT INTO pedidos (numero, nombre, apellidos, email, total) VALUES (?, ?, ?, ?, ?)')
        ->execute([$numero, $nombre, $apellidos, $email, $total]);
    $pedidoId = (int) $pdo->lastInsertId();

    $insertar = $pdo->prepare('INSERT INTO lineas_pedido (pedido_id, producto_id, nombre, precio_unitario, cantidad) VALUES (?, ?, ?, ?, ?)');
    foreach ($cantidades as $id => $cantidad) {
        $insertar->execute([$pedidoId, $id, $productos[$id]['nombre'], $productos[$id]['precio'], $cantidad]);
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    responder(500, ['errores' => ['general' => 'No se pudo guardar el pedido. Inténtalo de nuevo.']]);
}

responder(201, ['numero' => $numero, 'total' => round($total, 2)]);
