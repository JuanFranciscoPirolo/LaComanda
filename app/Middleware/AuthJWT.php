<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthJWT
{
    private static $claveSecreta = 'T3sT$JWT';
    private static $tipoEncriptacion = 'HS256'; // Cambiado a un string en lugar de un array

    public static function CrearToken($datos)
    {
        $ahora = time();
        $payload = array(
            'iat' => $ahora,
            'exp' => $ahora + 60000, // 60,000 seconds = 16.67 hours
            'aud' => self::Aud(),
            'data' => $datos,
            'app' => "LaComanda"
        );
        return JWT::encode($payload, self::$claveSecreta, self::$tipoEncriptacion); // Añadido el tercer parámetro
    }

    public static function VerificarToken($token)
    {
        if (empty($token)) {
            throw new Exception("El token está vacío.");
        }

        try {
            $decodificado = JWT::decode($token, new Key(self::$claveSecreta, self::$tipoEncriptacion)); // Usando Key object
        } catch (Exception $e) {
            throw $e;
        }

        if ($decodificado->aud !== self::Aud()) {
            throw new Exception("No es el usuario válido");
        }
    }

    public static function ObtenerPayLoad($token)
    {
        if (empty($token)) {
            throw new Exception("El token está vacío.");
        }
        return JWT::decode($token, new Key(self::$claveSecreta, self::$tipoEncriptacion));
    }

    public static function ObtenerData($token)
    {
        return JWT::decode($token, new Key(self::$claveSecreta, self::$tipoEncriptacion))->data;
    }

    private static function Aud()
    {
        $aud = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }

        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();

        return sha1($aud);
    }
}
