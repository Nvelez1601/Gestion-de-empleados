<?php
header('Content-Type: application/json');
require 'conex_db.php';

$response = [
    'exito' => false,
    'mensaje' => 'Error desconocido'
];

try {
    // 1. Validar que recibimos el contenido del QR
    if(!isset($_POST['contenido_qr']) || empty(trim($_POST['contenido_qr']))) {
        throw new Exception('No se recibió el contenido del QR');
    }

    $contenido_qr = trim($_POST['contenido_qr']);
    
    // 2. Separar las líneas del QR (nombre y correo)
    $lineas = explode("\n", $contenido_qr);
    
    if(count($lineas) < 2) {
        throw new Exception('El formato del QR es incorrecto. Debe contener al menos 2 líneas (nombre y correo)');
    }

    $nombre = mysqli_real_escape_string($conexion, trim($lineas[0]));
    $correo = mysqli_real_escape_string($conexion, trim($lineas[1]));
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i:s');

    // 3. Validar formato del correo
    if(!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El correo electrónico no tiene un formato válido');
    }

    // 4. Insertar en la base de datos
    $query = "INSERT INTO invitados 
             (nombre, correo, fecha, hora) 
             VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'ssss', $nombre, $correo, $fecha_actual, $hora_actual);
    $resultado = mysqli_stmt_execute($stmt);

    if(!$resultado) {
        throw new Exception('Error al guardar en base de datos: ' . mysqli_error($conexion));
    }

    $response = [
        'exito' => true,
        'mensaje' => 'Invitado registrado correctamente',
        'datos' => [
            'nombre' => $nombre,
            'correo' => $correo,
            'fecha' => $fecha_actual,
            'hora' => $hora_actual
        ]
    ];

} catch (Exception $e) {
    $response['mensaje'] = $e->getMessage();
} finally {
    if(isset($stmt)) mysqli_stmt_close($stmt);
    if(isset($conexion)) mysqli_close($conexion);
    echo json_encode($response);
}