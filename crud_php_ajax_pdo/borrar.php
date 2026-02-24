<?php
include("conexion.php");
include("funciones.php");

if (isset($_POST['id_usuario'])) {
    $id_usuario = $_POST['id_usuario'];

    // Validar que sea un entero positivo
    if (!ctype_digit($id_usuario) || intval($id_usuario) <= 0) {
        die("ID de usuario inválido");
    }

    // Verificar que el usuario existe
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuario no encontrado");
    }

    // Borrar imagen si existe
    if (!empty($usuario['imagen'])) {
        $ruta_imagen = 'img/' . $usuario['imagen'];
        if (file_exists($ruta_imagen)) {
            unlink($ruta_imagen);
        }
    }

    // Borrar registro
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = :id");
    $resultado = $stmt->execute([':id' => $id_usuario]);

    if ($resultado) {
        echo "Registro eliminado correctamente";
    } else {
        echo "Error al eliminar el registro";
    }

} else {
    echo "No se recibió el ID del usuario";
}
?>