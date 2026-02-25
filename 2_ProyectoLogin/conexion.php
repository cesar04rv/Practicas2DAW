<?php
require_once("datos.php");

//Conexion con mysqli_connect

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if (!$link) {
    die("ERROR: No se ha podido conectar con la base de datos. " . mysqli_connect_error());
}

?>