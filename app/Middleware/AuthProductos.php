<?php
use Slim\Psr7\Response as ResponseClass;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthProductos
{


    public static function ValidarCampos(Request $request, RequestHandler $requestHandler)
    {
        $params = $request->getParsedBody();

        if (isset($params['rol'], $params['tipo'], $params['sector']) &&
            !empty($params['rol']) && !empty($params['tipo']) && !empty($params['sector'])
        ) {
            $response = $requestHandler->handle($request);
        } else {
            $response = new ResponseClass();
            $response->getBody()->write(json_encode(array("error" => "Faltan parametros o son invalidos: rol, tipo, sector")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        return $response;
    }

    public static function ValidarRol(Request $request, RequestHandler $requestHandler)
    {
        $params = $request->getParsedBody();
        
        if (!isset($params['id_usuario']) || !isset($params['id_producto'])) {
            $response = new ResponseClass();
            $response->getBody()->write(json_encode(array("error" => "Faltan parámetros id_usuario o id_producto")));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $id_usuario = $params['id_usuario'];
        $id_producto = $params['id_producto'];

        // Obtener usuario y producto de la base de datos
        $usuario = Usuario::obtenerUsuario($id_usuario);
        $producto = Producto::ObtenerProducto($id_producto);

        if ($usuario && $producto && self::EsUsuarioAutorizado($usuario, $producto)) {
            $response = $requestHandler->handle($request);
        } else {
            $response = new ResponseClass();
            $response->getBody()->write(json_encode(array("error" => "Usuario no autorizado para modificar/borrar este producto")));
            return $response->withHeader('Content-Type', 'application/json');
        }
        return $response;
    }

    private static function EsUsuarioAutorizado($usuario, $producto)
    {
        $sectorProducto = $producto->sector;
        $sectorUsuario = $usuario->rol;

        $sectoresPermitidos = [
            1 => [2],           // Barra de tragos y vinos => Bartender
            2 => [3],           // Barra de choperas => Cervecero
            3 => [4],           // Cocina => Cocinero
            4 => [4],           // Candy Bar => Cocinero
        ];

        return isset($sectoresPermitidos[$sectorProducto]) && in_array($sectorUsuario, $sectoresPermitidos[$sectorProducto]);
    }
}
