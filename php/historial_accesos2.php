<?php
require_once 'conex_db.php';

function formatearHorasTrabajadas($tiempo) {
    if ($tiempo === null || $tiempo === '00:00:00' || $tiempo === '') {
        return 'N/A';
    }
    
    list($horas, $minutos, $segundos) = explode(':', $tiempo);
    $minutosTotales = ($horas * 60) + $minutos + ($segundos / 60);
    $horasDecimal = $minutosTotales / 60;
    $horasFormateadas = floor($horasDecimal);
    $minutosFormateados = round(($horasDecimal - $horasFormateadas) * 60);
    
    return sprintf("%02d:%02d", $horasFormateadas, $minutosFormateados);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de acceso de empleados</title>

    <!-- Vinculos de APIS de fuentes y hojas de estilos -->
    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Vinculos de JS -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="../js/efectos.js" defer></script>
    <script src="../js/main.js" defer></script>

    <style>
        /* Contenedor principal con scroll */
        .formulario-inicio-sesion-admin {
            max-height: 80vh;
            overflow-y: auto;
            padding: 20px;
        }
        
        /* Estilos para las tablas */
        .tabla-contenedor {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .tabla-scroll {
            max-height: 300px;
            overflow-y: auto;
            width: 100%;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            position: sticky;
            top: 0;
            background-color: #4169E1;
            color: white;
            z-index: 10;
        }
        
        /* Estilos para la tabla de invitados */
        .tabla-invitados-container h3 {
            text-align: center;
            margin: 25px 0 15px 0;
            color: #4169E1;
        }
        
        /* Personalización del scrollbar */
        .tabla-scroll::-webkit-scrollbar {
            width: 8px;
        }
        
        .tabla-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .tabla-scroll::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        .tabla-scroll::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>

<body>
    <!-- Fondo de partículas -->
    <div id="particles-js"></div>

    <!-- Formulario para acceder a la plataforma de administrador -->
    <div class="formulario-inicio-sesion-admin fadein-admin">
        <h1>Historial de acceso</h1>

        <!-- Contenedor para volver al apartado de administradores -->
        <div class="volver-index">
            <a href="../html/registro_inicio_sesion.html">Volver a la plataforma de administradores</a>
        </div>
            
        <?php
        $fecha = $_POST['fecha-historial'] ?? '';
        if (!empty($fecha)) {
            echo '<div class="resultados-historial">';
            echo "<h2>Registro de empleados del día: " . htmlspecialchars($fecha) . "</h2>";

            /*** TABLA DE EMPLEADOS ***/
            $consulta_empleados = $conexion->prepare("
                SELECT 
                    empleado_id, 
                    nombre_completo, 
                    hora_entrada,
                    hora_salida,
                    horas_trabajadas 
                FROM historial_accesos 
                WHERE fecha = ?
                ORDER BY hora_entrada ASC
            ");
            $consulta_empleados->bind_param("s", $fecha);
            $consulta_empleados->execute();
            $resultado_empleados = $consulta_empleados->get_result();

            if ($resultado_empleados->num_rows > 0) {
                echo '<div class="tabla-contenedor">';
                echo '<div class="tabla-scroll">';
                echo '<table>
                        <thead>
                            <tr>
                                <th>ID Empleado</th>
                                <th>Nombre</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Horas Trabajadas</th>
                            </tr>
                        </thead>
                        <tbody>';
                
                while ($fila = $resultado_empleados->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($fila['empleado_id']) . "</td>
                            <td>" . htmlspecialchars($fila['nombre_completo']) . "</td>
                            <td>" . date('H:i', strtotime($fila['hora_entrada'])) . "</td>
                            <td>" . (!empty($fila['hora_salida']) ? date('H:i', strtotime($fila['hora_salida'])) : 'N/A') . "</td>
                            <td>" . formatearHorasTrabajadas($fila['horas_trabajadas']) . "</td>
                        </tr>";
                }
                echo '</tbody></table>';
                echo '</div>'; // Cierre de div tabla-scroll
                echo '</div>'; // Cierre de div tabla-contenedor
            } else {
                echo "<p>No hay registros de empleados para esta fecha</p>";
            }

            $consulta_empleados->close();

            /*** TABLA DE INVITADOS ***/
            $consulta_invitados = $conexion->prepare("
                SELECT 
                    nombre, 
                    correo, 
                    hora
                FROM invitados 
                WHERE fecha = ?
                ORDER BY hora ASC
            ");
            $consulta_invitados->bind_param("s", $fecha);
            $consulta_invitados->execute();
            $resultado_invitados = $consulta_invitados->get_result();

            echo '<div class="tabla-invitados-container">';
            echo "<h2>Registro de invitados del día: " . htmlspecialchars($fecha) . "</h2>";
            
            if ($resultado_invitados->num_rows > 0) {
                echo '<div class="tabla-contenedor">';
                echo '<div class="tabla-scroll">';
                echo '<table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Hora de Entrada</th>
                            </tr>
                        </thead>
                        <tbody>';
                
                while ($fila = $resultado_invitados->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($fila['nombre']) . "</td>
                            <td>" . htmlspecialchars($fila['correo']) . "</td>
                            <td>" . formatearHorasTrabajadas($fila['hora']) . "</td>
                        </tr>";
                }
                echo '</tbody></table>';
                echo '</div>'; // Cierre de div tabla-scroll
                echo '</div>'; // Cierre de div tabla-contenedor
            } else {
                echo "<p>No hay registros de invitados para esta fecha</p>";
            }

            $consulta_invitados->close();
            echo '</div>'; // Cierre de div tabla-invitados-container
            echo '</div>'; // Cierre de div resultados-historial
        } else {
            echo '<div class="resultados-historial"><p>Por favor, selecciona una fecha válida</p></div>';
        }
        
        $conexion->close();
        ?>
    </div>
</body>
</html>