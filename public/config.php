<?php
define('DB_FILE', 'propiedades.db');
define('ADMIN_PASS', 'tutuca1976');

// Crear base de datos si no existe
$db = new SQLite3(DB_FILE);

// Crear tabla
$db->exec('CREATE TABLE IF NOT EXISTS articulos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo TEXT NOT NULL,
    descripcion TEXT,
    precio REAL,
    direccion TEXT,
    tipo_operacion INTEGER DEFAULT 1,
    imagenes TEXT
)');

// Intentar agregar columna (ignorar error si ya existe)
@$db->exec('ALTER TABLE articulos ADD COLUMN tipo_operacion INTEGER DEFAULT 1');

$db->close();

// Configurar Flight
Flight::set('flight.views.path', 'views');

// Definir tipos de operación
define('TIPO_VENTA', 1);
define('TIPO_ALQUILER', 2);
define('TIPO_ALQUILER_TEMPORAL', 3);

function getTipoOperacion($tipo) {
    $tipos = [
        TIPO_VENTA => 'Venta',
        TIPO_ALQUILER => 'Alquiler',
        TIPO_ALQUILER_TEMPORAL => 'Alquiler Temporal'
    ];
    return $tipos[$tipo] ?? 'Venta';
}