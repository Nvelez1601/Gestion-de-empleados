<?php
header('Content-Type: application/json');
require 'conex_db.php';

$response = [
    'exito' => false,
    'mensaje' => 'Error desconocido',            // php encargador de validar los QR escaneados
    'usuario' => null
];

try {
    // Validación corregida (versión correcta)
    if(!isset($_POST['id']) || empty(trim($_POST['id']))) {
        throw new Exception('No se recibió el ID');
    }

    $id = mysqli_real_escape_string($conexion, trim($_POST['id']));
    
    // Validación adicional en servidor
    if(!preg_match('/^[A-Za-z0-9]{6}$/', $id)) {
        throw new Exception('Formato de ID inválido');
    }

    $query = "SELECT ID, nombre, correo, fecha_reg FROM login_db WHERE ID = '$id' LIMIT 1";
    $resultado = mysqli_query($conexion, $query);

    if(!$resultado) {
        throw new Exception('Error en consulta: ' . mysqli_error($conexion));
    }

    if(mysqli_num_rows($resultado) > 0) {
        $usuario = mysqli_fetch_assoc($resultado);
        $response = [
            'exito' => true,
            'mensaje' => 'Acceso concedido',
            'usuario' => [
                'nombre' => $usuario['nombre'],
                'correo' => $usuario['correo'],
                'fecha_reg' => $usuario['fecha_reg']
            ]
        ];
    } else {
        throw new Exception('Usuario no registrado en el sistema');
    }

} catch (Exception $e) {
    $response['mensaje'] = $e->getMessage();
} finally {
    mysqli_close($conexion);
    echo json_encode($response);
}
?>