
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroDatShop - Módulo para Monitorear</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>

    </style>
</head>
<body>
    <header>
        <h1 id="titulo">AgroDataShop </h1>
        
        <img src="../img/logo.png" alt="Descripción de la imagen" width="100" height="100">
    </header>

    
    

    <div class="content">
        <h2>Módulo para Monitorear Sensores</h2>
        <p>Aquí puedes gestionar y visualizar datos en tiempo real provenientes de nuestros sensores.</p>
    </div>

    
    
    <?php
        // Incluye tu archivo de conexión
        include '../php/conexion.php'; // o require, según prefieras
        include '../php/mostrar_tabla.php';
    ?>



</body>
</html>
