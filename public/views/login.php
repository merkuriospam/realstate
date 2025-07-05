<h1>Login Admin</h1>
<?php if(isset($error)): ?>
    <p style="color: red;"><?= $error ?></p>
<?php endif; ?>

<form method="POST" action="/login">
    <input type="password" name="password" placeholder="Contraseña" required autofocus>
    <button type="submit">Ingresar</button>
</form>