<?php	

	include("conex_db.php");  // conectar la base de datos

	$mensaje = ""; // variable para almacenar el mensaje de respuesta

	if (isset($_POST['btn_register'])) {  

		if (!empty($_POST['nombre']) && !empty($_POST['correo']) && 
			!empty($_POST['usuario']) && !empty($_POST['contrasena'])) {

				function generarID() {                      // funcion para generar la ID aleatoria
					$letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; // Letras aleatorias
					$numeros = '0123456789'; // Numero aleatorios
					
					// Generar 3 letras aleatorias
					$letrasAleatorias = '';
					for ($i = 0; $i < 3; $i++) {
						$letrasAleatorias .= $letras[rand(0, strlen($letras) - 1)];
					}
				
					// Generar 3 números aleatorios
					$numerosAleatorios = '';
					for ($i = 0; $i < 3; $i++) {
						$numerosAleatorios .= $numeros[rand(0, strlen($numeros) - 1)];
					}
				
					// Combinar letras y números
					$combinado = $letrasAleatorias . $numerosAleatorios;
				
					// Mezclar el resultado aleatoriamente
					$idAleatoria = str_shuffle($combinado);
				
					return $idAleatoria;
				}
				
				$id = generarID();	// Llamamos a la funcion y guardamos el string en la variable ID			

			// Inicializamos las variables para recibir datos
			$nombre = trim($_POST['nombre']); 
			$correo = trim($_POST['correo']);
			$usuario = trim($_POST['usuario']);
			$contrasena = password_hash(trim($_POST['contrasena']), PASSWORD_DEFAULT); // encriptar contraseña
			$fechareg = date("Y-m-d");

			// Consultamos en la base de datos
			$consulta = "INSERT INTO login_db (id, nombre, contrasena, usuario, correo, fecha_reg)
						VALUES ('$id','$nombre','$contrasena','$usuario','$correo','$fechareg')";

			$resultado = mysqli_query($conexion, $consulta);

			if ($resultado) {
				// $mensaje = "<h3 class='ok'>Te registraste correctamente</h3>";
				
				// codigo para crear el QR
				
				header("Location: ../html/registro_inicio_sesion.html");
				exit;
			} else {
				$mensaje = "<h3 class='bad'>Ocurrió un error: " . mysqli_error($conexion) . "</h3>";
			}
		} else {
			$mensaje = "<h3 class='bad'>Completa todos los campos</h3>";
		}
	}
?>

