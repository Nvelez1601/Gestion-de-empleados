<?php
require_once 'vendor/autoload.php'; // Necesitarás instalar la librería endroid/qr-code

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Conectar a la base de datos
include("conex_db.php");

// Obtener el ID del empleado (puede ser pasado por GET o POST)
if (isset($_GET['id'])) {
    $id_empleado = $_GET['id'];
    
    // Verificar que el ID existe en la base de datos
    $consulta = "SELECT id FROM login_db WHERE id = '$id_empleado'";
    $resultado = mysqli_query($conexion, $consulta);
    
    if (mysqli_num_rows($resultado) > 0) {
        // Crear el código QR
        $qrCode = new QrCode($id_empleado);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        // Mostrar el QR directamente en el navegador
        header('Content-Type: '.$result->getMimeType());
        echo $result->getString();
        
        // Opcional: guardar el QR en un archivo
        $result->saveToFile(__DIR__.'/qrcodes/'.$id_empleado.'.png');
        
    } else {
        echo "ID de empleado no encontrado";
    }
} else {
    echo "No se proporcionó ID de empleado";
}
?>