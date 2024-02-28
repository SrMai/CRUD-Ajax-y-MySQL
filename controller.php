<?php
require 'config.php';

class ClienteManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function obtenerRegistro($id_cliente) {
    /**
     * Obtiene la información de un registro de la base de datos según su número de registro.
     * param int $id_cliente - El número del registro que se va a obtener.
     */

        // Verificar si se recibió el número de registro
        if (isset($id_cliente)) {
            // Consulta SQL para obtener la información del registro
            $sql = "SELECT * FROM clientes WHERE id_cliente = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id_cliente);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $registro = $result->fetch_assoc();
                echo json_encode($registro);
            } else {
                echo json_encode(array("error" => "No se encontró ningún registro con el número de cliente proporcionado"));
            }
            // Cerrar la conexión y liberar los recursos
            $stmt->close();
        } else {
            // Si no se proporciona el número de registro, devolver un mensaje de error
            echo json_encode(array("error" => "No se proporcionó el número de cliente"));
        }
    }

    public function create() {
    /**
     * Función para crear un nuevo registro.
     */

        // Verificar si se recibieron datos del formulario de creación de clientes
        if(isset($_POST['id_cliente'], $_POST['domicilio_cliente'], $_POST['nombre_cliente'], $_POST['apellido_cliente'], $_POST['email_cliente'])) {
            $id_cliente = $this->conn->real_escape_string($_POST['id_cliente']);
            $domicilio_cliente = $this->conn->real_escape_string($_POST['domicilio_cliente']);
            $nombre_cliente = $this->conn->real_escape_string($_POST['nombre_cliente']);
            $apellido_cliente = $this->conn->real_escape_string($_POST['apellido_cliente']);
            $email_cliente = $this->conn->real_escape_string($_POST['email_cliente']);
            // Verificar si la ID del cliente ya existe en la base de datos
            $sql_check_id = "SELECT id_cliente FROM clientes WHERE id_cliente = '$id_cliente'";
            $result_check_id = $this->conn->query($sql_check_id);
            if ($result_check_id->num_rows > 0) {
                echo "Error al crear el registro: La ID del cliente ya existe";
            } else {
                // Consulta para insertar el nuevo registro en la base de datos
                $sql = "INSERT INTO clientes (id_cliente, domicilio_cliente, nombre_cliente, apellido_cliente, email_cliente)
                        VALUES ('$id_cliente', '$domicilio_cliente', '$nombre_cliente', '$apellido_cliente', '$email_cliente')";
                if ($this->conn->query($sql) === TRUE) {
                    echo "Registro creado exitosamente";
                } else {
                    echo "Error al crear el registro: " . $this->conn->error;
                }
            }
        } else {
            echo "No se recibieron datos del formulario";
        }
    }

    public function read() {
    /**
     * Función para realizar la lectura de clientes con filtrado, paginación y ordenamiento.
     */

        /* Un arreglo de las columnas a mostrar en la tabla */
        $columns = ['id_cliente', 'nombre_cliente', 'apellido_cliente', 'domicilio_cliente', 'email_cliente'];

        /* nombre_cliente de la tabla */
        $table = "clientes";

        $id = 'id_cliente';

        $campo = isset($_POST['campo']) ? $this->conn->real_escape_string($_POST['campo']) : null;


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
        $limit = isset($_POST['clientes']) ? $this->conn->real_escape_string($_POST['clientes']) : 10;
        $pagina = isset($_POST['pagina']) ? $this->conn->real_escape_string($_POST['pagina']) : 0;

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


        /* Consulta SQL para obtener los clientes con paginación y ordenamiento */
        $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . "
        FROM $table
        $where
        $sOrder
        $sLimit";
        $resultado = $this->conn->query($sql);
        $num_rows = $resultado->num_rows;

        /* Consulta para total de registro filtrados */
        $sqlFiltro = "SELECT FOUND_ROWS()";
        $resFiltro = $this->conn->query($sqlFiltro);
        $row_filtro = $resFiltro->fetch_array();
        $totalFiltro = $row_filtro[0];

        /* Consulta para total de registro filtrados */
        $sqlTotal = "SELECT count($id) FROM $table ";
        $resTotal = $this->conn->query($sqlTotal);
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
                $output['data'] .= '<tr class="text-center">';
                $output['data'] .= '<td>' . $row['id_cliente'] . '</td>';
                $output['data'] .= '<td>' . $row['nombre_cliente'] . '</td>';
                $output['data'] .= '<td>' . $row['apellido_cliente'] . '</td>';
                $output['data'] .= '<td>' . $row['domicilio_cliente'] . '</td>';
                $output['data'] .= '<td>' . $row['email_cliente'] . '</td>';
                $output['data'] .= '<td><button class="btn btn-warning btn-sm" onclick="actualizar(' . $row['id_cliente'] . ')"><i class="fas fa-sync-alt"></i>Editar</button></td>';
                $output['data'] .= '<td><button class="btn btn-danger btn-sm" onclick="deleteE(' . $row['id_cliente'] . ')">Eliminar</button></td>';
                $output['data'] .= '</tr>';
            }
        } else {
            $output['data'] .= '<tr class="text-center" >';
            $output['data'] .= '<td colspan="7">No hay registros en el sistema</td>';
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

    public function update() {
    /**
     * Función para actualizar un registro existente en la base de datos.
     */

        // Verificar si se recibieron los datos del formulario de actualización del registro
        if (isset($_POST['id_cliente'], $_POST['domicilio_cliente'], $_POST['nombre_cliente'], $_POST['apellido_cliente'], $_POST['email_cliente'])) {
            // Obtener los datos del formulario
            $id_cliente = $this->conn->real_escape_string($_POST['id_cliente']);
            $domicilio_cliente = $this->conn->real_escape_string($_POST['domicilio_cliente']);
            $nombre_cliente = $this->conn->real_escape_string($_POST['nombre_cliente']);
            $apellido_cliente = $this->conn->real_escape_string($_POST['apellido_cliente']);
            $email_cliente = $this->conn->real_escape_string($_POST['email_cliente']);

            // Consulta para actualizar los datos del registro en la base de datos
            $sql = "UPDATE clientes SET domicilio_cliente='$domicilio_cliente', nombre_cliente='$nombre_cliente', apellido_cliente='$apellido_cliente', email_cliente='$email_cliente' WHERE id_cliente='$id_cliente'";

            if ($this->conn->query($sql) === TRUE) {
                echo "Registro actualizado exitosamente";
            } else {
                echo "Error al actualizar el registro: " . $this->conn->error;
            }
        } else {
            echo "No se recibieron datos del formulario";
        }
    }

    public function delete() {
    /**
     * Función para eliminar un registro de la base de datos.
     */

        if (isset($_POST['employee_id'])) {
            $employee_id = $this->conn->real_escape_string($_POST['employee_id']);
            // Consulta SQL para eliminar el registro de la tabla
            $sql = "DELETE FROM clientes WHERE id_cliente = '$employee_id'";
            if ($this->conn->query($sql) === TRUE) {
                echo "Registro eliminado exitosamente";
            } else {
                echo "Error al eliminar el registro: " . $this->conn->error;
            }
        }
    }
}

$requestType = $_SERVER['REQUEST_METHOD']; // Obtener el tipo de solicitud HTTP (GET, POST, etc.)
// Evaluar el tipo de solicitud
switch ($requestType) {
    case 'POST':
    session_start();
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            // Si usuario no ha iniciado sesión, redirigir a la petición a la URL de inicio de sesión, este filtro
            // es por seguridad para evitar saltos de inicio de sesion
            echo "Su petición no fue procesada debido a falta de permisos";
            header('Location: login.php');
            break;
            exit;
        } else {
            if(isset($_POST['action'])) { // Verificar si se recibió un parámetro 'action' en la solicitud POST
                $action = $_POST['action'];
                $clienteManager = new ClienteManager($conn);
                switch ($action) {
                    case 'create':
                        $clienteManager->create();
                        break;
                    case 'update':
                        $clienteManager->update();
                        break;
                    case 'delete':
                        $clienteManager->delete();
                        break;
                    case 'read':
                        $clienteManager->read();
                        break;
                    case "obtenerRegistro":
                    // Verificar si se recibió el número de registro
                    if (isset($_POST["id_cliente"])) {
                        // Obtener el número de registro de la solicitud AJAX
                        $id_cliente = $_POST["id_cliente"];
                        // Llamar a la función para obtener la información del registro
                        $clienteManager->obtenerRegistro($id_cliente);
                    } else {
                        // Si no se proporciona el número de registro, devolver un mensaje de error
                        echo json_encode(array("error" => "No se proporcionó el número de cliente"));
                    }
                    break;
                    default:
                        // Manejar acción desconocida
                        break;
                }
            }
            break;
        }
    default:
        // Manejar otros métodos HTTP si es necesario
        break;
}
?>