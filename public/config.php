<?php
define('DB_FILE', 'articulos.db');
define('ADMIN_PASS', 'tutuca1976');

// Crear base de datos si no existe
$db = new SQLite3(DB_FILE);
$db->exec('CREATE TABLE IF NOT EXISTS articulos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo TEXT NOT NULL,
    descripcion TEXT,
    precio REAL,
    direccion TEXT,
    imagenes TEXT
)');
$db->close();

// Configurar Flight
Flight::set('flight.views.path', 'views');