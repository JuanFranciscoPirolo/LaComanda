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
                $mesa = Mesa::obtenerMesa($codigo); // Supongamos que tienes un método para obtener la mesa por su código
    
                if ($mesa) {
                    return $handler->handle($request); // Si la mesa existe, continúa con la solicitud
                }
            }
    
            // Si no se encontró la mesa o no se proporcionó el código
            $response = new ResponseMw();
            $response->getBody()->write(json_encode(array("error" => "Mesa no existente")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        public static function ValidarMesaCodigoMesa($request, $handler){
            $parametros = $request->getParsedBody();
            if(isset($parametros['codigo'])){
                $mesa = Mesa::obtenerMesa($parametros['codigo']);
                if($mesa){
                    return $handler->handle($request);
                }
            }
            throw new Exception('Mesa no existente');
        }

        public static function ValidarCampos($request, $handler){
            $parametros = $request->getParsedBody();
            if(isset($parametros['codigo'])){
                $codigo = $parametros['codigo'];
                $mesa = Mesa::obtenerMesa($codigo);
                if(self::ValidarMesaExistente($mesa)){
                    return $handler->handle($request);
                }
            }
            throw new Exception('Campos Invalidos');
        }

        public static function ValidarMesaCerrada($request, $handler){
            $parametros = $request->getParsedBody();
            $mesa = Mesa::obtenerMesa($parametros['codigo']);
            if($mesa->estado == "cerrada"){
                return $handler->handle($request);
            }
            throw new Exception('la mesa no esta cerrada');
        }

        public static function ValidarCamposCobroEntreFechas($request, $handler){
            $parametros = $request->getQueryParams();
            if (isset($parametros['codigo']) && isset($parametros['fechaEntrada']) && isset($parametros['fechaSalida']))
            {
                $mesa = Mesa::obtenerMesa($parametros['codigo']);
                if($mesa && $mesa->estado == "cerrada")
                {
                    return $handler->handle($request);
                }
                else
                {
                    throw new Exception('la mesa no esta cerrada o no existe');
                }
            }
            else
            {
                throw new Exception('Campos Invalidos');
            }
        }


        public static function ValidarMesaExistente($mesa){
            if($mesa){
                return true;
            }
            return false;
        }

    }

?>