<h1><?= htmlspecialchars($propiedad['titulo']) ?></h1>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div>
        <?php if(!empty($propiedad['imagenes'])): ?>
            <div style="margin-bottom: 20px;">
                <?php foreach($propiedad['imagenes'] as $img): ?>
                    <img src="/uploads/<?= $img ?>" style="width: 100%; margin-bottom: 10px;">
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Sin imágenes</p>
        <?php endif; ?>
    </div>
    
    <div>
        <h2>Detalles</h2>
        <p><strong>Tipo:</strong> <?= getTipoOperacion($propiedad['tipo_operacion'] ?? 1) ?></p>
        <p><strong>Precio:</strong> $<?= number_format($propiedad['precio'], 2) ?></p>
        <p><strong>Dirección:</strong> <?= htmlspecialchars($propiedad['direccion']) ?></p>
        
        <h3>Descripción</h3>
        <p><?= nl2br(htmlspecialchars($propiedad['descripcion'])) ?></p>
        
        <div style="margin-top: 30px;">
            <a href="/buscar" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none;">← Volver al buscador</a>
            <?php if(isset($_SESSION['auth'])): ?>
                <a href="/editar/<?= $propiedad['id'] ?>" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; margin-left: 10px;">Editar</a>
            <?php endif; ?>
        </div>
    </div>
</div>