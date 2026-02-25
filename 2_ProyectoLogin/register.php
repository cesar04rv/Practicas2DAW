
<?php
include("code-register.php");
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Document</title>
    <link rel="stylesheet" href="./styles/estilos.css">
</head>

<body>
    <div class="container-all">
        <div class="ctn-form">
            <img src="./img/logo ghub.jfif" alt="" class="logo" />
            <div class="campos">
                <h1 class="title">Registrarse</h1>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <label for="email">Nombre de Usuario</label>
                    <input type="text" id="nombre" name="username"  />
                    <span class="msg-error"><?php echo $username_err; ?></span>
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email"  />
                    <span class="msg-error"><?php echo $email_err; ?></span>
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password"  />
                    <span class="msg-error"><?php echo $password_err; ?></span>

                    <input type="submit" value="Registrarse" />
            </div>
            </form>
            <span class="text-footer">Ya tienes una cuenta?
                <a href="#">Iniciar sesión </a>
            </span>


        </div>
        <div class="ctn-text">
            <div class="capa">
                <h1 class="title-description">Lorem ipsum dolor sit amet.</h1>
                <p class="text-description">
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Natus
                    iste dolores nam velit modi distinctio rerum quae libero debitis,
                    tenetur vitae mollitia repudiandae? Iure quo tempora nihil
                    voluptate totam minus.
                </p>
            </div>
        </div>
    </div>
</body>

</html>