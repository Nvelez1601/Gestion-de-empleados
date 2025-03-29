<?php
    require_once 'conex_db.php';

    // Función para convertir decimal a formato horas:minutos
    function formatearHorasTrabajadas($horasDecimal) {
        if ($horasDecimal === null) return 'N/A';
        $horas = floor($horasDecimal);
        $minutos = round(($horasDecimal - $horas) * 60);
        return sprintf("%02d:%02d", $horas, $minutos);
    }

    // Validar y obtener fecha
    $fecha = $_POST['fecha-historial'] ?? '';
    if (empty($fecha)) {
        die("Por favor selecciona una fecha válida");
    }

    // Consulta optimizada
    $consulta = $conexion->prepare("
        SELECT 
            empleado_id, 
            nombre_completo, 
            TIME_FORMAT(hora_entrada, '%H:%i') as entrada,
            TIME_FORMAT(hora_salida, '%H:%i') as salida,
            horas_trabajadas 
        FROM historial_accesos 
        WHERE fecha = ?
        ORDER BY hora_entrada ASC
    ");
    $consulta->bind_param("s", $fecha);
    $consulta->execute();
    $resultado = $consulta->get_result();

    // Encabezado HTML para estilos
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Historial de Accesos</title>
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
                margin: 20px 0;
                font-family: Poppins, sans-serif;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 12px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            .volver-btn {
                display: inline-block;
                padding: 10px 20px;
                background-color: #4CAF50;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>';

    echo "<h3>Registros del día: " . htmlspecialchars($fecha) . "</h3>";

    if ($resultado->num_rows > 0) {
        echo '<table>
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
                    <td>" . htmlspecialchars($fila['entrada']) . "</td>
                    <td>" . ($fila['salida'] ?? 'N/A') . "</td>
                    <td>" . formatearHorasTrabajadas($fila['horas_trabajadas']) . "</td>
                </tr>";
        }
        echo '</table>';
    } else {
        echo "<p>No hay registros para esta fecha</p>";
    }

    echo '<a href="../html/registro_inicio_sesion.html" class="volver-btn">Volver al sistema</a>
    </body>
    </html>';

    $consulta->close();
    $conexion->close();
?>