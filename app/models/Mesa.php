<?php
class Mesa
{
    public $codigo;
    public $estado;
    public $nombreMozo;
    public $fecha_baja;

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function obtenerMesa($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    public function crearMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigo, estado, nombreMozo, fecha_baja) 
                                                        VALUES (:codigo, :estado, :nombreMozo, :fecha_baja)"); 
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':nombreMozo', $this->nombreMozo, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_baja', $this->fecha_baja, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function modificarMesa($mesa)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET estado = :estado, 
                                                    nombreMozo = :nombreMozo, fecha_baja = :fecha_baja
                                                    WHERE codigo = :codigo");

        $consulta->bindValue(':nombreMozo', $mesa->nombreMozo, PDO::PARAM_STR);
        $consulta->bindValue(':codigo', $mesa->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_baja', $mesa->fecha_baja, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $mesa->estado, PDO::PARAM_STR);


        $consulta->execute();
    }

    public static function borrarMesa($codigo)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET fecha_baja = :fecha_baja, estado = 'cerrada' WHERE codigo = :codigo");

        $fecha = new DateTime(date("Y-m-d"));
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_baja', date_format($fecha, 'Y-m-d'), PDO::PARAM_STR);
        $consulta->execute();
        echo "Mesa borrada correctamente";
    }

    public static function generarCodigo($length = 5) 
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $len = strlen($chars);
        $retorno = '';
        for ($i = 0; $i < $length; $i++) {
            $retorno .= $chars[mt_rand(0, $len - 1)];
        }
        return $retorno;
    }

    public static function obtenerCobroMesa($codigo) 
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT cobro FROM mesas WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();

        $cobro = $consulta->fetchColumn();
        
        return $cobro;
    }

    public static function InsertarEncuesta($codigo_mesa, $puntuacion, $comentario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "INSERT INTO encuestas (codigo_mesa, puntuacion, comentario) VALUES (:codigo_mesa, :puntuacion, :comentario)"
        );
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':puntuacion', $puntuacion, PDO::PARAM_INT);
        $consulta->bindValue(':comentario', $comentario, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function obtenerMejoresComentarios()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("
            SELECT comentario, puntuacion, codigo_mesa
            FROM encuestas 
            ORDER BY puntuacion DESC 
            LIMIT 3
        ");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function obtenerMesaMasUsada()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("
            SELECT codigo_mesa, COUNT(*) AS cantidad_pedidos
            FROM pedidos
            GROUP BY codigo_mesa
            ORDER BY cantidad_pedidos DESC
            LIMIT 1
        ");
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }
    
    



    
    
}
