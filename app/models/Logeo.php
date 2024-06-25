<?php

require_once './DataBase/AccesoDatos.php';
require_once './Middleware/Logger.php';

class Logueo {
    
    public $id;
    public $id_usuario;
    public $fecha;
    public $tipo_operacion;

    public function __construct(){}

    public function getId() {
        return $this->id;
    }

    public function getIdUsuario() {
        return $this->id_usuario;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getTipoOperacion() {
        return $this->tipo_operacion;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setIdUsuario($id_usuario) {
        $this->id_usuario = $id_usuario;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setTipoOperacion($tipo_operacion) {
        $this->tipo_operacion = $tipo_operacion;
    }

    
    public static function crear($ingreso)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ingresos (id_usuario, fecha, tipo_operacion) VALUES (:id_usuario, :fecha, :tipo_operacion)");
        $consulta->bindValue(':id_usuario', $ingreso->getIdUsuario(), PDO::PARAM_INT);
        $consulta->bindValue(':fecha', $ingreso->getFecha(), PDO::PARAM_STR);
        $consulta->bindValue(':tipo_operacion', $ingreso->getTipoOperacion(), PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objDataAccess = AccesoDatos::obtenerInstancia();
        $query = $objDataAccess->prepareQuery("SELECT id, id_usuario, fecha, tipo_operacion FROM ingresos");
        $query->execute();

        return $query->fetchAll(PDO::FETCH_CLASS, "Logueo");
    }


}


?>