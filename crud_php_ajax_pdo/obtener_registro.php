<?php

include ("conexion.php");
include("funciones.php");

if (isset($_POST["id_usuario"])){
    $salida = array();
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = '".$_POST["id_usuario"]."'");
}
$stmt-> execute ();
$resultado =$stmt->fetchAll();
foreach ($resultado as $fila){
    $salida["nombre"] = $fila["nombre"];
    $salida["apellidos"] = $fila["apellidos"];
    $salida["telefono"] = $fila["telefono"];
    $salida["email"] = $fila["email"];

    if ($fila["imagen"] != ""){
        $salida["imagen_usuario"] = '<img src="img/' . $fila["imagen"] . '"
        class="img-thumbnail width="50" height=35 /><inpput type="hidden"
        name ="imagen_usuario_oculta" value= '.$fila["imagen"].'"';
    }else{
        $salida["imagen_usuario"] = '<inpput type="hidden"
        name ="imagen_usuario_oculta" value= '.$fila["imagen"].'"';
    }
    echo json_encode($salida);
    }