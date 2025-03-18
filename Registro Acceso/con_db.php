<?php

// conxion con la base de datos
$conexion = mysqli_connect("localhost", "root", "", "acceso_db");

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
