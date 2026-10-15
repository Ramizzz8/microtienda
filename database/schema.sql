-- ============================================================
-- Microtienda · Esquema y datos de ejemplo (MySQL / MariaDB)
-- Ejecutar como root:  mysql -u root -p < database/schema.sql
-- ============================================================

DROP DATABASE IF EXISTS microtienda;
CREATE DATABASE microtienda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE microtienda;

CREATE USER IF NOT EXISTS 'microtienda'@'localhost' IDENTIFIED BY 'Microtienda123$';
GRANT SELECT, INSERT ON microtienda.* TO 'microtienda'@'localhost';
FLUSH PRIVILEGES;

CREATE TABLE productos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(80)   NOT NULL,
    descripcion VARCHAR(255)  NOT NULL,
    precio      DECIMAL(8,2)  NOT NULL,
    icono       VARCHAR(16)   NOT NULL,
    color       CHAR(7)       NOT NULL DEFAULT '#baffc9',
    stock       SMALLINT UNSIGNED NOT NULL DEFAULT 10
);

CREATE TABLE pedidos (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero     CHAR(10)      NOT NULL UNIQUE,
    nombre     VARCHAR(80)   NOT NULL,
    apellidos  VARCHAR(120)  NOT NULL,
    email      VARCHAR(160)  NOT NULL,
    total      DECIMAL(10,2) NOT NULL,
    creado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cada línea guarda el nombre y el precio del momento de la compra,
-- así el pedido no cambia aunque luego se modifique el producto
CREATE TABLE lineas_pedido (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id       INT UNSIGNED NOT NULL,
    producto_id     INT UNSIGNED NOT NULL,
    nombre          VARCHAR(80)  NOT NULL,
    precio_unitario DECIMAL(8,2) NOT NULL,
    cantidad        SMALLINT UNSIGNED NOT NULL,
    FOREIGN KEY (pedido_id)   REFERENCES pedidos(id)   ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
);

INSERT INTO productos (nombre, descripcion, precio, icono, color) VALUES
('Cohete a pedales',     'Para perseguir correcaminos sin gastar gasolina. Casco no incluido.',           89.99, '🚀', '#baffc9'),
('Imán gigante',         'Atrae objetos metálicos a 100 metros. Y algún que otro yunque.',                 34.50, '🧲', '#bae1ff'),
('Monopatín a reacción', 'Velocidad punta: «demasiado rápido». Frenos opcionales (se venden aparte).',     59.00, '🛹', '#ffb7ce'),
('Agujero portátil',     'Despliégalo en cualquier pared y atraviésala. Funciona solo con tus enemigos.',  24.99, '🕳️', '#fff3a0'),
('Paraguas antiyunques', 'Varillas de acero reforzado. Probado con 300 kg de hierro caído del cielo.',      19.90, '☂️', '#d5c6ff'),
('Martillo gigante',     'Ideal para golpes de efecto. Mango ergonómico y cabeza de 50 kg.',               42.00, '🔨', '#ffd6a5'),
('Kit de disfraces',     'Bigote, gafas y sombrero: nadie te reconocerá. Garantizado.',                    15.75, '🎭', '#caffbf'),
('Alpiste gourmet',      'Irresistible para cualquier pájaro veloz. Úsalo con responsabilidad.',            7.95, '🌾', '#ffc6ff'),
('Muelles saltarines',   'Salta como un canguro y alcanza cualquier cornisa de un solo brinco.',              29.99, '🦘', '#9bf6ff');
