<?php
// Conexión a la base de datos. Los datos se pueden cambiar con variables de entorno.
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
                getenv('TIENDA_DB_HOST') ?: 'localhost',
                getenv('TIENDA_DB_NAME') ?: 'microtienda'),
            getenv('TIENDA_DB_USER') ?: 'microtienda',
            getenv('TIENDA_DB_PASS') ?: 'Microtienda123$',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    return $pdo;
}

function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function euros(float $cantidad): string
{
    return number_format($cantidad, 2, ',', '.') . ' €';
}
