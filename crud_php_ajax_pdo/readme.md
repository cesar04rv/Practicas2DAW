
CRUD de Usuarios con PHP, PDO, AJAX y DataTables.js

Este proyecto es un sistema de registro de usuarios con funcionalidad CRUD (Crear, Leer, Actualizar y Borrar) utilizando PHP, PDO para la conexión a base de datos MySQL, AJAX para operaciones sin recargar la página y DataTables.js para la visualización y gestión de los registros.

------------------------------------------------------------
Tecnologías utilizadas

- PHP (versión 8.0)
- XAMP 7.3.29
- MySQL
- PDO para la conexión segura a la base de datos
- AJAX (jQuery) para enviar y recibir datos sin recargar
- DataTables.js para tablas interactivas
- Bootstrap 5 para diseño y modales
- Bootstrap Icons para íconos de acción
- Carpeta CSS para estilos personalizados (estilos.css)
- Carpeta img para almacenar imágenes de los usuarios

------------------------------------------------------------
Estructura de archivos

/
├── css/
│   └── estilos.css
├── img/
├── borrar.php
├── conexion.php
├── crear.php
├── funciones.php
├── index.php
├── obtener_registro.php
└── obtener_registros.php

------------------------------------------------------------
Configuración de la base de datos

1. Crear una base de datos en MySQL llamada 'crud_usuarios':

CREATE DATABASE crud_usuarios;

2. Crear la tabla 'usuarios' con la siguiente estructura:

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(50) NOT NULL,
    imagen VARCHAR(100) DEFAULT NULL,
    telefono VARCHAR(15),
    email VARCHAR(100) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

3. Actualiza los datos de conexión en conexion.php si tu usuario o contraseña de MySQL son distintos a los del ejemplo:

$usuario = "root";
$password = "";
$conexion = new PDO("mysql:host=localhost;dbname=crud_usuarios", $usuario, $password);
$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

------------------------------------------------------------
Funcionamiento

- Crear Usuario:  
  Al hacer clic en el botón "Crear" se abre un modal donde puedes ingresar nombre, apellidos, teléfono, email y seleccionar una imagen.  
  Se valida que:
  - Nombre y apellidos solo contengan letras y espacios.
  - Email tenga formato básico válido.
  - Teléfono tenga exactamente 9 dígitos.
  - Imagen sea formato GIF, PNG, JPG o JPEG.

- Editar Usuario:  
  Cada fila de la tabla tiene un botón "Editar" que carga los datos del usuario en el modal para su actualización.

- Borrar Usuario:  
  Cada fila tiene un botón "Borrar" que elimina el registro de la base de datos y la imagen asociada del servidor.

- DataTables.js:  
  - Permite búsqueda en tiempo real por nombre o apellidos.
  - Ordenamiento y paginación automática.
  - La tabla se actualiza automáticamente tras crear, editar o borrar un usuario sin recargar la página.

------------------------------------------------------------
### Ejemplo visual
![Demo del CRUD](assets/PDO.gif)
Validaciones

- Validaciones, tanto del lado del cliente, como del del servidor.
- Nombre y apellidos: solo letras y espacios.
- Email: debe contener '@' y '.' y no contener espacios.
- Teléfono: exactamente 9 dígitos.
- Imagen: solo formatos permitidos (GIF, PNG, JPG, JPEG).

Si alguna validación falla, se muestra un alert y no se envía el formulario.

------------------------------------------------------------
Cómo usarlo

1. Colocar todos los archivos en tu servidor XAMP .  
2. Crear la base de datos y tabla según las instrucciones anteriores.  
3. Abrir index.php en tu navegador.  
4. Administrar usuarios mediante la interfaz: crear, editar y borrar.  

------------------------------------------------------------
Observaciones

- Las imágenes se guardan en la carpeta img/.  
- Las funciones de subida, edición y eliminación de imágenes se encuentran en funciones.php.  
- La interacción con la base de datos se realiza mediante PDO para mayor seguridad y manejo de errores.  
- La tabla principal utiliza AJAX y DataTables.js para no recargar la página al realizar operaciones.  

------------------------------------------------------------
Autor

César Rodríguez
