<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class ValidarSocio{

    public function __invoke(Request $request, RequestHandler $handler){
        $header = $request->getHeaderLine(("Authorization"));
        $response = new Response;

        try{
            if($header === ""){
                $response->getBody()->write(json_encode(array('Error' => "Token invalido")));
            }else{

                $token = trim(explode("Bearer", $header)[1]);
                $data = AuthJWT::ObtenerData($token);

                if($data->rol == 'socio'){
                    $response = $handler->handle($request);
                }
                else{
                    $response->getBody()->write(json_encode(array('Error' => "Accion solo para los socios")));
                }
            }
        }
        catch(Exception $ex){
            $response->getBody()->write(json_encode(array("Error"=> $ex->getMessage())));

        }
        finally{
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
}

?>