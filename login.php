<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['dashboard_usuario'];
    $password = $_POST['dashboard_clave'];

    $sql = "SELECT * FROM users WHERE name = ? AND pass = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Usuario y contraseña válidos
        session_start();
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header('Location: index.php'); // Redirige al dashboard después de iniciar sesión
        exit;
    } else {
        // Usuario o contraseña incorrectos
        echo "Usuario o contraseña incorrectos";
    }
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">

    <link rel="icon" type="image/png" href="https://carlosayala.space/vistas/assets/admin/assets/img/Logo.png" sizes="64x64">

    <title>Login MAVI</title>

    <!-- Normalize V8.0.1 -->
    <link rel="stylesheet" href="https://carlosayala.space/vistas/assets/admin/css/normalize.css">

    <!-- MDBootstrap V5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://carlosayala.space/vistas/assets/admin/css/mdb.min.css">

    <!-- Font Awesome V5.15.1 -->
    <link rel="stylesheet" href="https://carlosayala.space/vistas/assets/admin/css/all.css">
    <script src="https://kit.fontawesome.com/906a7c7e1a.js" crossorigin="anonymous"></script>

    <!-- Sweet Alert V10.14.0 -->
    <script src="https://carlosayala.space/vistas/assets/admin/js/sweetalert2.js" ></script>

    <!-- General Styles -->
    <link rel="stylesheet" href="https://carlosayala.space/vistas/assets/admin/css/style.css">
    <!-- Animación de Try -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@1.5.7/dist/lottie-player.js"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-interactivity@latest/dist/lottie-interactivity.min.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-content">
            <figure class="full-box mb-4">
                <img src="https://carlosayala.space/vistas/assets/avatar/Avatar_default_male.png" alt="" class="img-fluid login-icon">
            </figure>
            <form action="" method="POST" autocomplete="off">
                <div class="form-outline mb-4">
                    <input type="text" class="form-control" id="login_usuario" name="dashboard_usuario" pattern="[a-zA-Z0-9]{4,30}" maxlength="30" required="" >
                    <label for="login_usuario" class="form-label"><i class="fas fa-user-secret"></i> &nbsp; Usuario</label>
                </div>
                <div class="form-outline mb-4">
                    <input type="password" class="form-control" id="login_clave" name="dashboard_clave" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required="" >
                    <label for="login_clave" class="form-label"><i class="fas fa-key"></i> &nbsp; Contraseña</label>
                </div>
                <button type="submit" class="btn btn-primary text-center mb-4 w-100">LOG IN</button>
            </form>
        </div>
        <a href="https://carlosayala.space/ud/index.php" class="login-icon-home" data-toggle="tooltip" data-placement="top" title="Inicio" ><i class="fas fa-home"></i></a>
    </div>

<!--=============================================
=            Include JavaScript files           =
==============================================-->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

<!-- MDBootstrap V5 -->
<script src="https://carlosayala.space/vistas/assets/admin/js/mdb.min.js" ></script>

<!-- Ajax JS -->
<script src="https://carlosayala.space/vistas/assets/admin/js/ajax.js" ></script>

<!-- General scripts -->
<script src="https://carlosayala.space/vistas/assets/admin/js/main.js" ></script>
</body>
