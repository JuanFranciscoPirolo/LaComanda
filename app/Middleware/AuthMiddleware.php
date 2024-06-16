<?php

use Slim\Psr7\Response as ResponseClass;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware
{
    private $rol; // Parametro para hacer el middleware más reutilizable

    public function __construct($rol)
    {
        $this->rol = $rol;
    }

    public function __invoke(Request $request, RequestHandler $requestHandler)
    {
        $response = new ResponseClass();
        $params = $request->getQueryParams();
        
        // Verificar si las credenciales están en los parámetros de consulta
        if (isset($params["credencial"])) {
            $credenciales = $params["credencial"];
        } else {
            // Si no están en los parámetros de consulta, buscar en el cuerpo de la solicitud
            $parsedBody = $request->getParsedBody();
            $credenciales = isset($parsedBody["credencial"]) ? $parsedBody["credencial"] : null;
        }

        if ($credenciales === $this->rol) {
            $response = $requestHandler->handle($request);
        } else {
            $response->getBody()->write(json_encode(array("error" => "No sos " . $this->rol)));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}
