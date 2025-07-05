<h1>Buscar Propiedades</h1>

<form method="GET" action="/" style="background: #f5f5f5; padding: 20px; margin-bottom: 20px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
        <select name="tipo_operacion">
            <option value="">-- Tipo de Operación --</option>
            <option value="1" <?= ($_GET['tipo_operacion'] ?? '') == '1' ? 'selected' : '' ?>>Venta</option>
            <option value="2" <?= ($_GET['tipo_operacion'] ?? '') == '2' ? 'selected' : '' ?>>Alquiler</option>
            <option value="3" <?= ($_GET['tipo_operacion'] ?? '') == '3' ? 'selected' : '' ?>>Alquiler Temporal</option>
        </select>

        <input type="text" name="titulo" placeholder="Buscar en título" value="<?= htmlspecialchars($_GET['titulo'] ?? '') ?>">

        <input type="text" name="direccion" placeholder="Dirección" value="<?= htmlspecialchars($_GET['direccion'] ?? '') ?>">

        <input type="number" name="precio_min" placeholder="Precio mínimo" value="<?= $_GET['precio_min'] ?? '' ?>">

        <input type="number" name="precio_max" placeholder="Precio máximo" value="<?= $_GET['precio_max'] ?? '' ?>">
    </div>

    <button type="submit" style="margin-top: 10px;">Buscar</button>
    <a href="/buscar" style="margin-left: 10px;">Limpiar filtros</a>
</form>

<?php if (isset($propiedades)): ?>
    <h2>Resultados: <?= count($propiedades) ?> propiedades encontradas</h2>

    <?php if (empty($propiedades)): ?>
        <p>No se encontraron propiedades con los criterios seleccionados.</p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            <?php foreach ($propiedades as $prop): ?>
                <?php $imagenes = json_decode($prop['imagenes'] ?: '[]'); ?>
                <div style="border: 1px solid #ddd; padding: 15px;">
                    <?php if (!empty($imagenes)): ?>
                        <img src="/uploads/<?= $imagenes[0] ?>" style="width: 100%; height: 200px; object-fit: cover;">
                    <?php endif; ?>

                    <h3><?= htmlspecialchars($prop['titulo']) ?></h3>
                    <p><strong><?= getTipoOperacion($prop['tipo_operacion'] ?? 1) ?></strong></p>
                    <p>Precio: $<?= number_format($prop['precio'], 2) ?></p>
                    <p>Dirección: <?= htmlspecialchars($prop['direccion']) ?></p>
                    <p><?= htmlspecialchars(substr($prop['descripcion'], 0, 100)) ?>...</p>

                    <a href="/ver/<?= $prop['id'] ?>" style="color: #007bff;">Ver detalles</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>