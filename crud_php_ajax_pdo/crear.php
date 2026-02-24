<?php
include("conexion.php");
include("funciones.php");

// Función de validación del lado servidor
function validarCampos($nombre, $apellidos, $telefono, $email, $imagen_nombre) {
    // Validar nombre y apellidos (solo letras y espacios)
    $solo_letras = function($str) {
        $str = trim($str);
        for ($i = 0; $i < strlen($str); $i++) {
            $c = mb_substr($str, $i, 1);
            if (!ctype_alpha($c) && $c != ' ' && !in_array($c, ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ'])) {
                return false;
            }
        }
        return true;
    };

    if (! $solo_letras($nombre)) {
        die("El nombre solo puede contener letras y espacios");
    }

    if (! $solo_letras($apellidos)) {
        die("Los apellidos solo pueden contener letras y espacios");
    }

    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Correo electrónico inválido");
    }

    // Validar teléfono (9 dígitos)
    if (!ctype_digit($telefono) || strlen($telefono) != 9) {
        die("El teléfono debe contener exactamente 9 dígitos");
    }

    // Validar imagen (si se subió)
    if ($imagen_nombre != '') {
        $extension = strtolower(pathinfo($imagen_nombre, PATHINFO_EXTENSION));
        $permitidas = ['gif', 'png', 'jpg', 'jpeg'];
        if (!in_array($extension, $permitidas)) {
            die("Formato de imagen inválido. Solo GIF, PNG, JPG, JPEG permitidos");
        }
    }
}

// Obtener campos del formulario
$nombre = trim($_POST["nombre"]);
$apellidos = trim($_POST["apellidos"]);
$telefono = trim($_POST["telefono"]);
$email = trim($_POST["email"]);
$imagen = '';

// Si hay imagen subida
if (isset($_FILES["imagen_usuario"]) && $_FILES["imagen_usuario"]["name"] != '') {
    $imagen = $_FILES["imagen_usuario"]["name"];
}

// Validar los datos
validarCampos($nombre, $apellidos, $telefono, $email, $imagen);

// Si operación es Crear
if ($_POST["operacion"] == "Crear") {

    if ($imagen != '') {
        $imagen = subir_imagen();
    }

    $stmt = $conexion->prepare("INSERT INTO usuarios(nombre, apellidos, imagen, telefono, email) VALUES(:nombre, :apellidos, :imagen, :telefono, :email)");
    $resultado = $stmt->execute([
        ':nombre' => $nombre,
        ':apellidos' => $apellidos,
        ':telefono' => $telefono,
        ':email' => $email,
        ':imagen' => $imagen
    ]);

    if ($resultado) {
        echo "Registro creado correctamente";
    }

}

// Si operación es Editar
if ($_POST["operacion"] == "Editar") {

    if (isset($_FILES["imagen_usuario"]) && $_FILES["imagen_usuario"]["name"] != '') {
        $imagen = subir_imagen();
    } else {
        $imagen = $_POST["imagen_usuario_oculta"] ?? '';
    }

    $stmt = $conexion->prepare("
        UPDATE usuarios
        SET nombre = :nombre,
            apellidos = :apellidos,
            imagen = :imagen,
            telefono = :telefono,
            email = :email
        WHERE id = :id
    ");

    $resultado = $stmt->execute([
        ':nombre' => $nombre,
        ':apellidos' => $apellidos,
        ':telefono' => $telefono,
        ':email' => $email,
        ':imagen' => $imagen,
        ':id' => $_POST["id_usuario"]
    ]);

    if ($resultado) {
        echo "Registro actualizado correctamente";
    }
}
?>