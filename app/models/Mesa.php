<?php
class Mesa
{
    public $codigo;
    public $estado;
    public $nombreMozo;
    public $cobro;
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
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigo, estado, nombreMozo, cobro, fecha_baja) 
                                                        VALUES (:codigo, :estado, :nombreMozo, :cobro, :fecha_baja)"); 
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':nombreMozo', $this->nombreMozo, PDO::PARAM_STR);
        $consulta->bindValue(':cobro', $this->cobro, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_baja', $this->fecha_baja, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function modificarMesa($mesa)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET estado = :estado, 
                                                    nombreMozo = :nombreMozo, cobro = :cobro, fecha_baja = :fecha_baja
                                                    WHERE codigo = :codigo");

        $consulta->bindValue(':nombreMozo', $mesa->nombreMozo, PDO::PARAM_STR);
        $consulta->bindValue(':cobro', $mesa->cobro, PDO::PARAM_STR);
        $consulta->bindValue(':codigo', $mesa->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_baja', $mesa->fecha_baja, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $mesa->estado, PDO::PARAM_STR);


        $consulta->execute();
    }

    public static function borrarMesa($codigo)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET fecha_baja = :fecha_baja, estado = 'inactiva' WHERE codigo = :codigo");

        $fecha = new DateTime(date("Y-m-d"));
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_baja', date_format($fecha, 'Y-m-d'), PDO::PARAM_STR);
        $consulta->execute();
        echo "Mesa borrada correctamente";
    }

    public static function generarCodigo($length = 5) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $len = strlen($chars);
        $retorno = '';
        for ($i = 0; $i < $length; $i++) {
            $retorno .= $chars[mt_rand(0, $len - 1)];
        }
        return $retorno;
    }
    

    public static function CobrarYCerrarMesa($codigo, $cobro){
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET estado = :estado, cobro = :cobro WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', 'cerrada', PDO::PARAM_STR);
        $consulta->bindValue(':cobro', $cobro, PDO::PARAM_STR);
        $consulta->execute();
    }
}
