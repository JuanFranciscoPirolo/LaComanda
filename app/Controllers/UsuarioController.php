<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';

class UsuarioController extends Usuario implements IApiUsable
{
    public function TraerUno($request, $response, $args)
    {
        $usr_id = $args['id_usuario'];
        $usuario = Usuario::obtenerUsuario($usr_id);
        $payload = json_encode($usuario);
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        
        // DATOS DEL USUARIO
        $nombre = $parametros['nombre'];
        $clave = $parametros['clave'];
        $rol = $parametros['rol'];
        $estado = "activo";
        $fecha_baja = null;

        // Creamos el usuario
        $user = new Usuario();
        $user->nombre = $nombre;
        $user->clave = $clave;
        $user->rol = $rol;
        $user->estado = $estado;
        $user->fecha_baja = $fecha_baja;
        $user->crearUsuario();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        // Validar que todos los campos necesarios están presentes
        $required_fields = ['nombre', 'rol', 'clave', 'estado', 'id_usuario'];
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

        $usuario = Usuario::obtenerUsuario($data['id_usuario']);

        $usuario->nombre = $data['nombre'];
        $usuario->clave = $data['clave'];
        $usuario->rol = $data['rol'];
        $usuario->estado = $data['estado'];

        Usuario::modificarUsuario($usuario);

        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        if ($_SERVER["REQUEST_METHOD"] == "DELETE")
        {
            // Leer datos de la solicitud DELETE
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['id_usuario'])) 
            {
                http_response_code(400); // Bad Request
                header('Content-Type: application/json');
                echo json_encode(["mensaje" => "Falta el número de pedido en la solicitud."]);
                return;
            }

            $usuarioId = $data['id_usuario'];
            Usuario::borrarUsuario($usuarioId);

            return $response->withHeader('Content-Type', 'application/json');
        }

    }
}