<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
    public function TraerUno($request, $response, $args)
    {
        $id_pedido = $args['id_pedido'];
        $pedido_final = Pedido::obtenerPedido($id_pedido);
        $payload = json_encode($pedido_final);

        $response->getBody()->write($payload);
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
        $precio_final = $parametros['precio_final'];
        $fecha_baja = null;

        $pedido = new Pedido();
        $pedido->nombre_cliente = $nombre_cliente;
        $pedido->tiempo_estimado = $tiempo_estimado;
        $pedido->precio_final = $precio_final;
        $pedido->fecha_baja = $fecha_baja;

        $pedido->crearPedido();

        $payload = json_encode(array("mensaje" => "Pedido creado con éxito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        // Validar que todos los campos necesarios están presentes
        $required_fields = ['nombre_cliente', 'tiempo_estimado', 'precio_final', 'id_pedido'];
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

        $pedido = Pedido::obtenerPedido($data['id_pedido']);

        $pedido->nombre_cliente = $data['nombre_cliente'];
        $pedido->tiempo_estimado = $data['tiempo_estimado'];
        $pedido->precio_final = $data['precio_final'];
        $pedido->fecha_baja = $data['fecha_baja'];

        Pedido::modificarPedido($pedido);

        $payload = json_encode(array("mensaje" => "Pedido modificado con éxito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        if ($_SERVER["REQUEST_METHOD"] == "DELETE")
        {
            // Leer datos de la solicitud DELETE
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['id_pedido'])) 
            {
                http_response_code(400); // Bad Request
                header('Content-Type: application/json');
                echo json_encode(["mensaje" => "Falta el número de pedido en la solicitud."]);
                return;
            }

            $pedidoId = $data['id_pedido'];
            Pedido::borrarPedido($pedidoId);

            return $response->withHeader('Content-Type', 'application/json');
        }
    }
}
