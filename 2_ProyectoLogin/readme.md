Sistema de Login en PHP con HTML5 y CSS3

Este proyecto es un sistema básico de autenticación que permite a los usuarios registrarse, iniciar sesión y cerrar sesión. Está desarrollado en PHP utilizando MySQLi para la conexión a la base de datos, con una maquetación sencilla en HTML5 y estilos aplicados con CSS3.

El entorno de desarrollo utilizado es XAMPP, que incluye Apache y MySQL, facilitando la ejecución local del proyecto.

---

Estructura del Proyecto

/img
    ├── img1.jpg
    └── logo ghub.jfif

/styles
    └── estilos.css

- bienvenida.php
- code-login.php
- code-register.php
- conexion.php
- datos.php
- index.php
- info.php
- logout.php
- register.php
- validaciones.php

---

Descripción de Archivos Principales

- index.php  
  Página principal con el formulario de inicio de sesión. Incluye la lógica de validación y autenticación llamando a code-login.php.

- register.php  
  Página con el formulario de registro de usuario. Utiliza code-register.php para validar y registrar nuevos usuarios.

- bienvenida.php  
  Página de bienvenida para usuarios autenticados. Muestra un mensaje y un botón para cerrar sesión de forma segura con token CSRF.

- logout.php  
  Controlador para cerrar sesión. Verifica el token CSRF y destruye la sesión antes de redirigir al login.

- code-login.php  
  Lógica para validar y procesar el inicio de sesión. Valida campos, verifica credenciales y crea sesión con token CSRF.

- code-register.php  
  Lógica para validar y procesar el registro. Valida campos, verifica unicidad de usuario y email, y guarda usuario en base de datos.

- conexion.php  
  Establece la conexión con la base de datos MySQL usando la extensión MySQLi y las credenciales definidas en datos.php.

- datos.php  
  Archivo de configuración que define las constantes de conexión a la base de datos.

- validaciones.php  
  Funciones para validar campos del formulario, emails, contraseñas y otros datos de entrada.

- styles/estilos.css  
  Archivo CSS con los estilos para los formularios y la interfaz del sistema.

- img/  
  Carpeta con imágenes usadas en la interfaz (logo y otras).

---

Funcionalidades Clave

- Registro de usuario con validaciones de:  
  - Nombre de usuario (solo letras).  
  - Email con formato válido.  
  - Contraseña con mínimo 8 caracteres.  
  - Verificación de usuarios y emails únicos en la base de datos.

- Inicio de sesión con:  
  - Validación de email y contraseña.  
  - Hashing seguro de contraseñas con password_hash y verificación con password_verify.  
  - Manejo de sesiones PHP y generación de token CSRF para seguridad.

- Página de bienvenida accesible solo tras autenticación exitosa.

- Cierre de sesión seguro que valida token CSRF para evitar ataques.

---

Base de Datos

La base de datos usada es MySQL y debe contener una tabla llamada usuarios con al menos las siguientes columnas:

Campo    | Tipo          | Descripción                
---------|---------------|---------------------------
id       | INT (PK, AI)  | Identificador único         
usuario  | VARCHAR(255)  | Nombre de usuario           
email    | VARCHAR(255)  | Correo electrónico          
clave    | VARCHAR(255)  | Contraseña hasheada         

---

Requisitos y Configuración

- Entorno de desarrollo local con XAMPP (Apache y MySQL).  
- PHP versión 7 o superior recomendada.  
- Modificar el archivo datos.php para ajustar las credenciales de la base de datos:

  define('DB_SERVER', 'localhost');
  define('DB_USERNAME', 'root');
  define('DB_PASSWORD', '');
  define('DB_NAME', 'login_tuto');

- Crear o importar la base de datos login_tuto y la tabla usuarios con los campos indicados.

---

Uso

1. Acceder a index.php para iniciar sesión.  
2. Si no tienes cuenta, ir a register.php para registrarse.  
3. Al iniciar sesión correctamente, serás redirigido a bienvenida.php.  
4. Desde bienvenida.php puedes cerrar sesión con el botón, que llama a logout.php.

---

Seguridad

- Uso de tokens CSRF para evitar ataques en formularios sensibles (logout).  
- Validación de entrada para evitar campos vacíos y formatos incorrectos.  
- Hashing seguro de contraseñas con password_hash.  
- Preparación de consultas SQL con sentencias preparadas (MySQLi) para evitar inyección SQL.


