<?php
require 'flight/Flight.php';
require 'config.php';

// Middleware de autenticación
Flight::before('start', function(&$params, &$output){
    $rutas_publicas = ['/login'];
    if(!in_array(Flight::request()->url, $rutas_publicas)) {
        session_start();
        if(!isset($_SESSION['auth'])) {
            Flight::redirect('/login');
        }
    }
});

// Ruta login
Flight::route('GET /login', function(){
    Flight::render('login');
});

Flight::route('POST /login', function(){
    if(Flight::request()->data->password === ADMIN_PASS) {
        session_start();
        $_SESSION['auth'] = true;
        Flight::redirect('/');
    } else {
        Flight::render('login', ['error' => 'Contraseña incorrecta']);
    }
});

// Ruta principal - Listar
Flight::route('/', function(){
    $db = new SQLite3(DB_FILE);
    $results = $db->query('SELECT * FROM articulos ORDER BY id DESC');
    $propiedades = [];
    while($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $propiedades[] = $row;
    }
    Flight::render('list', ['propiedades' => $propiedades]);
});

// Ruta nueva propiedad
Flight::route('GET /nueva', function(){
    Flight::render('form');
});

Flight::route('POST /nueva', function(){
    $db = new SQLite3(DB_FILE);
    $data = Flight::request()->data;
    $files = Flight::request()->files;
    
    $propiedad_id = uniqid();
    $imagenes = [];
    
    // Procesar imágenes
    for($i = 0; $i < 5; $i++) {
        if(isset($files["img$i"]) && $files["img$i"]['error'] == 0) {
            $ext = pathinfo($files["img$i"]['name'], PATHINFO_EXTENSION);
            $nombre_original = pathinfo($files["img$i"]['name'], PATHINFO_FILENAME);
            $nombre = $propiedad_id . '_' . $nombre_original . '.' . $ext;
            
            if(move_uploaded_file($files["img$i"]['tmp_name'], "uploads/$nombre")) {
                $imagenes[] = $nombre;
            }
        }
    }
    
    sort($imagenes);
    
    $stmt = $db->prepare('INSERT INTO articulos (titulo, descripcion, precio, direccion, imagenes) VALUES (:titulo, :desc, :precio, :dir, :imgs)');
    $stmt->bindValue(':titulo', $data->titulo);
    $stmt->bindValue(':desc', $data->descripcion);
    $stmt->bindValue(':precio', $data->precio);
    $stmt->bindValue(':dir', $data->direccion);
    $stmt->bindValue(':imgs', json_encode($imagenes));
    $stmt->execute();
    
    Flight::redirect('/');
});

// Ruta editar
Flight::route('GET /editar/@id', function($id){
    $db = new SQLite3(DB_FILE);
    $stmt = $db->prepare('SELECT * FROM articulos WHERE id = :id');
    $stmt->bindValue(':id', $id);
    $result = $stmt->execute();
    $propiedad = $result->fetchArray(SQLITE3_ASSOC);
    
    if(!$propiedad) {
        Flight::redirect('/');
    }
    
    $propiedad['imagenes'] = json_decode($propiedad['imagenes'] ?: '[]');
    sort($propiedad['imagenes']);
    
    Flight::render('edit', ['propiedad' => $propiedad]);
});

Flight::route('POST /editar/@id', function($id){
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
    if(isset($data->eliminar)) {
        foreach($data->eliminar as $idx) {
            if(isset($imagenes[$idx])) {
                @unlink("uploads/" . $imagenes[$idx]);
                unset($imagenes[$idx]);
            }
        }
        $imagenes = array_values($imagenes);
    }
    
    // Subir nuevas imágenes
    for($i = 0; $i < 5; $i++) {
        if(isset($files["img$i"]) && $files["img$i"]['error'] == 0) {
            $ext = pathinfo($files["img$i"]['name'], PATHINFO_EXTENSION);
            $nombre_original = pathinfo($files["img$i"]['name'], PATHINFO_FILENAME);
            $nombre = $propiedad_id . '_' . $nombre_original . '.' . $ext;
            
            if(move_uploaded_file($files["img$i"]['tmp_name'], "uploads/$nombre")) {
                $imagenes[] = $nombre;
            }
        }
    }
    
    sort($imagenes);
    
    $stmt = $db->prepare('UPDATE articulos SET titulo=:titulo, descripcion=:desc, precio=:precio, direccion=:dir, imagenes=:imgs WHERE id=:id');
    $stmt->bindValue(':titulo', $data->titulo);
    $stmt->bindValue(':desc', $data->descripcion);
    $stmt->bindValue(':precio', $data->precio);
    $stmt->bindValue(':dir', $data->direccion);
    $stmt->bindValue(':imgs', json_encode($imagenes));
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    
    Flight::redirect('/');
});

// Ruta eliminar
Flight::route('GET /eliminar/@id', function($id){
    $db = new SQLite3(DB_FILE);
    
    $stmt = $db->prepare('SELECT imagenes FROM articulos WHERE id = :id');
    $stmt->bindValue(':id', $id);
    $result = $stmt->execute();
    $row = $result->fetchArray();
    
    if($row) {
        $imagenes = json_decode($row['imagenes'] ?: '[]');
        foreach($imagenes as $img) {
            @unlink("uploads/$img");
        }
        
        $stmt = $db->prepare('DELETE FROM articulos WHERE id = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }
    
    Flight::redirect('/');
});

// Logout
Flight::route('/logout', function(){
    session_start();
    session_destroy();
    Flight::redirect('/login');
});

Flight::start();