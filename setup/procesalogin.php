<?php

include("setup.php");

// === CORRECCIÓN 1 (CP-LOGIN-03 - SQL Injection) ===
// Antes: $sql="select * from usuarios where email='".$_POST['frmusuario']."' and password='".$_POST['frmpassword']."'";
// Los valores de $_POST se concatenaban directo dentro de las comillas del SQL.
// Un usuario como  admin@gmail.com' --   anulaba la verificación de password
// (todo lo que sigue al -- se vuelve comentario SQL) y permitía iniciar sesión sin clave.
// Se reemplaza por una sentencia preparada con parámetros bindeados.
session_start(); // === CORRECCIÓN 2 (estructura) ===
// Antes: session_start() solo se llamaba DENTRO del if($cont_login!=0), es decir,
// nunca se iniciaba sesión en el intento fallido. Se mueve al inicio del archivo
// para tener un manejo de sesión consistente en ambos casos.

$conexion = conectar();
$frmusuario = $_POST['frmusuario'];
$frmpassword = $_POST['frmpassword'];

$stmt = mysqli_prepare($conexion, "select * from usuarios where email=? and password=? and estado='1'");
// === CORRECCIÓN 3 (CP-LOGIN-01 - lógica de negocio faltante) ===
// Antes: la consulta no usaba el campo "estado" de la tabla usuarios, por lo que
// una cuenta marcada como inactiva (estado != '1') igual podía autenticarse.
// Ahora solo se permite el acceso si estado = '1'.
mysqli_stmt_bind_param($stmt, "ss", $frmusuario, $frmpassword);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$cont_login = mysqli_num_rows($result);
$datos = mysqli_fetch_array($result);

if($cont_login!=0)
{
    $_SESSION['nombre']=$datos['nombre'];
    header('Location:../index.php');
}else{
    // === CORRECCIÓN 4 (CP-LOGIN-02 - feedback al usuario) ===
    // Antes: un login fallido redirigía exactamente igual que uno exitoso,
    // sin ningún mensaje, dejando al usuario sin saber si su intento falló.
    header('Location:../index.php?login_error=1');
}

?>
