<?php

use Slim\Psr7\Response as ResponseClass;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware
{
    private $rol;

    public function __construct($rol)
    {
        $this->rol = $rol;
    }

    public function __invoke(Request $request, RequestHandler $requestHandler)
    {
        $response = new ResponseClass();
        $params = $request->getQueryParams();
        
        if (isset($params["credencial"])) {
            $credenciales = $params["credencial"];
        } else {
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
