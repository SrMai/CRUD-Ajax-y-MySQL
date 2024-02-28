<!--
    Descripción: Este es un CRUD en PHP con AJAX y MySQL realizado como parte de una prueba técnica para MAVI.
                 Actualmente es capaz de realizar las funciones "CRUD" mas la capacidad de tener una paginación
                 y ordenamiento alfanumérico de los registros mostrados al dar click en las columnas.
    Autor: Carlos Ayala (https://github.com/SrMai)
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
</head>
<body>
    <main>
        <div class="container py-4 text-center">
            <h2>Registros CRUD</h2>

            <div class="row g-4">

                <div class="col-auto">
                    <label for="num_registros" class="col-form-label">Mostrar: </label>
                </div>

                <div class="col-auto">
                    <select name="num_registros" id="num_registros" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="col-auto">
                    <label for="num_registros" class="col-form-label">registros </label>
                </div>

                <div class="col-5"></div>

                <div class="col-auto">
                    <label for="campo" class="col-form-label">Buscar: </label>
                </div>
                <div class="col-auto">
                    <input type="text" name="campo" id="campo" class="form-control">
                </div>
                <button onclick="mostrarFormulario()">Crear Nuevo Registro</button>
            </div>

            <div class="row py-4">
                <div class="col">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <th class="sort asc">Num. registro</th>
                            <th class="sort asc">Nombre</th>
                            <th class="sort asc">Apellido</th>
                            <th class="sort asc">Fecha nacimiento</th>
                            <th class="sort asc">Fecha ingreso</th>
                            <th></th>
                            <th></th>
                        </thead>

                        <tbody id="content">

                        </tbody>
                    </table>
                </div>
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
        </div>
    </main>

    <script>
        /**
        *   READ
        */

        getData(); // Obtener datos iniciales al cargar la página

        //Código de escucha para saber cuando el usuario escribe alguna letra en el buscador
        document.getElementById("campo").addEventListener("keyup", function() {
            getData()
        }, false)
        //Revisión para el paginado de los resultados
        document.getElementById("num_registros").addEventListener("change", function() {
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
                }).catch(err => console.log(err))// Manejar errores si ocurren durante la solicitud
        }
        //Botón para cambiar de pagina
        function nextPage(pagina){
            document.getElementById('pagina').value = pagina
            getData()
        }

        /**
        *   ORDENACIÓN De DATOS
        */
        let columns = document.getElementsByClassName("sort")
        let tamanio = columns.length
        for(let i = 0; i < tamanio; i++){
            columns[i].addEventListener("click", ordenar)
        }

        function ordenar(e){
            /**
             *Ordena las columnas de la tabla según la entrada del usuario.
             *
             *param $e es la columna del evento.
             */
            //Obtiene la columna que fue clickeada
            let elemento = e.target

            document.getElementById('orderCol').value = elemento.cellIndex

            if(elemento.classList.contains("asc")){
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
                html:
                    `<form id="formularioRegistro">
                        <div>
                            <label for="CREATE_no_emp">Número de Registro:</label>
                            <input type="number" id="CREATE_no_emp" name="no_emp" required>
                        </div>
                        <div>
                            <label for="CREATE_fecha_nacimiento">Fecha de Nacimiento:</label>
                            <input type="date" id="CREATE_fecha_nacimiento" name="fecha_nacimiento" required>
                        </div>
                        <div>
                            <label for="CREATE_nombre">Nombre:</label>
                            <input type="text" id="CREATE_nombre" name="nombre" required>
                        </div>
                        <div>
                            <label for="CREATE_apellido">Apellido:</label>
                            <input type="text" id="CREATE_apellido" name="apellido" required>
                        </div>
                        <div>
                            <label for="CREATE_genero">Género:</label>
                            <select id="CREATE_genero" name="genero" required>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                        <div>
                            <label for="CREATE_fecha_ingreso">Fecha de Ingreso:</label>
                            <input type="date" id="CREATE_fecha_ingreso" name="fecha_ingreso" required>
                        </div>
                    </form>`,
                showCancelButton: true,
                confirmButtonText: 'Crear Registro',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    // Recoger los valores ingresados por el usuario
                    let no_emp = document.getElementById('CREATE_no_emp').value;
                    let fecha_nacimiento = document.getElementById('CREATE_fecha_nacimiento').value;
                    let nombre = document.getElementById('CREATE_nombre').value;
                    let apellido = document.getElementById('CREATE_apellido').value;
                    let genero = document.getElementById('CREATE_genero').value;
                    let fecha_ingreso = document.getElementById('CREATE_fecha_ingreso').value;

                    // Validar que los campos no estén vacíos
                    if (!no_emp || !fecha_nacimiento || !nombre || !apellido || !genero || !fecha_ingreso) {
                        Swal.showValidationMessage('Por favor complete todos los campos');
                        return false;
                    }
                    // Retornar los datos ingresados por el usuario
                    return { no_emp, fecha_nacimiento, nombre, apellido, genero, fecha_ingreso };
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
                let no_emp = document.getElementById("CREATE_no_emp").value;
                let fecha_nacimiento = document.getElementById("CREATE_fecha_nacimiento").value;
                let nombre = document.getElementById("CREATE_nombre").value;
                let apellido = document.getElementById("CREATE_apellido").value;
                let genero = document.getElementById("CREATE_genero").value;
                let fecha_ingreso = document.getElementById("CREATE_fecha_ingreso").value;
                // Crear un objeto con los datos del formulario
                let formData = new FormData();
                formData.append('action', 'create');
                formData.append('no_emp', no_emp);
                formData.append('fecha_nacimiento', fecha_nacimiento);
                formData.append('nombre', nombre);
                formData.append('apellido', apellido);
                formData.append('genero', genero);
                formData.append('fecha_ingreso', fecha_ingreso);
                // Enviar la petición AJAX
                fetch('controller.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                    Swal.fire('!Creado!', result, 'success');
                    getData();
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
        function actualizar(no_emp) {
        /**
         * Realiza la actualización de un registro utilizando una solicitud AJAX para obtener los datos del registro a editar
         * y otro para enviar los datos actualizados al servidor.
         * param {number} no_emp - El número del registro que se va a actualizar.
         */
            // Hacer una solicitud AJAX para obtener la información del registro por su número de registro
            fetch('controller.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=obtenerRegistro&no_emp=' + no_emp
            })
            .then(response => response.json())// Convertir la respuesta a JSON
            .then(registro => {
                // Mostrar un modal con un formulario prellenado con los datos del registro para su edición
                Swal.fire({
                    title: 'Editar Registro',
                    html: // Contenido HTML del modal con el formulario prellenado
                        `<form id="formularioRegistro">
                            <div>
                                <label for="no_emp">Número de Registro:</label>
                                <input type="number" id="no_emp" name="no_emp" value="${registro.no_emp}" required>
                            </div>
                            <div>
                                <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="${registro.fecha_nacimiento}" required>
                            </div>
                            <div>
                                <label for="nombre">Nombre:</label>
                                <input type="text" id="nombre" name="nombre" value="${registro.nombre}" required>
                            </div>
                            <div>
                                <label for="apellido">Apellido:</label>
                                <input type="text" id="apellido" name="apellido" value="${registro.apellido}" required>
                            </div>
                            <div>
                                <label for="genero">Género:</label>
                                <select id="genero" name="genero" required>
                                    <option value="M" ${registro.genero === 'M' ? 'selected' : ''}>Masculino</option>
                                    <option value="F" ${registro.genero === 'F' ? 'selected' : ''}>Femenino</option>
                                </select>
                            </div>
                            <div>
                                <label for="fecha_ingreso">Fecha de Ingreso:</label>
                                <input type="date" id="fecha_ingreso" name="fecha_ingreso" value="${registro.fecha_ingreso}" required>
                            </div>
                        </form>`,
                    showCancelButton: true,
                    confirmButtonText: 'Actualizar',
                    cancelButtonText: 'Cancelar',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        // Recoger los valores actualizados del formulario
                        let no_emp = document.getElementById("no_emp").value;
                        let fecha_nacimiento = document.getElementById("fecha_nacimiento").value;
                        let nombre = document.getElementById("nombre").value;
                        let apellido = document.getElementById("apellido").value;
                        let genero = document.getElementById("genero").value;
                        let fecha_ingreso = document.getElementById("fecha_ingreso").value;
                        // Enviar una solicitud AJAX para actualizar los datos del registro
                        return fetch('controller.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=update&no_emp=${no_emp}&fecha_nacimiento=${fecha_nacimiento}&nombre=${nombre}&apellido=${apellido}&genero=${genero}&fecha_ingreso=${fecha_ingreso}`
                        })
                        .then(response => response.text())
                        .then(result => {
                            // Mostrar un mensaje de éxito o error según la respuesta del servidor
                            if (result.includes("ito")) {
                                getData();
                                Swal.fire('¡Actualizado!', result, 'success');
                            } else {
                                getData();
                                Swal.fire('¡Error!', result, 'error');
                            }
                        })
                        .catch(error => {// Mostrar un mensaje de error si hay un problema con la solicitud
                            Swal.fire('¡Error!', 'Hubo un error al actualizar el registro', 'error');
                        });
                    }
                });
            })
            .catch(error => { // Mostrar un mensaje de error si no se puede obtener la información del registro
                Swal.fire('¡Error!', 'Hubo un error al obtener la información del registro', 'error');
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

</body>

</html>