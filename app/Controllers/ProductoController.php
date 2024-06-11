<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto implements IApiUsable
{
    public function TraerUno($request, $response, $args)
    {
        $id_producto = $args['id_producto'];
        $producto_final = Producto::obtenerProducto($id_producto);
        $payload = json_encode($producto_final);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::obtenerTodos();
        $payload = json_encode(array("listaProducto" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        
        $precio = $parametros['precio'];
        $tipo = $parametros['tipo'];
        $sector = $parametros['sector'];
        $fecha_baja = null;

        $producto = new Producto();
        $producto->precio = $precio;
        $producto->tipo = $tipo;
        $producto->sector = $sector;
        $producto->fecha_baja = $fecha_baja;

        $producto->crearProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        // Validar que todos los campos necesarios están presentes
        $required_fields = ['precio', 'tipo', 'sector','id_producto'];
        foreach ($required_fields as $field) 
        {
            if (!isset($data[$field])) 
            {
                http_response_code(400); // Bad Request
                header('Content-Type: application/json');
                echo json_encode(["mensaje" => "Faltan datos en la solicitud: $field."]);
                return;
            }
        }

        $producto = Producto::obtenerProducto($data['id_producto']);

        $producto->precio = $data['precio'];
        $producto->tipo = $data['tipo'];
        $producto->sector = $data['sector'];
        $producto->fecha_baja = $data['fecha_baja'];

        Producto::modificarProducto($producto);

        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        if ($_SERVER["REQUEST_METHOD"] == "DELETE")
        {
            // Leer datos de la solicitud DELETE
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['id_producto'])) 
            {
                http_response_code(400); // Bad Request
                header('Content-Type: application/json');
                echo json_encode(["mensaje" => "Falta el número de pedido en la solicitud."]);
                return;
            }

            $productoId = $data['id_producto'];
            Producto::borrarProducto($productoId);

            return $response->withHeader('Content-Type', 'application/json');
        }
    }
}