<!DOCTYPE html>
<html>
<head>
    <title>Admin Inmobiliaria</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        form { max-width: 600px; }
        input, textarea, button, select { width: 100%; margin-bottom: 10px; padding: 8px; }
        textarea { height: 100px; }
        .header { margin-bottom: 20px; }
        .header a { margin-right: 15px; }
        .imagen-actual { margin-bottom: 10px; padding: 10px; background: #f5f5f5; }
        .imagen-actual img { max-width: 150px; display: block; margin-bottom: 5px; }
    </style>
</head>
<body>
    <?php if(isset($_SESSION['auth'])): ?>
    <div class="header">
        <a href="/">Buscador</a>
        <a href="/admin">Listado Admin</a>
        <a href="/nueva">Nueva Propiedad</a>
        <a href="/logout" style="float: right;">Cerrar Sesión</a>
    </div>
    <?php endif; ?>
    
    <?php echo $content; ?>
</body>
</html>