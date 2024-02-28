<!--
    Descripción: Este es un CRUD en PHP con AJAX y MySQL realizado como parte de una prueba técnica para MAVI.
                 Actualmente es capaz de realizar las funciones "CRUD" mas la capacidad de tener una paginación
                 y ordenamiento alfanumérico de los clientes mostrados al dar click en las columnas.
    Autor: Carlos Ayala (https://github.com/SrMai)
-->
<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // El usuario no ha iniciado sesión, redirigir a la página de inicio de sesión
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <!-- Normalize V8.0.1 -->
    <link rel="stylesheet" href="https://carlosayala.space/vistas/assets/admin/css/normalize.css">

    <!-- MDBootstrap V5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://carlosayala.space/vistas/assets/admin/css/mdb.min.css">

    <!-- Font Awesome V5.15.1 -->
    <link rel="stylesheet" href="https://carlosayala.space/vistas/assets/admin/css/all.css">
    <script src="https://kit.fontawesome.com/906a7c7e1a.js" crossorigin="anonymous"></script>

    <!-- General Styles -->
    <link rel="stylesheet" href="https://carlosayala.space/vistas/assets/admin/css/style.css">
    <!-- Animación de Try -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@1.5.7/dist/lottie-player.js"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-interactivity@latest/dist/lottie-interactivity.min.js"></script>

</head>

<body>

    <main class="full-box main-container">
        <section class="full-box nav-lateral">
            <div class="full-box nav-lateral-bg"></div>
            <div class="full-box nav-lateral-content scroll">
                <figure class="full-box nav-lateral-avatar">
                    <i class="far fa-times-circle"></i>
                    <img src="https://carlosayala.space/vistas/assets/avatar/Avatar_default_male.png" class="img-fluid"
                        alt="Avatar">
                    <figcaption class="roboto-medium text-center">
                        <?php echo $_SESSION['username']; ?> <br><small
                            class="roboto-condensed-light">Administrador</small>
                    </figcaption>
                </figure>
                <div class="full-box nav-lateral-bar"></div>
                <nav class="full-box nav-lateral-menu">
                    <ul>
                        <li>
                            <a href="#"><i class="fab fa-dashcube fa-fw"></i> &nbsp;
                                PCU</a>
                        </li>

                        <li>
                            <a href="javascript:void(0);" class="nav-btn-submenu"><i class="fas fa-users fa-fw"></i>
                                &nbsp; Clientes <i class="fas fa-chevron-down"></i></a>
                            <ul>
                                <li>
                                    <a href="#" onclick="mostrarFormulario()"><i class="fas fa-plus fa-fw"></i>
                                        &nbsp; Nuevo cliente</a>
                                </li>
                                <li>
                                    <a href="#"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de clientes</a>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <a href="https://www.mavi.mx/"><i class="fas fa-home fa-fw"></i> &nbsp; Sitio web</a>
                        </li>

                        <li>
                            <a href="logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> &nbsp; Cerrar sesión</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </section>
        <section class="full-box page-content scroll">
            <div class="full-box page-header">
                <h3 class="text-start roboto-condensed-regular text-uppercase">
                    <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de clientes
                </h3>
            </div>
            <div class="container-fluid">
                <ul class="nav nav-tabs nav-justified mb-4" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a onclick="mostrarFormulario()" class="nav-link"><i class="fas fa-plus fa-fw"></i>
                            &nbsp; Nuevo cliente</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de
                            clientes</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <input class="nav-link" type="search" name="campo" id="campo" placeholder="Buscar cliente"
                            aria-label="Search">
                    </li>
                </ul>
            </div>
            <div class="container-fluid">
                <div class="dashboard-container">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-dark">
                                <tr class="text-center font-weight-bold">
                                    <th class="sort asc">ID</th>
                                    <th class="sort asc">Nombre</th>
                                    <th class="sort asc">Apellido</th>
                                    <th class="sort asc">Domicilio</th>
                                    <th class="sort asc">Email</th>
                                    <th>Actualizar</th>
                                    <th>Eliminar</th>
                                </tr>
                            </thead>
                            <tbody id="content" class="bg-white">
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <label id="lbl-total"></label>
                        </div>
                        <div class="col-6" id="nav-paginacion"></div>
                        <input type="hidden" id="pagina" value="1">
                        <input type="hidden" id="orderCol" value="0">
                        <input type="hidden" id="orderType" value="asc">
                    </div>
                    <a class="nav-link disabled" style="color:black" href="#" tabindex="-1"
                        aria-disabled="true">Mostrar:</a>
                    <select name="num_registros" id="num_registros" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <a class="nav-link disabled" style="color:black" href="#" tabindex="-1"
                        aria-disabled="true">Registros</a>

                </div>
            </div>

        </section>
    </main>
    <script>
        /**
         *   READ
         */

        getData(); // Obtener datos iniciales al cargar la página

        //Código de escucha para saber cuando el usuario escribe alguna letra en el buscador
        document.getElementById("campo").addEventListener("keyup", function () {
            getData()
        }, false)
        //Revisión para el paginado de los resultados
        document.getElementById("num_registros").addEventListener("change", function () {
            getData()
        }, false)

        function getData() {
            /**
             * Realiza una solicitud AJAX para obtener datos del servidor y actualizar la interfaz de usuario.
             */
            let metodo = "read" // Definir el método de la solicitud (en este caso, 'read' para leer datos)
            // Obtenemos los valores de los elementos del DOM
            let input = document.getElementById("campo").value
            let num_registros = document.getElementById("num_registros").value
            let content = document.getElementById("content")
            let pagina = document.getElementById("pagina").value
            let orderCol = document.getElementById("orderCol").value
            let orderType = document.getElementById("orderType").value

            // Establecemos le paginado si hay pocos datos
            if (pagina == null) {
                pagina = 1
            }

            let url = "controller.php"
            let formaData = new FormData() // Crear un objeto FormData para enviar los datos al servidor
            formaData.append('action', metodo)
            formaData.append('campo', input)
            formaData.append('registros', num_registros)
            formaData.append('pagina', pagina)
            formaData.append('orderCol', orderCol)
            formaData.append('orderType', orderType)
            // Realizar una solicitud fetch para enviar los datos al controlador
            fetch(url, {
                    method: "POST",
                    body: formaData // Pasar el objeto FormData como cuerpo de la solicitud
                }).then(response => response.json()) // Convertir la respuesta del servidor a formato JSON
                .then(data => {
                    // Actualizar el contenido HTML con los datos recibidos del servidor
                    content.innerHTML = data.data
                    document.getElementById("lbl-total").innerHTML = 'Mostrando ' + data.totalFiltro +
                        ' de ' + data.totalRegistros + ' registros'
                    document.getElementById("nav-paginacion").innerHTML = data.paginacion
                }).catch(err => console.log(err)) // Manejar errores si ocurren durante la solicitud
        }
        //Botón para cambiar de pagina
        function nextPage(pagina) {
            document.getElementById('pagina').value = pagina
            getData()
        }

        /**
         *   ORDENACIÓN De DATOS
         */
        let columns = document.getElementsByClassName("sort")
        let tamanio = columns.length
        for (let i = 0; i < tamanio; i++) {
            columns[i].addEventListener("click", ordenar)
        }

        function ordenar(e) {
            /**
             *Ordena las columnas de la tabla según la entrada del usuario.
             *
             *param $e es la columna del evento.
             */
            //Obtiene la columna que fue clickeada
            let elemento = e.target

            document.getElementById('orderCol').value = elemento.cellIndex

            if (elemento.classList.contains("asc")) {
                document.getElementById("orderType").value = "asc"
                elemento.classList.remove("asc")
                elemento.classList.add("desc")
            } else {
                document.getElementById("orderType").value = "desc"
                elemento.classList.remove("desc")
                elemento.classList.add("asc")
            }
            //Solicita un refresh despues de reordenar
            getData()
        }

        /**
         *   DELETE
         */
        function deleteE(employee_id) {
            /**
             * Realiza una solicitud AJAX para eliminar un registro y muestra un cuadro de diálogo de confirmación.
             * param {number} employee_id - El ID del empleado que se va a eliminar.
             */
            // Mostrar un cuadro de diálogo de confirmación utilizando SweetAlert2
            Swal.fire({
                title: '¿Está seguro?',
                text: '¡No podrá revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminarlo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                // Si el usuario confirma la eliminación
                if (result.isConfirmed) {
                    // Crear un objeto FormData para enviar los datos al servidor
                    let formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('employee_id', employee_id);
                    // Realizar una solicitud fetch para enviar los datos al controlador
                    fetch('controller.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(result => {
                            // Mostrar un cuadro de diálogo de éxito
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: result,
                                icon: 'success'
                            }).then(() => {
                                getData(); // Actualizar la tabla después de la eliminación
                            });
                        })
                        .catch(error => { // Manejar errores si ocurren durante la solicitud
                            console.error('Error al eliminar el registro:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Hubo un error al eliminar el registro',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: "Cancelado",
                        text: "Eliminación cancelada.",
                        icon: "error"
                    });
                }
            });
        }
        /**
         *   CREATE
         */
        // Mostrar formulario de creación con SweetAlert2
        function mostrarFormulario() {
            /**
             * Muestra un formulario modal utilizando SweetAlert2 para crear un nuevo registro.
             * Este formulario incluye campos para ingresar los datos del nuevo registro.
             * returns {Promise} - Una promesa que se resuelve cuando el usuario confirma la creación del registro.
             */
            // Mostrar un cuadro de diálogo modal utilizando SweetAlert2
            Swal.fire({
                title: 'Crear Nuevo Registro',
                html: `<form id="formularioRegistro">
                        <div class="mb-3">
                            <label for="CREATE_id_cliente">Número de Cliente:</label>
                            <input type="number" id="CREATE_id_cliente" name="id_cliente" required>
                        </div>
                        <div class="mb-3">
                            <label for="CREATE_email_cliente">Email:</label>
                            <input tyoe="email" id="CREATE_email_cliente" name="email_cliente" required>
                        </div>
                        <div class="mb-3">
                            <label for="CREATE_nombre_cliente">Nombre:</label>
                            <input type="text" id="CREATE_nombre_cliente" name="nombre_cliente" required>
                        </div>
                        <div class="mb-3">
                            <label for="CREATE_apellido_cliente">Apellido:</label>
                            <input type="text" id="CREATE_apellido_cliente" name="apellido_cliente" required>
                        </div>
                        <div class="mb-3">
                            <label for="CREATE_domicilio_cliente">Domicilio:</label>
                            <input tyoe="text" id="CREATE_domicilio_cliente" name="domicilio_cliente" required>
                        </div>
                    </form>`,
                showCancelButton: true,
                confirmButtonText: 'Crear Registro',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    // Recoger los valores ingresados por el usuario
                    let id_cliente = document.getElementById('CREATE_id_cliente').value;
                    let domicilio_cliente = document.getElementById('CREATE_domicilio_cliente').value;
                    let nombre_cliente = document.getElementById('CREATE_nombre_cliente').value;
                    let apellido_cliente = document.getElementById('CREATE_apellido_cliente').value;
                    let email_cliente = document.getElementById('CREATE_email_cliente').value;

                    // Validar que los campos no estén vacíos
                    if (!id_cliente || !domicilio_cliente || !nombre_cliente || !apellido_cliente || !
                        email_cliente) {
                        Swal.showValidationMessage('Por favor complete todos los campos');
                        return false;
                    }
                    // Retornar los datos ingresados por el usuario
                    return {
                        id_cliente,
                        domicilio_cliente,
                        nombre_cliente,
                        apellido_cliente,
                        email_cliente
                    };
                }
            }).then((result) => {
                // Si el usuario hace clic en "Crear"
                if (result.isConfirmed) {
                    // Enviar los datos del formulario al servidor para crear un nuevo registro
                    crear();
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: "Cancelado",
                        text: "Creación de registro cancelada.",
                        icon: "error"
                    });
                }
            });
        }

        function crear() {
            /**
             * Crea un nuevo registro enviando los datos del formulario al servidor utilizando una solicitud AJAX.
             * returns {Promise} - Una promesa que se resuelve cuando la solicitud AJAX se completa correctamente o se rechaza si hay un error.
             */
            return new Promise((resolve, reject) => {
                // Recoger los valores del formulario
                let id_cliente = document.getElementById("CREATE_id_cliente").value;
                let domicilio_cliente = document.getElementById("CREATE_domicilio_cliente").value;
                let nombre_cliente = document.getElementById("CREATE_nombre_cliente").value;
                let apellido_cliente = document.getElementById("CREATE_apellido_cliente").value;
                let email_cliente = document.getElementById("CREATE_email_cliente").value;
                // Crear un objeto con los datos del formulario
                let formData = new FormData();
                formData.append('action', 'create');
                formData.append('id_cliente', id_cliente);
                formData.append('domicilio_cliente', domicilio_cliente);
                formData.append('nombre_cliente', nombre_cliente);
                formData.append('apellido_cliente', apellido_cliente);
                formData.append('email_cliente', email_cliente);
                // Enviar la petición AJAX
                fetch('controller.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(result => {
                        // Verificar el contenido de la respuesta
                        if (result.includes("Error")) {
                            // Si la respuesta contiene la palabra "Error", mostrar mensaje de error
                            Swal.fire('¡Error!', result, 'error');
                        } else {
                            // De lo contrario, mostrar mensaje de éxito
                            Swal.fire('¡Creado!', result, 'success');
                            getData();
                        }
                        // Resolver la promesa con el resultado
                        resolve(result);
                    })
                    .catch(error => {
                        // Rechazar la promesa con el error
                        Swal.fire('¡Error!', 'Hubo un error al crear el registro', 'error');
                        reject(error);
                    });
            });
        }

        /**
         *   UPDATE
         */
        function actualizar(id_cliente) {
            /**
             * Realiza la actualización de un registro utilizando una solicitud AJAX para obtener los datos del registro a editar
             * y otro para enviar los datos actualizados al servidor.
             * param {number} id_cliente - El número del registro que se va a actualizar.
             */
            // Hacer una solicitud AJAX para obtener la información del registro por su Número de Cliente
            fetch('controller.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=obtenerRegistro&id_cliente=' + id_cliente
                })
                .then(response => response.json()) // Convertir la respuesta a JSON
                .then(registro => {
                    // Mostrar un modal con un formulario prellenado con los datos del registro para su edición
                    Swal.fire({
                        title: 'Editar Registro',
                        html: // Contenido HTML del modal con el formulario prellenado
                            `<form id="formularioRegistro">
                            <div>
                                <label for="id_cliente">Número de Cliente:</label>
                                <input type="number" id="id_cliente" name="id_cliente" value="${registro.id_cliente}" disabled>
                            </div>
                            <div>
                                <label for="email_cliente">Email:</label>
                                <input tyoe="email" id="email_cliente" name="email_cliente" value="${registro.email_cliente}" required>
                            </div>
                            <div>
                                <label for="nombre_cliente">Nombre:</label>
                                <input type="text" id="nombre_cliente" name="nombre_cliente" value="${registro.nombre_cliente}" required>
                            </div>
                            <div>
                                <label for="apellido_cliente">Apellido:</label>
                                <input type="text" id="apellido_cliente" name="apellido_cliente" value="${registro.apellido_cliente}" required>
                            </div>
                            <div>
                                <label for="domicilio_cliente">Dirección:</label>
                                <input tyoe="text" id="domicilio_cliente" name="domicilio_cliente" value="${registro.domicilio_cliente}" required>
                            </div>
                        </form>`,
                        showCancelButton: true,
                        confirmButtonText: 'Actualizar',
                        cancelButtonText: 'Cancelar',
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            // Recoger los valores actualizados del formulario
                            let id_cliente = document.getElementById("id_cliente").value;
                            let domicilio_cliente = document.getElementById("domicilio_cliente").value;
                            let nombre_cliente = document.getElementById("nombre_cliente").value;
                            let apellido_cliente = document.getElementById("apellido_cliente").value;
                            let email_cliente = document.getElementById("email_cliente").value;
                            // Enviar una solicitud AJAX para actualizar los datos del registro
                            return fetch('controller.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: `action=update&id_cliente=${id_cliente}&domicilio_cliente=${domicilio_cliente}&nombre_cliente=${nombre_cliente}&apellido_cliente=${apellido_cliente}&email_cliente=${email_cliente}`
                                })
                                .then(response => response.text())
                                .then(result => {
                                    // Mostrar un mensaje de éxito o error según la respuesta del servidor
                                    if (result.includes("Error")) {
                                        Swal.fire('¡Error!', result, 'error');
                                        getData();
                                    } else {
                                        getData();
                                        Swal.fire('¡Actualizado!', result, 'success');
                                    }
                                })
                                .catch(error => { // Mostrar un mensaje de error si hay un problema con la solicitud
                                    Swal.fire('¡Error!', 'Hubo un error al actualizar el registro',
                                        'error');
                                });
                        }
                    });
                })
                .catch(error => { // Mostrar un mensaje de error si no se puede obtener la información del registro
                    Swal.fire('¡Error!', 'Hubo un error al obtener la información del registro', 'error');
                });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
    </script>

    <!--=============================================
=            Include JavaScript files           =
==============================================-->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
    </script>

    <!-- MDBootstrap V5 -->
    <script src="https://carlosayala.space/vistas/assets/admin/js/mdb.min.js"></script>

    <!-- Ajax JS -->
    <script src="https://carlosayala.space/vistas/assets/admin/js/ajax.js"></script>

    <!-- General scripts -->
    <script src="https://carlosayala.space/vistas/assets/admin/js/main.js"></script>
</body>

</html>
