<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $nombre = $_POST['nombre_invitado'];
    $correo = $_POST['correo_invitado'];

    // Validar que los campos no estén vacíos
    if (!empty($nombre) && !empty($correo)) {
        // Escapar los datos para evitar problemas de seguridad
        $nombre = escapeshellarg(arg: $nombre);
        $correo = escapeshellarg(arg: $correo);

        // Ruta al script de Python
        $ruta_python = escapeshellcmd(command: "python ../script_python/enviar_qr.py");

        // Ejecutar el script de Python
        $comando = "$ruta_python $correo $nombre";
        $output = shell_exec(command: $comando . " 2>&1"); // Capturar la salida del script

        // Verificar si el script se ejecutó correctamente
        if ($output) {
            // Redirigir a la página principal con un mensaje de éxito
            header(header: "Location: ../index.html?mensaje=exito");
            exit();
        } else {
            echo "Hubo un error al ejecutar el script de Python.";
        }
    } else {
        echo "Por favor, complete todos los campos.";
    }
} else {
    echo "Método no permitido.";
}
?>