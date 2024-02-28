<?php
require 'config.php';

function obtenerRegistro($no_emp) {
/**
 * Obtiene la información de un registro de la base de datos según su número de registro.
 * param int $no_emp - El número del registro que se va a obtener.
 */
    global $conn;
    // Verificar si se recibió el número de registro
    if (isset($no_emp)) {
        // Consulta SQL para obtener la información del registro
        $sql = "SELECT * FROM registros WHERE no_emp = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $no_emp);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $registro = $result->fetch_assoc();
            echo json_encode($registro);
        } else {
            echo json_encode(array("error" => "No se encontró ningún registro con el número de registro proporcionado"));
        }
        // Cerrar la conexión y liberar los recursos
        $stmt->close();
        $conn->close();
    } else {
        // Si no se proporciona el número de registro, devolver un mensaje de error
        echo json_encode(array("error" => "No se proporcionó el número de registro"));
    }
}

function create() {
/**
 * Función para crear un nuevo registro.
 */
    global $conn;
    // Verificar si se recibieron datos del formulario de creación de registros
    if(isset($_POST['no_emp'], $_POST['fecha_nacimiento'], $_POST['nombre'], $_POST['apellido'], $_POST['genero'], $_POST['fecha_ingreso'])) {
        $no_emp = $conn->real_escape_string($_POST['no_emp']);
        $fecha_nacimiento = $conn->real_escape_string($_POST['fecha_nacimiento']);
        $nombre = $conn->real_escape_string($_POST['nombre']);
        $apellido = $conn->real_escape_string($_POST['apellido']);
        $genero = $conn->real_escape_string($_POST['genero']);
        $fecha_ingreso = $conn->real_escape_string($_POST['fecha_ingreso']);
        // Consulta para insertar el nuevo registro en la base de datos
        $sql = "INSERT INTO registros (no_emp, fecha_nacimiento, nombre, apellido, genero, fecha_ingreso)
                VALUES ('$no_emp', '$fecha_nacimiento', '$nombre', '$apellido', '$genero', '$fecha_ingreso')";
        if ($conn->query($sql) === TRUE) {
            echo "Registro creado exitosamente";
        } else {
            echo "Error al crear el registro: " . $conn->error;
        }
    } else {
        echo "No se recibieron datos del formulario";
    }
}

function read() {
/**
 * Función para realizar la lectura de registros con filtrado, paginación y ordenamiento.
 */
    global $conn;
    /* Un arreglo de las columnas a mostrar en la tabla */
    $columns = ['no_emp', 'nombre', 'apellido', 'fecha_nacimiento', 'fecha_ingreso'];

    /* Nombre de la tabla */
    $table = "registros";

    $id = 'no_emp';

    $campo = isset($_POST['campo']) ? $conn->real_escape_string($_POST['campo']) : null;


    /* Filtrado */
    $where = '';

    if ($campo != null) {
        $where = "WHERE (";

        $cont = count($columns);
        for ($i = 0; $i < $cont; $i++) {
            $where .= $columns[$i] . " LIKE '%" . $campo . "%' OR ";
        }
        $where = substr_replace($where, "", -3);
        $where .= ")";
    }

    /* Limit */
    $limit = isset($_POST['registros']) ? $conn->real_escape_string($_POST['registros']) : 10;
    $pagina = isset($_POST['pagina']) ? $conn->real_escape_string($_POST['pagina']) : 0;

    if (!$pagina) {
        $inicio = 0;
        $pagina = 1;
    } else {
        $inicio = ($pagina - 1) * $limit;
    }

    // Construcción de la cláusula LIMIT
    $sLimit = "LIMIT $inicio , $limit";

    /**
     * Ordenamiento
     */

     $sOrder = "";
     if(isset($_POST['orderCol'])){
        $orderCol = $_POST['orderCol'];
        $oderType = isset($_POST['orderType']) ? $_POST['orderType'] : 'asc';

        $sOrder = "ORDER BY ". $columns[intval($orderCol)] . ' ' . $oderType;
     }


    /* Consulta SQL para obtener los registros con paginación y ordenamiento */
    $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . "
    FROM $table
    $where
    $sOrder
    $sLimit";
    $resultado = $conn->query($sql);
    $num_rows = $resultado->num_rows;

    /* Consulta para total de registro filtrados */
    $sqlFiltro = "SELECT FOUND_ROWS()";
    $resFiltro = $conn->query($sqlFiltro);
    $row_filtro = $resFiltro->fetch_array();
    $totalFiltro = $row_filtro[0];

    /* Consulta para total de registro filtrados */
    $sqlTotal = "SELECT count($id) FROM $table ";
    $resTotal = $conn->query($sqlTotal);
    $row_total = $resTotal->fetch_array();
    $totalRegistros = $row_total[0];

    /* Mostrado resultados */
    $output = [];
    $output['totalRegistros'] = $totalRegistros;
    $output['totalFiltro'] = $totalFiltro;
    $output['data'] = '';
    $output['paginacion'] = '';

    // Construcción de los resultados de la consulta
    if ($num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $output['data'] .= '<tr>';
            $output['data'] .= '<td>' . $row['no_emp'] . '</td>';
            $output['data'] .= '<td>' . $row['nombre'] . '</td>';
            $output['data'] .= '<td>' . $row['apellido'] . '</td>';
            $output['data'] .= '<td>' . $row['fecha_nacimiento'] . '</td>';
            $output['data'] .= '<td>' . $row['fecha_ingreso'] . '</td>';
            $output['data'] .= '<td><button class="btn btn-warning btn-sm" onclick="actualizar(' . $row['no_emp'] . ')">Editar</button></td>';
            $output['data'] .= '<td><button class="btn btn-danger btn-sm" onclick="deleteE(' . $row['no_emp'] . ')">Eliminar</button></td>';
            $output['data'] .= '</tr>';
        }
    } else {
        $output['data'] .= '<tr>';
        $output['data'] .= '<td colspan="7">Sin resultados</td>';
        $output['data'] .= '</tr>';
    }

    // Construcción de la paginación
    if ($output['totalRegistros'] > 0) {
        $totalPaginas = ceil($output['totalRegistros'] / $limit);

        $output['paginacion'] .= '<nav>';
        $output['paginacion'] .= '<ul class="pagination">';

        $numeroInicio = 1;

        if(($pagina - 4) > 1){
            $numeroInicio = $pagina - 4;
        }

        $numeroFin = $numeroInicio + 9;

        if($numeroFin > $totalPaginas){
            $numeroFin = $totalPaginas;
        }

        for ($i = $numeroInicio; $i <= $numeroFin; $i++) {
            if ($pagina == $i) {
                $output['paginacion'] .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
            } else {
                $output['paginacion'] .= '<li class="page-item"><a class="page-link" href="#" onclick="nextPage(' . $i . ')">' . $i . '</a></li>';
            }
        }
        $output['paginacion'] .= '</ul>';
        $output['paginacion'] .= '</nav>';
    }

    // Salida JSON de los resultados
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
}

function update() {
/**
 * Función para actualizar un registro existente en la base de datos.
 */
    global $conn;
    // Verificar si se recibieron los datos del formulario de actualización del registro
    if (isset($_POST['no_emp'], $_POST['fecha_nacimiento'], $_POST['nombre'], $_POST['apellido'], $_POST['genero'], $_POST['fecha_ingreso'])) {
        // Obtener los datos del formulario
        $no_emp = $conn->real_escape_string($_POST['no_emp']);
        $fecha_nacimiento = $conn->real_escape_string($_POST['fecha_nacimiento']);
        $nombre = $conn->real_escape_string($_POST['nombre']);
        $apellido = $conn->real_escape_string($_POST['apellido']);
        $genero = $conn->real_escape_string($_POST['genero']);
        $fecha_ingreso = $conn->real_escape_string($_POST['fecha_ingreso']);

        // Consulta para actualizar los datos del registro en la base de datos
        $sql = "UPDATE registros SET fecha_nacimiento='$fecha_nacimiento', nombre='$nombre', apellido='$apellido', genero='$genero', fecha_ingreso='$fecha_ingreso' WHERE no_emp='$no_emp'";

        if ($conn->query($sql) === TRUE) {
            echo "Registro actualizado exitosamente";
        } else {
            echo "Error al actualizar el registro: " . $conn->error;
        }
    } else {
        echo "No se recibieron datos del formulario";
    }
}

function delete() {
/**
 * Función para eliminar un registro de la base de datos.
 */
    global $conn;
    if (isset($_POST['employee_id'])) {
        $employee_id = $conn->real_escape_string($_POST['employee_id']);
        // Consulta SQL para eliminar el registro de la tabla
        $sql = "DELETE FROM registros WHERE no_emp = '$employee_id'";
        if ($conn->query($sql) === TRUE) {
            echo "Registro eliminado exitosamente";
        } else {
            echo "Error al eliminar el registro: " . $conn->error;
        }
    }
}

$requestType = $_SERVER['REQUEST_METHOD']; // Obtener el tipo de solicitud HTTP (GET, POST, etc.)
// Evaluar el tipo de solicitud
switch ($requestType) {
    case 'POST':
        if(isset($_POST['action'])) { // Verificar si se recibió un parámetro 'action' en la solicitud POST
            $action = $_POST['action'];
            switch ($action) {
                case 'create':
                    create();
                    break;
                case 'update':
                    update();
                    break;
                case 'delete':
                    delete();
                    break;
                case 'read':
                    read();
                    break;
                case "obtenerRegistro":
                // Verificar si se recibió el número de registro
                if (isset($_POST["no_emp"])) {
                    // Obtener el número de registro de la solicitud AJAX
                    $no_emp = $_POST["no_emp"];
                    // Llamar a la función para obtener la información del registro
                    obtenerRegistro($no_emp);
                } else {
                    // Si no se proporciona el número de registro, devolver un mensaje de error
                    echo json_encode(array("error" => "No se proporcionó el número de registro"));
                }
                break;
                default:
                    // Manejar acción desconocida
                    break;
            }
        }
        break;
    default:
        // Manejar otros métodos HTTP si es necesario
        break;
}
?>