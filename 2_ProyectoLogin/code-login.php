<?php
include("conexion.php");
include("validaciones.php");


$username = $email = $password = "";
$username_err = $email_err = $password_err = "";

//comprobar que los campos pasados por el formulario no esten vacios
if ($_SERVER["REQUEST_METHOD"] == "POST") {


    if (!validar_campo($_POST["email"])) {
        $email_err = "El campo email es obligatorio";
    } else if (!validar_email($_POST["email"])) {
        $email_err = "El email no es válido";
    } else {
        $email = trim($_POST["email"]);
    }

    if (!validar_campo($_POST["password"])) {
        $password_err = "El campo contraseña es obligatorio";
    } else if (!longitud_password($_POST["password"])) {
        $password_err = "La contraseña debe tener al menos 5 caracteres";
    } else {
        $password = trim($_POST["password"]);
    }
}
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
//en caso de que no haya errores, se puede proceder a validar el usuario en la base de datos
if (isset($password) && isset($email)) {
    $stmt = mysqli_prepare($link, "SELECT id, usuario, email, clave FROM usuarios WHERE email = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $param_email);
        $param_email = $email;

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $username, $email, $hashed_password);
                if (mysqli_stmt_fetch($stmt)) {
                    if (password_verify($password, $hashed_password)) {
                        // La contraseña es correcta, iniciar sesión
                        session_start();
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;

                        // Generar token CSRF
                        if (empty($_SESSION['csrf_token'])) {
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        }
                        header("location: bienvenida.php");
                        exit();
                    } else {
                        // La contraseña no es válida
                        $password_err = "La contraseña que has ingresado no es válida.";
                    }
                }
            } else {
                // El email no existe
                $email_err = "No se encontró una cuenta con ese email.";
            }
        } else {
            echo "Algo salió mal al ejecutar la consulta. Por favor, intenta de nuevo más tarde.";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error en la preparación de la consulta: " . mysqli_error($link);
    }
}
