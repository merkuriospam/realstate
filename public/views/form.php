<h1>Nueva Propiedad</h1>

<form method="POST" action="/nueva" enctype="multipart/form-data">
    <input type="text" name="titulo" placeholder="Título" required>
    <textarea name="descripcion" placeholder="Descripción"></textarea>
    <input type="number" name="precio" placeholder="Precio" step="0.01" required>
    <input type="text" name="direccion" placeholder="Dirección" required>
    
    <h3>Imágenes (máx 5):</h3>
    <p><small>Tip: Nombra tus archivos 01-frente.jpg, 02-cocina.jpg, etc. para controlar el orden</small></p>
    <?php for($i = 0; $i < 5; $i++): ?>
        <input type="file" name="img<?= $i ?>" accept="image/*">
    <?php endfor; ?>
    
    <button type="submit">Guardar Propiedad</button>
</form>