<h1>Editar Propiedad</h1>

<form method="POST" action="/editar/<?= $propiedad['id'] ?>" enctype="multipart/form-data">
    <select name="tipo_operacion" required>
        <option value="1" <?= $propiedad['tipo_operacion'] == 1 ? 'selected' : '' ?>>Venta</option>
        <option value="2" <?= $propiedad['tipo_operacion'] == 2 ? 'selected' : '' ?>>Alquiler</option>
        <option value="3" <?= $propiedad['tipo_operacion'] == 3 ? 'selected' : '' ?>>Alquiler Temporal</option>
    </select>
    
    <input type="text" name="titulo" value="<?= htmlspecialchars($propiedad['titulo']) ?>" required>
    <textarea name="descripcion"><?= htmlspecialchars($propiedad['descripcion']) ?></textarea>
    <input type="number" name="precio" value="<?= $propiedad['precio'] ?>" step="0.01" required>
    <input type="text" name="direccion" value="<?= htmlspecialchars($propiedad['direccion']) ?>" required>
    
    <h3>Imágenes actuales:</h3>
    <?php foreach($propiedad['imagenes'] as $idx => $img): ?>
        <div class="imagen-actual">
            <img src="/uploads/<?= $img ?>">
            <label>
                <input type="checkbox" name="eliminar[]" value="<?= $idx ?>"> Eliminar esta imagen
            </label>
        </div>
    <?php endforeach; ?>
    
    <h3>Agregar nuevas imágenes:</h3>
    <p><small>Tip: Nombra tus archivos 01-frente.jpg, 02-cocina.jpg, etc. para controlar el orden</small></p>
    <?php for($i = 0; $i < 5 - count($propiedad['imagenes']); $i++): ?>
        <input type="file" name="img<?= $i ?>" accept="image/*">
    <?php endfor; ?>
    
    <button type="submit">Actualizar Propiedad</button>
</form>