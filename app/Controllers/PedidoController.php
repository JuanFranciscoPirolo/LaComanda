<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
    public function TraerUno($request, $response, $args)
    {
        $codigo_pedido = $args['codigo_pedido'];
        $pedido_final = Pedido::obtenerPedido($codigo_pedido);
        $productos = Producto::obtenerTodos();
        
        if ($pedido_final) {
            echo "Detalles del Pedido:\n";
            echo "ID Pedido: " . $pedido_final->id_pedido . "\n";
            echo "Nombre Cliente: " . $pedido_final->nombre_cliente . "\n";
            echo "Tiempo Estimado: " . $pedido_final->tiempo_estimado . "\n";
            echo "Precio Final: " . $pedido_final->precio_final . "\n";
            echo "Fecha Baja: " . $pedido_final->fecha_baja . "\n";
            echo "Código Mesa: " . $pedido_final->codigo_mesa . "\n";
    
            if (!empty($pedido_final->productos)) {
                echo "Productos:\n";
    
                foreach ($pedido_final->productos as $detalle) 
                {
                    if (is_array($detalle)) {

                        $id_producto = $detalle['id_producto'];
                        $cantidad = $detalle['cantidad'];
                    } 
                    else
                     {
                        $id_producto = $detalle->id_producto;
                        $cantidad = $detalle->cantidad;
                    }
    
                    
                    $nombre_producto = "Producto no encontrado";
                    foreach ($productos as $producto) {
                        if ($producto->id_producto == $id_producto) 
                        {
                            $nombre_producto = $producto->tipo; 
                            break;
                        }
                    }
    
                    echo "Producto: " . $nombre_producto . ", Cantidad: " . $cantidad;
                }
            } else {
                echo "No hay productos asociados a este pedido.";
            }
        } else {
            echo "Pedido no encontrado.";
        }
    
        return $response;
    }

    public function cambiarEstadoAPreparacionn($request, $response, $args) {
        $params = $request->getParsedBody();
        $id_pedido = $params['id_pedido'];
        $tiempo_preparacion = $params['tiempo_preparacion'];

        $resultado = Pedido::cambiarEstadoAPreparacion($id_pedido, $tiempo_preparacion);

        $payload = json_encode(array("mensaje" => $resultado ? "Estado cambiado a en preparación" : "Error al cambiar el estado"));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function obtenerDemora($request, $response, $args) 
    {
        $codigo_mesa = $args['codigo_mesa'];
        $id_pedido = $args['id_pedido'];

        $pedidos = Pedido::obtenerTodos();
        $pedidoEncontrado = null;

        foreach ($pedidos as $pedido) 
        {
            if ($pedido->codigo_mesa == $codigo_mesa && $pedido->id_pedido == $id_pedido) 
            {
                $pedidoEncontrado = $pedido;
                break;
            }
        }

        if ($pedidoEncontrado) 
        {
            $response->getBody()->write(json_encode(['tiempo_estimado' => $pedidoEncontrado->tiempo_estimado]));
        } 
        else 
        {
            $response->getBody()->write(json_encode(['error' => 'Pedido no encontrado']));
            return $response->withStatus(404);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
    public function CambiarEstadoListoParaServirr($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $codigoPedido = $parametros['codigo_pedido'];
        $idEmpleado = $parametros['id_usuario'];
    
        
        $usuario = Usuario::obtenerUsuario($idEmpleado);
        $rol = $usuario->rol;
    
        
        $filasAfectadas = Pedido::cambiarEstadoListoParaServir($codigoPedido, $rol);
    
        if ($filasAfectadas > 0) {
            $payload = json_encode(array("mensaje" => "Estado actualizado a 'listo para servir' para los productos del rol $rol"));
        } else {
            $payload = json_encode(array("mensaje" => "No se encontraron productos para actualizar"));
        }
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function listarPedidos($request, $response, $args) 
    {
        $pedidos = Pedido::obtenerTodos();

        $response->getBody()->write(json_encode($pedidos));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::obtenerTodos();
        $payload = json_encode(array("listaPedido" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
    
        $nombre_cliente = $parametros['nombre_cliente'];
        $tiempo_estimado = $parametros['tiempo_estimado'];
        $codigo_mesa = $parametros['codigo_mesa'];
        $precio_final = $parametros['precio_final'];
        $productos = $parametros['productos'];
        $fecha_baja = null;
    
        
        foreach ($productos as $producto) 
        {
            $id_producto = $producto['id_producto'];
            $producto_db = Producto::obtenerProducto($id_producto);

            if (!$producto_db) 
            {
                $payload = json_encode(array("mensaje" => "El producto con id $id_producto no existe."));

                $response->getBody()->write($payload);

                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }
    
        $pedido = new Pedido();
        $pedido->nombre_cliente = $nombre_cliente;
        $pedido->tiempo_estimado = $tiempo_estimado;
        $pedido->codigo_mesa = $codigo_mesa;
        $pedido->precio_final = $precio_final;
        $pedido->fecha_baja = $fecha_baja;
        $pedido->productos = $productos;
        $pedido->estado = "en preparacion";
        $pedido->crearPedido();

        $payload = json_encode(array("mensaje" => "Pedido creado con exito"));
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    

    public function ModificarUno($request, $response, $args)
    {
        $data = $request->getParsedBody();
    
        $required_fields = ['id_pedido', 'nombre_cliente', 'tiempo_estimado', 'precio_final'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                $payload = json_encode(array("mensaje" => "Faltan datos en la solicitud: $field."));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }
    
        $pedido = Pedido::obtenerPedido($data['id_pedido']);
        if (!$pedido) {
            $payload = json_encode(array("mensaje" => "Pedido no encontrado."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    
        $pedido->nombre_cliente = $data['nombre_cliente'];
        $pedido->tiempo_estimado = $data['tiempo_estimado'];
        $pedido->precio_final = $data['precio_final'];
        $pedido->fecha_baja = isset($data['fecha_baja']) ? $data['fecha_baja'] : null;
    
        Pedido::modificarPedido($pedido);
    
        $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    

    public function BorrarUno($request, $response, $args)
    {
        if ($_SERVER["REQUEST_METHOD"] == "DELETE")
        {
            // Leer datos de la solicitud DELETE
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['codigo_mesa'])) 
            {
                http_response_code(400); // Bad Request
                header('Content-Type: application/json');
                echo json_encode(["mensaje" => "Falta el número de pedido en la solicitud."]);
                return;
            }

            $codigo_mesa = $data['codigo_mesa'];
            Pedido::borrarPedido($codigo_mesa); // o sea se entrega
            
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public static function listarPedidosListos($request, $response, $args) {
        $pedidosListos = Pedido::obtenerPedidosListos();

        $response->getBody()->write(json_encode($pedidosListos));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function ActualizarFotos($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $codigo = $parametros['codigo'];
        $foto = $parametros['foto_url'];
    
        $filasAfectadas = Pedido::actualizarFoto($codigo, $foto);
        
        if ($filasAfectadas > 0) {
            $payload = json_encode(array("mensaje" => "Foto actualizada con exito"));
        } else {
            $payload = json_encode(array("mensaje" => "No se encontro la mesa o no se pudo actualizar la foto"));
        }
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarPedidosNoEntregadosATiempo($request, $response, $args) {
        $pedidosNoEntregados = Pedido::obtenerPedidosNoEntregadosATiempo();
    
        if (empty($pedidosNoEntregados)) {
            $response->getBody()->write(json_encode(["mensaje" => "No hay pedidos que no se entregaron a tiempo."]));
        } else {
            $response->getBody()->write(json_encode($pedidosNoEntregados));
        }
    
        return $response->withHeader('Content-Type', 'application/json');
    }
    

    
}