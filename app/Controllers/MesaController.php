<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

class MesaController extends Mesa implements IApiUsable
{
    public function TraerUno($request, $response, $args)
    {
        $mesa_id = $args['id_mesa'];
        $mesa_final = Mesa::obtenerMesa($mesa_id);
        $payload = json_encode($mesa_final);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listaMesa" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombreMozo = $parametros['nombreMozo']; 
        $fecha_baja = null;

        $mesa = new Mesa();
        $mesa->codigo = Mesa::generarCodigo(5);
        $mesa->estado = "abierto";
        $mesa->nombreMozo = $nombreMozo; 
        $mesa->fecha_baja = $fecha_baja;

        $mesa->crearMesa();

        $payload = json_encode(array("mensaje" => "Mesa creada con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        $required_fields = ['codigo', 'nombreMozo'];
        foreach ($required_fields as $field) 
        {
            if (!isset($data[$field])) 
            {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(["mensaje" => "Faltan datos en la solicitud: $field."]);
                return;
            }
        }

        $mesa = Mesa::obtenerMesa($data['codigo']);
        $mesa->nombreMozo = $data['nombreMozo'];

        Mesa::modificarMesa($mesa);

        $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        if ($_SERVER["REQUEST_METHOD"] == "DELETE")
        {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['codigo'])) 
            {
                http_response_code(400); 
                header('Content-Type: application/json');
                echo json_encode(["mensaje" => "Falta el número de pedido en la solicitud."]);
                return;
            }

            $codigo = $data['codigo'];
            Mesa::borrarMesa($codigo);

            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function PublicarEncuesta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $codigo_mesa = $parametros['codigo_mesa'];
        $puntuacion = $parametros['puntuacion'];
        $comentario = $parametros['comentario'];  

        if (empty($codigo_mesa) || empty($puntuacion)) {
            $payload = json_encode(array("mensaje" => "Faltan parametros requeridos"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        Mesa::InsertarEncuesta($codigo_mesa, $puntuacion, $comentario);

        $payload = json_encode(array("mensaje" => "Encuesta publicada con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function ObtenerComentarios($request, $response, $args)
    {
        $comentarios = Mesa::obtenerMejoresComentarios();

        $payload = json_encode($comentarios);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerMesaUsada($request, $response, $args)
    {
        $mesa = Mesa::obtenerMesaMasUsada();

        if ($mesa) {
            $payload = json_encode($mesa);
        } else {
            $payload = json_encode(array("mensaje" => "No se encontraron datos"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
