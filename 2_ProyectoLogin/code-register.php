<?php
// Incluir archivos necesarios
require_once("conexion.php");
require_once("validaciones.php");

// Inicializar variables
$username = $email = $password = "";
$username_err = $email_err = $password_err = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validar nombre de usuario
    if (!validar_campo($_POST["username"])) {
        $username_err = "El campo nombre de usuario es obligatorio";
    } else if (!validarnombre($_POST["username"])) {
        $username_err = "El nombre de usuario no es válido";
    } else {
        $sql = "SELECT id FROM usuarios WHERE usuario = ?";
        $stmt = mysqli_prepare($link, $sql);
        if ($stmt) {
            $param_username = trim($_POST["username"]);
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $username_err = "El nombre de usuario ya existe";
                } else {
                    $username = $param_username;
                }
            } else {
                echo "Algo salió mal al verificar el usuario, intenta más tarde.";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error en la preparación de la consulta de usuario: " . mysqli_error($link);
        }
    }

    // 2. Validar email
    if (!validar_campo($_POST["email"])) {
        $email_err = "El campo email es obligatorio";
    } else if (!validar_email($_POST["email"])) {
        $email_err = "El email no es válido";
    } else {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($link, $sql);
        if ($stmt) {
            $param_email = trim($_POST["email"]);
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $email_err = "El email ya existe";
                } else {
                    $email = $param_email;
                }
            } else {
                echo "Algo salió mal al verificar el email, intenta más tarde.";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error en la preparación de la consulta de email: " . mysqli_error($link);
        }
    }

    // 3. Validar contraseña
    if (!validar_campo($_POST["password"])) {
        $password_err = "El campo contraseña es obligatorio";
    } else if (!longitud_password($_POST["password"])) {
        $password_err = "La contraseña debe tener al menos 8 caracteres";
    } else {
        $password = trim($_POST["password"]);
    }

    // 4. Insertar en la base de datos si no hay errores
    if (empty($username_err) && empty($email_err) && empty($password_err)) {
        $sql = "INSERT INTO usuarios (usuario, email, clave) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        if ($stmt) {
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $param_password);

            if (mysqli_stmt_execute($stmt)) {
                // Registro exitoso
                header("location: index.php");
                exit;
            } else {
                echo "Algo salió mal al registrar el usuario, intenta más tarde.";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "Error en la preparación de la consulta de inserción: " . mysqli_error($link);
        }
    }
}

// Cerrar conexión
mysqli_close($link);
?>