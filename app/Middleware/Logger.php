<?php

require_once "./models/Usuario.php";
require_once "./models/Logeo.php";

class Logger{

    public static function ValidarLogin($request, $handler){

        $parametros = $request->getParsedBody();
        $nombre = $parametros['nombre'];
        $clave = $parametros['clave'];
        $usuario = Usuario::obtenerPorClave($nombre, $clave);
        
        if ($usuario != false)
        {
            $logueo = new Logueo();
            $logueo->id_usuario = $usuario->id_usuario;
            $logueo->fecha = date('Y-m-d H:i:s');
            $logueo->tipo_operacion = 'Login-' .ucfirst($usuario->rol);
            Logueo::crear($logueo);
            return $handler->handle($request);
        }

        throw new Exception("Usuario y/o clave erroneos");
    }
}

?>