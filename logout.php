<?php
session_start();

// Destruir todas las variables de sesión
session_destroy();

// Redirigir a la página de inicio de sesión después de cerrar la sesión
header('Location: login.php');
exit;
?>
