<!DOCTYPE html>
<html>
<head>
	<title>Registrar usuario</title>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="estilo.css">
</head>
<body>
    <form method="post">
    	<h1>Registrar</h1>
    	<input type="text" name="nombre" placeholder="Nombre completo" required>
		<input type="email" name="correo" placeholder="Correo electrónico" required>
		<input type="text" name="usuario" placeholder="Usuario" required>
		<input type="password" name="contrasena" placeholder="Contraseña" required>
		<input type="submit" value="Registrar" name="btn_register">
    </form>
        <?php 
        include("registrar.php");
        ?>
</body>
</html>