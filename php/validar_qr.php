<?php
header('Content-Type: application/json');
require 'conex_db.php';

$response = [
    'exito' => false,
    'mensaje' => 'Error desconocido',
    'usuario' => null
];

try {
    // 1. Validar que recibimos el ID del empleado
    if(!isset($_POST['id']) || empty(trim($_POST['id']))) {
        throw new Exception('No se recibió el ID del empleado');
    }

    $id_empleado = mysqli_real_escape_string($conexion, trim($_POST['id']));

    // 2. Validación de formato
    if(!preg_match('/^[A-Za-z0-9]{6}$/', $id_empleado)) {
        throw new Exception('Formato de ID inválido (debe ser 6 caracteres alfanuméricos)');
    }

    // 3. Verificar que el empleado existe en login_db
    $query_empleado = "SELECT ID, CONCAT(nombre, ' ', apellido) AS nombre_completo 
                      FROM login_db 
                      WHERE ID = ? 
                      LIMIT 1";
    
    $stmt_empleado = mysqli_prepare($conexion, $query_empleado);
    mysqli_stmt_bind_param($stmt_empleado, 's', $id_empleado);
    mysqli_stmt_execute($stmt_empleado);
    $result_empleado = mysqli_stmt_get_result($stmt_empleado);

    if(!$result_empleado) {
        throw new Exception('Error al buscar empleado: ' . mysqli_error($conexion));
    }

    if(mysqli_num_rows($result_empleado) === 0) {
        throw new Exception('Empleado no registrado en el sistema');
    }

    $empleado = mysqli_fetch_assoc($result_empleado);

    // 4. Verificar registros de hoy en historial_accesos
    $query_verificacion = "SELECT 
                          SUM(CASE WHEN hora_entrada IS NOT NULL THEN 1 ELSE 0 END) AS entradas,
                          SUM(CASE WHEN hora_salida IS NOT NULL THEN 1 ELSE 0 END) AS salidas
                          FROM historial_accesos
                          WHERE empleado_id = ?
                          AND fecha = CURDATE()";
    
    $stmt_verificacion = mysqli_prepare($conexion, $query_verificacion);
    mysqli_stmt_bind_param($stmt_verificacion, 's', $id_empleado);
    mysqli_stmt_execute($stmt_verificacion);
    $result_verificacion = mysqli_stmt_get_result($stmt_verificacion);

    if(!$result_verificacion) {
        throw new Exception('Error al verificar asistencia: ' . mysqli_error($conexion));
    }

    $registros = mysqli_fetch_assoc($result_verificacion);
    $tiene_entrada = $registros['entradas'] > 0;
    $tiene_salida = $registros['salidas'] > 0;

    // 5. Validar si ya completó ambos registros hoy
    if($tiene_entrada && $tiene_salida) {
        throw new Exception('Querido empleado, usted ya ha marcado su entrada y su salida el día de hoy.');
    }

    // 6. Determinar tipo de registro (entrada o salida)
    if(!$tiene_entrada) {
        // Registrar entrada
        $query_insert = "INSERT INTO historial_accesos 
                        (empleado_id, nombre_completo, fecha, hora_entrada)
                        VALUES (?, ?, CURDATE(), CURTIME())";
        
        $stmt_insert = mysqli_prepare($conexion, $query_insert);
        mysqli_stmt_bind_param($stmt_insert, 'ss', $id_empleado, $empleado['nombre_completo']);
        $result_insert = mysqli_stmt_execute($stmt_insert);
        
        if(!$result_insert) {
            throw new Exception('Error al registrar entrada: ' . mysqli_error($conexion));
        }
        
        $response = [
            'exito' => true,
            'mensaje' => 'Entrada registrada correctamente',
            'usuario' => $empleado
        ];
    } else {
        // Registrar salida
        $query_update = "UPDATE historial_accesos 
                       SET hora_salida = CURTIME(),
                           horas_trabajadas = TIMEDIFF(CURTIME(), hora_entrada)
                       WHERE empleado_id = ?
                       AND fecha = CURDATE()
                       AND hora_salida IS NULL";
        
        $stmt_update = mysqli_prepare($conexion, $query_update);
        mysqli_stmt_bind_param($stmt_update, 's', $id_empleado);
        $result_update = mysqli_stmt_execute($stmt_update);
        
        if(!$result_update) {
            throw new Exception('Error al registrar salida: ' . mysqli_error($conexion));
        }
        
        // Obtener horas trabajadas
        $query_horas = "SELECT horas_trabajadas 
                       FROM historial_accesos
                       WHERE empleado_id = ?
                       AND fecha = CURDATE()
                       ORDER BY id DESC
                       LIMIT 1";
        
        $stmt_horas = mysqli_prepare($conexion, $query_horas);
        mysqli_stmt_bind_param($stmt_horas, 's', $id_empleado);
        mysqli_stmt_execute($stmt_horas);
        $result_horas = mysqli_stmt_get_result($stmt_horas);
        $horas = mysqli_fetch_assoc($result_horas);
        
        $response = [
            'exito' => true,
            'mensaje' => 'Salida registrada correctamente. Horas trabajadas: ' . $horas['horas_trabajadas'],
            'usuario' => $empleado
        ];
    }

} catch (Exception $e) {
    $response['mensaje'] = $e->getMessage();
} finally {
    // Cerrar todas las conexiones
    if(isset($stmt_empleado)) mysqli_stmt_close($stmt_empleado);
    if(isset($stmt_verificacion)) mysqli_stmt_close($stmt_verificacion);
    if(isset($stmt_insert)) mysqli_stmt_close($stmt_insert);
    if(isset($stmt_update)) mysqli_stmt_close($stmt_update);
    if(isset($stmt_horas)) mysqli_stmt_close($stmt_horas);
    if(isset($conexion)) mysqli_close($conexion);
    
    echo json_encode($response);
}