<?php

$usuario = "root";
$password = "";
$conexion = new PDO ("mysql:host=localhost;dbname=crud_usuarios",
$usuario, $password);
$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
