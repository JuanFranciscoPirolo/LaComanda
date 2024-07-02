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
        $mesa->cobro = $parametros['cobro']; 
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
    
     
        $required_fields = ['codigo', 'nombreMozo', 'cobro'];
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
        $mesa->cobro = $data['cobro'];
    
        
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
        public function ObtenerCobro($request, $response, $args)
    {
        $codigo = $args['codigo'];
        $cobro = Mesa::obtenerCobroMesa($codigo);
        
        if ($cobro !== false) {
            $payload = json_encode(array("codigo" => $codigo, "cobro" => $cobro));
        } else {
            $payload = json_encode(array("mensaje" => "No se encontró ninguna mesa con el código proporcionado."));
        }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}