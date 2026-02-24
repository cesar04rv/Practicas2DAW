<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="//cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <title>Crud con PHP, PDO, AJAX y Datatables.js!</title>
</head>

<body>
    <div class="container fondo">

        <h1 class="text-center">Registro de usuarios con PDO</h1>
        <a target="_blank" href="https://github.com/cesar04rv">
            <h1 class="text-center">github.com/cesar04rv</h1>
        </a>
        <div class="row">
            <div class="col-2 offset-10">
                <div class="text-center">
                    <!-- Button trigger modal -->
                    <button type="button"
                        class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2"
                        data-bs-toggle="modal"
                        data-bs-target="#modalUsuario"
                        id="botonCrear">
                        <i class="bi bi-plus-circle-fill"></i>
                        Crear
                    </button>
                </div>
            </div>
        </div>
        <br><br>

        <div class="table-responsive">
            <table id="datos_usuario" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Telefono</th>
                        <th>Email</th>
                        <th>Imagen</th>
                        <th>Fecha Creación</th>
                        <th>Editar</th>
                        <th>Borrar</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Formulario de ingreso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="POST" id="formulario" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-body">
                            <label for="nombre">Ingrese el nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control">
                            <br>
                            <label for="apellidos">Ingrese los apellidos</label>
                            <input type="text" name="apellidos" id="apellidos" class="form-control">
                            <br>

                            <label for="email">Ingrese el email</label>
                            <input type="text" name="email" id="email" class="form-control">
                            <br>
                            <label for="telefono">Ingrese el telefono</label>
                            <input type="number" name="telefono" id="telefono" class="form-control">
                            <br>
                            <label for="imagen">Selecciona una imagen</label>
                            <input type="file" name="imagen_usuario" id="imagen_usuario" class="form-control">
                            <span id="imagen-subida"></span>
                            <br>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" name="id_usuario" id="id_usuario">
                            <input type="hidden" name="operacion" id="operacion" value="Crear">
                            <input type="submit" name="action" id="action" class=" btn btn-success" value="Crear">
                        </div>
                </form>

            </div>

        </div>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>
    <script type="text/javascript">
        var dataTable;

        // ---------------- Función de validación sin regex ----------------
        function validarFormulario() {
            var nombre = $("#nombre").val().trim();
            var apellidos = $("#apellidos").val().trim();
            var email = $("#email").val().trim();
            var telefono = $("#telefono").val().trim();
            var imagen = $("#imagen_usuario").val().trim();

            function soloLetras(str) {
                for (var i = 0; i < str.length; i++) {
                    var c = str.charAt(i).toLowerCase();
                    if ((c < 'a' || c > 'z') && c != ' ' && c != 'á' && c != 'é' && c != 'í' && c != 'ó' && c != 'ú' && c != 'ñ') {
                        return false;
                    }
                }
                return true;
            }

            if (!soloLetras(nombre)) {
                alert("El nombre solo puede contener letras y espacios");
                return false;
            }
            if (!soloLetras(apellidos)) {
                alert("Los apellidos solo pueden contener letras y espacios");
                return false;
            }

            if (email.indexOf("@") == -1 || email.indexOf(".") == -1 || email.indexOf(" ") != -1) {
                alert("Ingrese un correo electrónico válido");
                return false;
            }

            if (telefono.length != 9 || isNaN(telefono)) {
                alert("El teléfono debe contener exactamente 9 dígitos");
                return false;
            }

            if (imagen != '') {
                var extension = imagen.split('.').pop().toLowerCase();
                var permitidas = ['gif', 'png', 'jpg', 'jpeg'];
                var valida = false;
                for (var i = 0; i < permitidas.length; i++) {
                    if (extension == permitidas[i]) {
                        valida = true;
                        break;
                    }
                }
                if (!valida) {
                    alert("Formato de imagen inválido. Solo GIF, PNG, JPG, JPEG");
                    $("#imagen_usuario").val('');
                    return false;
                }
            }

            return true; // Todo correcto
        }
        // ------------------------------------------------------------------

        $(document).ready(function () {

            $("#botonCrear").click(function () {
                $("#formulario")[0].reset();
                $(".modal-title").text("Crear Usuario");
                $("#action").val("Crear");
                $("#operacion").val("Crear");
                $("#imagen-subida").html("");
            });

            dataTable = $('#datos_usuario').DataTable({
                processing: true,
                serverSide: true,
                order: [],
                ajax: {
                    url: "obtener_registros.php",
                    type: "POST"
                },
                columnDefs: [{
                    targets: [0, 3, 4],
                    orderable: false,
                }]
            });

        });

        // ---------------- Submit con validación ----------------
        $(document).on('submit', '#formulario', function (event) {
            event.preventDefault();

            if (!validarFormulario()) return; // Detener si no pasa validación

            $.ajax({
                url: "crear.php",
                method: "POST",
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function (data) {
                    alert(data);
                    $('#formulario')[0].reset();

                    var modal = bootstrap.Modal.getInstance(document.getElementById('modalUsuario'));
                    modal.hide();

                    dataTable.ajax.reload();
                }
            });
        });

        // ---------------- Editar ----------------
        $(document).on('click', '.editar', function () {
            var id_usuario = $(this).attr("id");

            $.ajax({
                url: "obtener_registro.php",
                method: "POST",
                data: { id_usuario: id_usuario },
                dataType: "json",
                success: function (data) {
                    var modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
                    modal.show();

                    $('#nombre').val(data.nombre);
                    $('#apellidos').val(data.apellidos);
                    $('#telefono').val(data.telefono);
                    $('#email').val(data.email);
                    $('#id_usuario').val(id_usuario);

                    $('.modal-title').text("Editar Usuario");
                    $('#imagen-subida').html(data.imagen_usuario);

                    $('#action').val("Editar");
                    $('#operacion').val("Editar");
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        });

        // ---------------- Borrar ----------------
        $(document).on('click', '.borrar', function () {
            var id_usuario = $(this).attr("id");
            if (confirm("¿Estás seguro de borrar este registro: " + id_usuario + "?")) {
                $.ajax({
                    url: "borrar.php",
                    method: "POST",
                    data: { id_usuario: id_usuario },
                    success: function (data) {
                        alert(data);
                        dataTable.ajax.reload();
                    }
                })
            } else {
                return false;
            }
        });
    </script>
</body>

</html>