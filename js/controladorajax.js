// JavaScript Document

$(document).ready(function(){
   $('.button').click(function(){
     agregaritems($(this).attr('id'));
   });

   $('.elim').click(function(){
      eliminaritems($(this).attr('id'));
    });
    $('.limpiar').click(function(){
      eliminartodo();
    });
 });

function agregaritems(id)
{
	$.ajax({
            type: "POST",
            url: 'carrito.php',
            data: "op=1&iditems="+id,
            success: function(response)
            {
				$('#myModal').modal('show');
            },
            // === CORRECCIÓN 1 (CP-CAR-01) ===
            // Antes: no existía manejador "error". Cuando carrito.php fallaba en el
            // servidor (por ejemplo, el bug de count($_SESSION["carrito"]) sobre un
            // arreglo no inicializado, que en PHP 8 devuelve un error 500), jQuery
            // no ejecutaba nada: ni mensaje, ni log, ni modal. Para el usuario el
            // botón "Seleccionar" simplemente no hacía nada visible. Ahora se informa
            // el fallo explícitamente, tanto en consola como con una alerta simple.
            error: function(xhr, status, error)
            {
                console.error("Error al agregar el producto al carrito:", status, error, xhr.responseText);
                alert("No se pudo agregar el producto. Intenta nuevamente.");
            }
       });
}

function eliminaritems(pos)
{
	$.ajax({
            type: "POST",
            url: 'carrito.php',
            data: "op=2&pos="+pos,
            success: function(response)
            {
				location.reload();
            },
            // === CORRECCIÓN 2 (robustez) ===
            // Mismo problema que en agregaritems(): sin manejo de error, un fallo del
            // servidor al eliminar un ítem pasaba completamente inadvertido.
            error: function(xhr, status, error)
            {
                console.error("Error al eliminar el producto del carrito:", status, error, xhr.responseText);
                alert("No se pudo eliminar el producto. Intenta nuevamente.");
            }
       });
}

function eliminartodo()
{
	$.ajax({
            type: "POST",
            url: 'carrito.php',
            data: "op=3",
            success: function(response)
            {
				location.reload();
            },
            // === CORRECCIÓN 3 (robustez) ===
            // Igual que las dos funciones anteriores: se agrega manejo de error
            // explícito para que un fallo al vaciar el carrito no quede en silencio.
            error: function(xhr, status, error)
            {
                console.error("Error al vaciar el carrito:", status, error, xhr.responseText);
                alert("No se pudo vaciar el carrito. Intenta nuevamente.");
            }
       });
}
