<?php

include("setup/setup.php");
session_start();

// === CORRECCIÓN 1 (CP-COM-03 - falta de validación de sesión) ===
// Antes: el script usaba $_SESSION['id'] directamente, sin comprobar si existía.
// Si el usuario llegaba aquí sin haber navegado antes una carta (sin sesión de
// restaurant activa), $_SESSION['id'] no estaba definido, generaba un Warning
// y el INSERT igual se ejecutaba con id_restaurante vacío/NULL.
if(!isset($_SESSION['id'])){
    header('Location:index.php');
    exit;
}

// === CORRECCIÓN 2 (CP-COM-01 - SQL Injection) ===
// Antes: $sql="INSERT INTO comentarios SET usuario='".$_POST['usuario']."',comentario='".$_POST['comentario']."',id_restaurante=".$_SESSION['id'];
// Los valores de $_POST se insertaban directo en el SQL sin sanitizar.
// Se reemplaza por una sentencia preparada.
$usuario = $_POST['usuario'];
$comentario = $_POST['comentario'];
$id_restaurante = intval($_SESSION['id']);

$conexion = conectar();
$stmt = mysqli_prepare($conexion, "INSERT INTO comentarios SET usuario=?, comentario=?, id_restaurante=?");
mysqli_stmt_bind_param($stmt, "ssi", $usuario, $comentario, $id_restaurante);
mysqli_stmt_execute($stmt);

// Nota importante (CP-COM-02 - XSS almacenado):
// Este archivo solo INSERTA el comentario. La sanitización de salida
// (htmlspecialchars()) debe aplicarse en index.php, en la línea donde se hace
// "echo $datoscomentarios['comentario'];", ya que ahí es donde el script
// inyectado se ejecutaría en el navegador de otros usuarios. Revisar y corregir
// ese echo en index.php (no incluido en este set de archivos).

header('Location:index.php?id='.$id_restaurante);

?>
