<?php
include("conex_db.php");  // Conectar la base de datos

if (isset($_POST['btn_inicio_admin'])) {  

    // Verificar que los campos no estén vacíos
    if (!empty($_POST['usuario_admin']) && !empty($_POST['contrasena'])) {

        // Almacenar los datos ingresados en el formulario
        $usuario_admin = $_POST['usuario_admin'];
        $contrasena = $_POST['contrasena'];

        // Escapar caracteres especiales para evitar SQL Injection
        $usuario_admin = mysqli_real_escape_string($conexion, $usuario_admin);
        $contrasena = mysqli_real_escape_string($conexion, $contrasena);

        // Consultar si el usuario existe en la base de datos
        $consulta = "SELECT * FROM admin_db WHERE usuario = '$usuario_admin' AND contrasena = '$contrasena'";

        // Ejecutar la consulta
        $resultado = mysqli_query($conexion, $consulta);

        // Verificar si el usuario y la contraseña son correctos
        if (mysqli_num_rows($resultado) > 0) {
            // Si el usuario existe, redirigir a la página HTML de inicio de sesión
            header("Location: ../html/registro_inicio_sesion.html");  // Redirigir al archivo HTML en la carpeta 'html'
            
        } else {
            // Si el usuario no existe o la contraseña es incorrecta
            echo '<div class="alert alert-danger">Usuario o contraseña incorrectos.</div>';
        }

    } else {
        // Si los campos están vacíos
        echo '<div class="alert alert-danger">Los campos están vacíos</div>';
    }
}
?>
