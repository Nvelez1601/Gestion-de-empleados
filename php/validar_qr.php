<?php
    header('Content-Type: application/json');
    require 'conex_db.php';

    $response = [
        'exito' => false,
        'mensaje' => 'Error desconocido',
        'usuario' => null
    ];

    try {
        // Validación más robusta
        if(!isset($_POST['id'])) {
            throw new Exception('ID no recibido');
        }

        $id = trim($_POST['id']);
        
        // Validación de formato
        if(!preg_match('/^[A-Za-z0-9]{6}$/', $id)) {
            throw new Exception('Formato de ID inválido (debe ser 6 caracteres alfanuméricos)');
        }

        // Preparar consulta con sentencia preparada para mayor seguridad
        $query = "SELECT ID, CONCAT(nombre, ' ', apellido) AS nombre_completo, correo, fecha_reg 
                FROM login_db 
                WHERE ID = ? 
                LIMIT 1";
        
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, 's', $id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if(!$resultado) {
            throw new Exception('Error en consulta: ' . mysqli_error($conexion));
        }

        if(mysqli_num_rows($resultado) > 0) {
            $usuario = mysqli_fetch_assoc($resultado);
            
            // Iniciar transacción
            mysqli_begin_transaction($conexion);
            
            // Verificar si ya tiene entrada sin salida hoy
            $check_query = "SELECT id FROM historial_accesos 
                        WHERE empleado_id = ? 
                        AND fecha = CURDATE() 
                        AND hora_salida IS NULL
                        LIMIT 1";
            
            $stmt_check = mysqli_prepare($conexion, $check_query);
            mysqli_stmt_bind_param($stmt_check, 's', $id);
            mysqli_stmt_execute($stmt_check);
            $check_result = mysqli_stmt_get_result($stmt_check);
            
            if(mysqli_num_rows($check_result) > 0) {
                // Registrar salida
                $update_query = "UPDATE historial_accesos 
                    SET hora_salida = CURTIME(),
                        horas_trabajadas = TIMEDIFF(CURTIME(), hora_entrada)
                    WHERE empleado_id = ? 
                    AND fecha = CURDATE() 
                    AND hora_salida IS NULL";
                
                $stmt_update = mysqli_prepare($conexion, $update_query);
                mysqli_stmt_bind_param($stmt_update, 's', $id);
                $update_result = mysqli_stmt_execute($stmt_update);
                
                if(!$update_result) {
                    throw new Exception('Error al registrar salida: ' . mysqli_error($conexion));
                }
                
                $response = [
                    'exito' => true,
                    'mensaje' => 'Salida registrada correctamente',
                    'usuario' => $usuario
                ];
            } else {
                // Registrar nueva entrada
                $insert_query = "INSERT INTO historial_accesos 
                                (empleado_id, nombre_completo, fecha, hora_entrada)
                                VALUES (?, ?, CURDATE(), CURTIME())";
                
                $stmt_insert = mysqli_prepare($conexion, $insert_query);
                mysqli_stmt_bind_param($stmt_insert, 'ss', $id, $usuario['nombre_completo']);
                $insert_result = mysqli_stmt_execute($stmt_insert);
                
                if(!$insert_result) {
                    throw new Exception('Error al registrar acceso: ' . mysqli_error($conexion));
                }
                
                $response = [
                    'exito' => true,
                    'mensaje' => 'Acceso concedido - Entrada registrada',
                    'usuario' => $usuario
                ];
            }
            
            // Confirmar transacción
            mysqli_commit($conexion);
        } else {
            throw new Exception('Usuario no encontrado con ID: ' . htmlspecialchars($id));
        }

    } catch (Exception $e) {
        // Revertir en caso de error
        if(isset($conexion)) {
            mysqli_rollback($conexion);
        }
        $response['mensaje'] = $e->getMessage();
    } finally {
        if(isset($stmt)) mysqli_stmt_close($stmt);
        if(isset($stmt_check)) mysqli_stmt_close($stmt_check);
        if(isset($stmt_update)) mysqli_stmt_close($stmt_update);
        if(isset($stmt_insert)) mysqli_stmt_close($stmt_insert);
        if(isset($conexion)) mysqli_close($conexion);
        echo json_encode($response);
    }
?>