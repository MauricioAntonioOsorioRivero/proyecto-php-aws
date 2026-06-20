<?php

include("setup/setup.php");
session_start();

switch($_POST['op'])
{
    case "1": insertar();
        break;
    case "2": eliminaritems();
        break;
    case "3": eliminartodo();
        break;
}

function insertar()
{
    // === CORRECCIÓN 1 (CP-CAR-01) ===
    // Antes: $_SESSION["carrito"]; -> no inicializaba nada, era una instrucción sin efecto.
    // Si era el primer producto agregado, count($_SESSION["carrito"]) fallaba (TypeError en PHP 8)
    // porque la clave "carrito" todavía no existía. Esto era lo que rompía el botón "Seleccionar".
    if(!isset($_SESSION["carrito"]) || !is_array($_SESSION["carrito"])){
        $_SESSION["carrito"] = array();
    }

    // === CORRECCIÓN 2 (CP-LOGIN/CP-CAR-02 - seguridad) ===
    // Antes: $sql="select id, nombre, precio from items where id=".$_POST['iditems'];
    // El valor de $_POST llegaba directo al SQL, sin validar ni sanitizar -> SQL Injection.
    // Se castea a entero con intval() porque "id" siempre debe ser numérico.
    $iditems = intval($_POST['iditems']);
    $sql = "select id, nombre, precio from items where id=" . $iditems;
    $result = mysqli_query(conectar(), $sql);
    $datos = mysqli_fetch_array($result);

    // === CORRECCIÓN 3 (CP-CAR-02) ===
    // Antes: no se comprobaba si el producto realmente existía en la base de datos.
    // Si el id no existía, mysqli_fetch_array() devuelve false y se insertaba un ítem
    // con nombre y precio nulos. Ahora se corta la ejecución si no hay datos.
    if(!$datos){
        return;
    }

    $pos = count($_SESSION["carrito"]) + 1;
    $productos = array("posicion" => $pos, "id" => $datos['id'], "nombre" => $datos['nombre'], "precio" => $datos['precio']);
    $_SESSION["carrito"][$pos] = $productos;
}

function eliminaritems()
{
    // === CORRECCIÓN 4 (robustez) ===
    // Se valida que exista la clave antes de hacer unset(), para evitar notices
    // si se llama con una posición que ya fue eliminada o nunca existió.
    if(isset($_SESSION["carrito"][$_POST['pos']])){
        unset($_SESSION["carrito"][$_POST['pos']]);
    }
}

function eliminartodo()
{
    session_destroy();
}
?>
