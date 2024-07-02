<?php
use Slim\Psr7\Response as ResponseMw;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
require_once './models/Mesa.php';

    class AuthMesas{
        public static function ValidarMesa(Request $request, RequestHandler $handler)
        {
            $params = $request->getParsedBody();
            if (isset($params['codigo'])) {
                $codigo = $params['codigo'];
                $mesa = Mesa::obtenerMesa($codigo);
    
                if ($mesa) {
                    return $handler->handle($request);
                }
            }
    

            $response = new ResponseMw();
            $response->getBody()->write(json_encode(array("error" => "Mesa no existente")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        public static function ValidarMesaCodigoMesa($request, $handler){
            $parametros = $request->getParsedBody();
            $response = new ResponseMw(); 
            if(isset($parametros['codigo_mesa']))
            {
                $codigo_mesa = $parametros['codigo_mesa'];
                $mesas = Mesa::obtenerTodos();
                
                foreach ($mesas as $mesa) 
                {
                    if ($mesa->codigo == $codigo_mesa) 
                    {
                        return $handler->handle($request);
                    }
                }
              
                $response->getBody()->write(json_encode(array("mensaje" => "La mesa con el codigo: $codigo_mesa no existe")));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            else {
               
                $response->getBody()->write(json_encode(array("mensaje" => "Mesa no existente")));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        public static function ValidarMozoExistente($request, RequestHandler $handler)
        {
            $response = new ResponseMw(); 
            $parametros = $request->getParsedBody();
        
            if (isset($parametros['nombreMozo'])) 
            
            {
                $nombre_mozo = $parametros['nombreMozo'];
                $usuarios = Usuario::obtenerTodos();
                
                foreach ($usuarios as $usuario) 
                {
                    if ($usuario->rol === 'mozo' && $usuario->nombre === $nombre_mozo)
                    {
                        return $handler->handle($request);
                    }
                }
        
                $response->getBody()->write(json_encode(array("mensaje" => "El mozo con nombre $nombre_mozo no existe")));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            } else 
            {
                $response->getBody()->write(json_encode(array("mensaje" => "Nombre de mozo no proporcionado")));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }
        
        public static function ValidarMesaCerrada($request, $handler){
            $parametros = $request->getParsedBody();
            $mesa = Mesa::obtenerMesa($parametros['codigo']);
            if($mesa->estado == "cerrada")
            {
                return $handler->handle($request);
            }
            throw new Exception('la mesa no esta cerrada');
        }

        public static function ValidarMesaExistente($mesa){
            if($mesa){
                return true;
            }
            return false;
        }

    }

?>
 