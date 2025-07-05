<h1>Propiedades</h1>

<a href="/nueva" style="display: inline-block; margin-bottom: 20px; padding: 10px 15px; background: #007bff; color: white; text-decoration: none;">+ Nueva Propiedad</a>

<table>
    <tr>
        <th>ID</th>
        <th>Tipo</th>
        <th>Título</th>
        <th>Precio</th>
        <th>Dirección</th>
        <th>Imágenes</th>
        <th>Acciones</th>
    </tr>
    <?php foreach($propiedades as $prop): ?>
    <tr>
        <td><?= $prop['id'] ?></td>
        <td><?= getTipoOperacion($prop['tipo_operacion'] ?? 1) ?></td>
        <td><?= htmlspecialchars($prop['titulo']) ?></td>
        <td>$<?= number_format($prop['precio'], 2) ?></td>
        <td><?= htmlspecialchars($prop['direccion']) ?></td>
        <td><?= count(json_decode($prop['imagenes'] ?: '[]')) ?> fotos</td>
        <td>
            <a href="/editar/<?= $prop['id'] ?>">Editar</a> |
            <a href="/eliminar/<?= $prop['id'] ?>" onclick="return confirm('¿Eliminar esta propiedad?')">Eliminar</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>