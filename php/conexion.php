<?php
// Configuración de la conexión a la base de datos MySQL
   $db_host = '127.0.0.1';
  $db_user = 'root';
  $db_password = 'root';
  $db_db = 'temperaturas_db';
  $db_port = 8889;

// Crear la conexión usando mysqli
$conexion = new mysqli($db_host,
    $db_user,
    $db_password,
    $db_db,
	$db_port);

// Verificar si la conexión fue exitosa
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
} else {
   
}




?>
