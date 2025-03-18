<?php 
	include("con_db.php");

	$mensaje = ""; // variable para almacenar el mensaje de respuesta

	if (isset($_POST['btn_register'])) {  

		if (!empty($_POST['nombre']) && !empty($_POST['correo']) && 
			!empty($_POST['usuario']) && !empty($_POST['contrasena'])) {

			$id = rand(0, 999999);

			$nombre = trim($_POST['nombre']);
			$correo = trim($_POST['correo']);
			$usuario = trim($_POST['usuario']);
			$contrasena = password_hash(trim($_POST['contrasena']), PASSWORD_DEFAULT); // encriptar contraseña
			$fechareg = date("Y-m-d");

			$consulta = "INSERT INTO login_db (id, nombre, contrasena, usuario, correo, fecha_reg)
						VALUES ('$id','$nombre','$contrasena','$usuario','$correo','$fechareg')";

			$resultado = mysqli_query($conexion, $consulta);

			if ($resultado) {
				$mensaje = "<h3 class='ok'>Te registraste correctamente</h3>";
				
				// redirección a la misma pagina para evitar el reenvio de datos
				header("Location: index.php");
				exit;  // Asegura que no se ejecute el codigo siguiente
			} else {
				$mensaje = "<h3 class='bad'>Ocurrió un error: " . mysqli_error($conexion) . "</h3>";
			}
		} else {
			$mensaje = "<h3 class='bad'>Completa todos los campos</h3>";
		}
	}
?>

