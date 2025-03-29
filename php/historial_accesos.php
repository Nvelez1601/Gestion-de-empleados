<?php
// Conexión a la base de datos (ajusta estos valores)
    require_once 'conex_db.php';

    // Validar y obtener fecha
    $fecha = $_POST['fecha-historial'] ?? '';
    if (empty($fecha)) {
        die("Por favor selecciona una fecha válida");
    }

    // Consulta corregida
    $consulta = $conexion->prepare("
        SELECT empleado_id, nombre_completo, hora_entrada, hora_salida, horas_trabajadas 
        FROM historial_accesos 
        WHERE fecha = ?
    ");
    $consulta->bind_param("s", $fecha);
    $consulta->execute();
    $resultado = $consulta->get_result();

    // Mostrar resultados
    echo "<h3>Registros del día: " . htmlspecialchars($fecha) . "</h3>";

    if ($resultado->num_rows > 0) {
        echo '<table border="1">
                <tr>
                    <th>ID Empleado</th>
                    <th>Nombre</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Horas Trabajadas</th>
                </tr>';
        
        while ($fila = $resultado->fetch_assoc()) {
                echo "<tr>
                <td>" . htmlspecialchars($fila['empleado_id']) . "</td>
                <td>" . htmlspecialchars($fila['nombre_completo']) . "</td>
                <td>" . htmlspecialchars($fila['hora_entrada']) . "</td>
                <td>" . ($fila['hora_salida'] ?? 'N/A') . "</td>
                <td>" . number_format($fila['horas_trabajadas'], 2, ':', '') . " hrs</td>
                </tr>";

            // Añade esta función al inicio del archivo:
            function formatearHorasTrabajadas($hora) {
                if (!$hora) return '0:00';
                $horas = explode(':', $hora);
                return $horas[0] . ':' . $horas[1]; // Elimina los segundos
            }
        }
        echo '</table>';
    } else {
        echo "<p>No hay registros para esta fecha</p>";
    }

    $consulta->close();
    $conexion->close();
?>