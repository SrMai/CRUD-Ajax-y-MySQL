<?php
/* Creando una nueva conexión a la base de datos. */
$conn = new mysqli("127.0.0.1", "root", "TuContraseña", "BD_Registros");

/* Comprobando si hay un error de conexión. */
if ($conn->connect_error) {
    die('Error de conexion ' . $conn->connect_error);
}