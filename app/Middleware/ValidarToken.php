<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

require_once './Middleware/AuthJWT.php';

class ValidarToken
{
    private static function obtenerTokenDelHeader(Request $request): ?string
    {
        $header = $request->getHeaderLine("Authorization"); // Obtiene el header de autorización
        if (empty($header) || !str_starts_with($header, 'Bearer ')) {
            return null; // Devuelve null si el header no está presente o no tiene el formato correcto
        }
        return trim(explode("Bearer", $header)[1] ?? ''); // Devuelve el token o una cadena vacía si no está presente
    }

    public static function ValidarSocio(Request $request, RequestHandler $handler): Response
    {
        $token = self::obtenerTokenDelHeader($request);
        $response = new Response();

        if ($token === null) {
            $response->getBody()->write(json_encode(['Error' => 'Token no proporcionado o formato incorrecto']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        try {
            $payload = AuthJWT::ObtenerData($token);
            if ($payload->rol == 'socio') {
                return $handler->handle($request);
            } else {
                $response->getBody()->write(json_encode(['Error' => 'Accion solo para los socios']));
            }
        } catch (Exception $ex) {
            $response->getBody()->write(json_encode(['Error' => $ex->getMessage()]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarMozo(Request $request, RequestHandler $handler): Response
    {
        $token = self::obtenerTokenDelHeader($request);
        $response = new Response();

        if ($token === null) {
            $response->getBody()->write(json_encode(['Error' => 'Token no proporcionado o formato incorrecto']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        try {
            $payload = AuthJWT::ObtenerData($token);
            if ($payload->rol == 'mozo') {
                return $handler->handle($request);
            } else {
                $response->getBody()->write(json_encode(['Error' => 'Accion solo para los mozos']));
            }
        } catch (Exception $ex) {
            $response->getBody()->write(json_encode(['Error' => $ex->getMessage()]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarCocinero(Request $request, RequestHandler $handler): Response
    {
        $token = self::obtenerTokenDelHeader($request);
        $response = new Response();

        if ($token === null) {
            $response->getBody()->write(json_encode(['Error' => 'Token no proporcionado o formato incorrecto']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        try {
            $payload = AuthJWT::ObtenerData($token);
            if ($payload->rol == 'cocinero') {
                return $handler->handle($request);
            } else {
                $response->getBody()->write(json_encode(['Error' => 'Accion solo para los cocineros']));
            }
        } catch (Exception $ex) {
            $response->getBody()->write(json_encode(['Error' => $ex->getMessage()]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}
