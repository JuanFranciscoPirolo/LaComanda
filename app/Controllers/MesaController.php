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
    
        // Obtener los datos del formulario
        $nombreMozo = $parametros['nombreMozo']; // Asegúrate de que el nombre del mozo esté presente en los datos del formulario
    
        $fecha_baja = null; // Si no hay una fecha de baja definida en el formulario, se inicializa como null
        $mesa = new Mesa();
        $mesa->codigo = Mesa::generarCodigo(5);
        $mesa->estado = "abierto";
        $mesa->nombreMozo = $nombreMozo; // Asignar el nombre del mozo obtenido del formulario
        $mesa->cobro = $parametros['cobro']; // Se inicializa el cobro en 0
        $mesa->fecha_baja = $fecha_baja; // Asignar la fecha de baja
    
        $mesa->crearMesa();
    
        $payload = json_encode(array("mensaje" => "Mesa creada con éxito"));
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
    
        // Validar que todos los campos necesarios están presentes
        $required_fields = ['codigo', 'nombreMozo', 'cobro'];
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
    
        // Obtener la mesa por su código
        $mesa = Mesa::obtenerMesa($data['codigo']);
    
        // Verificar si se encontró una mesa
        if (!$mesa) {
            http_response_code(404); // Not Found
            header('Content-Type: application/json');
            echo json_encode(["mensaje" => "No se encontró ninguna mesa con el código proporcionado."]);
            return;
        }
    
        // Actualizar los datos de la mesa
        $mesa->nombreMozo = $data['nombreMozo'];
        $mesa->cobro = $data['cobro'];
    
        // Guardar los cambios en la base de datos
        Mesa::modificarMesa($mesa);
    
        $payload = json_encode(array("mensaje" => "Mesa modificada con éxito"));
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    

    public function BorrarUno($request, $response, $args)
    {
        if ($_SERVER["REQUEST_METHOD"] == "DELETE")
        {
            // Leer datos de la solicitud DELETE
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['codigo'])) 
            {
                http_response_code(400); // Bad Request
                header('Content-Type: application/json');
                echo json_encode(["mensaje" => "Falta el número de pedido en la solicitud."]);
                return;
            }

            $codigo = $data['codigo'];
            Mesa::borrarMesa($codigo);

            return $response->withHeader('Content-Type', 'application/json');
        }

    }
}