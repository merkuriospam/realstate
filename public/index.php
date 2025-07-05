<?php
require 'flight/Flight.php';
require 'config.php';

// Middleware de autenticación
// Middleware de autenticación
Flight::before('start', function (&$params, &$output) {
  // Obtener la ruta actual
  $route = Flight::request()->url;

  // Rutas que requieren autenticación
  $rutas_protegidas = ['/admin', '/nueva', '/editar', '/eliminar', '/actualizar'];

  // Verificar si la ruta actual requiere autenticación
  $requiere_auth = false;
  foreach ($rutas_protegidas as $ruta) {
    if (strpos($route, $ruta) === 0) {
      $requiere_auth = true;
      break;
    }
  }

  // Iniciar sesión solo si no está activa
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  // Si requiere autenticación y no está logueado, redirigir
  if ($requiere_auth && !isset($_SESSION['auth'])) {
    Flight::redirect('/login');
    return false;
  }
});

// Ruta GET para mostrar el formulario
Flight::route('GET /login', function(){
    Flight::render('login', [], 'content');
    Flight::render('layout');
});

// Ruta POST /login - CORREGIDA
Flight::route('POST /login', function () {
  if (Flight::request()->data->password === ADMIN_PASS) {
    $_SESSION['auth'] = true;  // Quitar session_start() de aquí
    Flight::redirect('/');
  } else {
    Flight::render('login', ['error' => 'Contraseña incorrecta'], 'content');
    Flight::render('layout');
  }
});


// Ruta principal - Buscador (reemplazar la ruta "/" actual)
Flight::route('/', function () {
  $db = new SQLite3(DB_FILE);
  $params = Flight::request()->query;

  $where = [];
  $bindings = [];

  // Construir consulta dinámica
  if (!empty($params->tipo_operacion)) {
    $where[] = 'tipo_operacion = :tipo';
    $bindings[':tipo'] = $params->tipo_operacion;
  }

  if (!empty($params->titulo)) {
    $where[] = 'titulo LIKE :titulo';
    $bindings[':titulo'] = '%' . $params->titulo . '%';
  }

  if (!empty($params->direccion)) {
    $where[] = 'direccion LIKE :direccion';
    $bindings[':direccion'] = '%' . $params->direccion . '%';
  }

  if (!empty($params->precio_min)) {
    $where[] = 'precio >= :precio_min';
    $bindings[':precio_min'] = $params->precio_min;
  }

  if (!empty($params->precio_max)) {
    $where[] = 'precio <= :precio_max';
    $bindings[':precio_max'] = $params->precio_max;
  }

  // Construir query
  $sql = 'SELECT * FROM articulos';
  if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
  }
  $sql .= ' ORDER BY id DESC';

  $stmt = $db->prepare($sql);
  foreach ($bindings as $key => $value) {
    $stmt->bindValue($key, $value);
  }

  $results = $stmt->execute();
  $propiedades = [];
  while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $propiedades[] = $row;
  }

  Flight::render('buscar', ['propiedades' => $propiedades], 'content');
  Flight::render('layout');
});

// Cambiar la ruta del listado admin a /admin
Flight::route('/admin', function () {
  $db = new SQLite3(DB_FILE);
  $results = $db->query('SELECT * FROM articulos ORDER BY id DESC');
  $propiedades = [];
  while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $propiedades[] = $row;
  }
  Flight::render('list', ['propiedades' => $propiedades], 'content');
  Flight::render('layout');
});

// Ruta nueva propiedad
Flight::route('GET /nueva', function () {
  Flight::render('form');
});

Flight::route('POST /nueva', function () {
  $db = new SQLite3(DB_FILE);
  $data = Flight::request()->data;
  $files = Flight::request()->files;

  $propiedad_id = uniqid();
  $imagenes = [];

  // Procesar imágenes
  for ($i = 0; $i < 5; $i++) {
    if (isset($files["img$i"]) && $files["img$i"]['error'] == 0) {
      $ext = pathinfo($files["img$i"]['name'], PATHINFO_EXTENSION);
      $nombre_original = pathinfo($files["img$i"]['name'], PATHINFO_FILENAME);
      $nombre = $propiedad_id . '_' . $nombre_original . '.' . $ext;

      if (move_uploaded_file($files["img$i"]['tmp_name'], "uploads/$nombre")) {
        $imagenes[] = $nombre;
      }
    }
  }

  sort($imagenes);

  $stmt = $db->prepare('INSERT INTO articulos (titulo, descripcion, precio, direccion, tipo_operacion, imagenes) VALUES (:titulo, :desc, :precio, :dir, :tipo, :imgs)');
  $stmt->bindValue(':titulo', $data->titulo);
  $stmt->bindValue(':desc', $data->descripcion);
  $stmt->bindValue(':precio', $data->precio);
  $stmt->bindValue(':dir', $data->direccion);
  $stmt->bindValue(':tipo', $data->tipo_operacion);
  $stmt->bindValue(':imgs', json_encode($imagenes));
  $stmt->execute();

  Flight::redirect('/');
});

// Ruta editar
Flight::route('GET /editar/@id', function ($id) {
  $db = new SQLite3(DB_FILE);
  $stmt = $db->prepare('SELECT * FROM articulos WHERE id = :id');
  $stmt->bindValue(':id', $id);
  $result = $stmt->execute();
  $propiedad = $result->fetchArray(SQLITE3_ASSOC);

  if (!$propiedad) {
    Flight::redirect('/');
  }

  $propiedad['imagenes'] = json_decode($propiedad['imagenes'] ?: '[]');
  sort($propiedad['imagenes']);

  Flight::render('edit', ['propiedad' => $propiedad]);
});

Flight::route('POST /editar/@id', function ($id) {
  $db = new SQLite3(DB_FILE);
  $data = Flight::request()->data;
  $files = Flight::request()->files;

  // Obtener imágenes actuales
  $stmt = $db->prepare('SELECT imagenes FROM articulos WHERE id = :id');
  $stmt->bindValue(':id', $id);
  $result = $stmt->execute();
  $row = $result->fetchArray();
  $imagenes = json_decode($row['imagenes'] ?: '[]');

  $propiedad_id = (!empty($imagenes) && isset($imagenes[0])) ? explode('_', $imagenes[0])[0] : uniqid();

  // Eliminar imágenes marcadas
  if (isset($data->eliminar)) {
    foreach ($data->eliminar as $idx) {
      if (isset($imagenes[$idx])) {
        @unlink("uploads/" . $imagenes[$idx]);
        unset($imagenes[$idx]);
      }
    }
    $imagenes = array_values($imagenes);
  }

  // Subir nuevas imágenes
  for ($i = 0; $i < 5; $i++) {
    if (isset($files["img$i"]) && $files["img$i"]['error'] == 0) {
      $ext = pathinfo($files["img$i"]['name'], PATHINFO_EXTENSION);
      $nombre_original = pathinfo($files["img$i"]['name'], PATHINFO_FILENAME);
      $nombre = $propiedad_id . '_' . $nombre_original . '.' . $ext;

      if (move_uploaded_file($files["img$i"]['tmp_name'], "uploads/$nombre")) {
        $imagenes[] = $nombre;
      }
    }
  }

  sort($imagenes);

  $stmt = $db->prepare('UPDATE articulos SET titulo=:titulo, descripcion=:desc, precio=:precio, direccion=:dir, tipo_operacion=:tipo, imagenes=:imgs WHERE id=:id');
  $stmt->bindValue(':titulo', $data->titulo);
  $stmt->bindValue(':desc', $data->descripcion);
  $stmt->bindValue(':precio', $data->precio);
  $stmt->bindValue(':dir', $data->direccion);
  $stmt->bindValue(':tipo', $data->tipo_operacion);
  $stmt->bindValue(':imgs', json_encode($imagenes));
  $stmt->bindValue(':id', $id);
  $stmt->execute();

  Flight::redirect('/');
});

// Ruta eliminar
Flight::route('GET /eliminar/@id', function ($id) {
  $db = new SQLite3(DB_FILE);

  $stmt = $db->prepare('SELECT imagenes FROM articulos WHERE id = :id');
  $stmt->bindValue(':id', $id);
  $result = $stmt->execute();
  $row = $result->fetchArray();

  if ($row) {
    $imagenes = json_decode($row['imagenes'] ?: '[]');
    foreach ($imagenes as $img) {
      @unlink("uploads/$img");
    }

    $stmt = $db->prepare('DELETE FROM articulos WHERE id = :id');
    $stmt->bindValue(':id', $id);
    $stmt->execute();
  }

  Flight::redirect('/');
});

// AGREGAR NUEVA RUTA - Buscador (después de la ruta principal):
Flight::route('/buscar', function () {
  $db = new SQLite3(DB_FILE);
  $params = Flight::request()->query;

  $where = [];
  $bindings = [];

  // Construir consulta dinámica
  if (!empty($params->tipo_operacion)) {
    $where[] = 'tipo_operacion = :tipo';
    $bindings[':tipo'] = $params->tipo_operacion;
  }

  if (!empty($params->titulo)) {
    $where[] = 'titulo LIKE :titulo';
    $bindings[':titulo'] = '%' . $params->titulo . '%';
  }

  if (!empty($params->direccion)) {
    $where[] = 'direccion LIKE :direccion';
    $bindings[':direccion'] = '%' . $params->direccion . '%';
  }

  if (!empty($params->precio_min)) {
    $where[] = 'precio >= :precio_min';
    $bindings[':precio_min'] = $params->precio_min;
  }

  if (!empty($params->precio_max)) {
    $where[] = 'precio <= :precio_max';
    $bindings[':precio_max'] = $params->precio_max;
  }

  // Construir query
  $sql = 'SELECT * FROM articulos';
  if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
  }
  $sql .= ' ORDER BY id DESC';

  $stmt = $db->prepare($sql);
  foreach ($bindings as $key => $value) {
    $stmt->bindValue($key, $value);
  }

  $results = $stmt->execute();
  $propiedades = [];
  while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $propiedades[] = $row;
  }

  Flight::render('buscar', ['propiedades' => $propiedades], 'content');
  Flight::render('layout');
});

// AGREGAR RUTA PARA VER DETALLES (opcional):
Flight::route('/ver/@id', function ($id) {
  $db = new SQLite3(DB_FILE);
  $stmt = $db->prepare('SELECT * FROM articulos WHERE id = :id');
  $stmt->bindValue(':id', $id);
  $result = $stmt->execute();
  $propiedad = $result->fetchArray(SQLITE3_ASSOC);

  if (!$propiedad) {
    Flight::notFound();
    return;
  }

  $propiedad['imagenes'] = json_decode($propiedad['imagenes'] ?: '[]');

  Flight::render('detalle', ['propiedad' => $propiedad], 'content');
  Flight::render('layout');
});

// Logout
Flight::route('/logout', function () {
  session_destroy();
  Flight::redirect('/login');
});

Flight::start();
