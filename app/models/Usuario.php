<?php
require_once ("./utilities/crearPDF.php");
class Usuario
{
    public $id_usuario;
    public $nombre;
    public $clave;
    public $fecha_baja;
    public $rol;
    public $sueldo; 
    
    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerEstadisticas()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        
        
        $consulta_pedidos = "
        SELECT 
            COUNT(*) AS total_pedidos,
            SUM(CASE WHEN estado = 'Listo para servir' THEN 1 ELSE 0 END) AS pedidos_listos_para_servir,
            SUM(precio_final) AS ingresos_totales,
            AVG(TIME_TO_SEC(tiempo_estimado)) / 60 AS tiempo_estimado_promedio_minutos
        FROM pedidos
        WHERE fecha_baja >= CURDATE() - INTERVAL 30 DAY
        ";
        $resultado_pedidos = $objAccesoDatos->prepararConsulta($consulta_pedidos);
        $resultado_pedidos->execute();
        $estadisticas_pedidos = $resultado_pedidos->fetch(PDO::FETCH_ASSOC);

        $consulta_mesas = "
        SELECT 
            COUNT(*) AS mesas_abiertas,
            SUM(CASE WHEN estado = 'cerrada' THEN 1 ELSE 0 END) AS mesas_cerradas,
            SUM(cobro) AS ingresos_totales_mesas
        FROM mesas
        WHERE fecha_baja >= CURDATE() - INTERVAL 30 DAY
        ";
        $resultado_mesas = $objAccesoDatos->prepararConsulta($consulta_mesas);
        $resultado_mesas->execute();
        $estadisticas_mesas = $resultado_mesas->fetch(PDO::FETCH_ASSOC);

        return [
            'pedidos' => $estadisticas_pedidos,
            'mesas' => $estadisticas_mesas
        ];
    }
    public static function obtenerUsuario($id_usuario)
    {
        try
        {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();                        
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE id_usuario = :id_usuario");
            $consulta->bindValue(':id_usuario', $id_usuario, PDO::PARAM_STR);
            $consulta->execute();

            $usuario = $consulta->fetchObject('Usuario');

            if (!$usuario) {
                throw new Exception("No se ha encontrado el usuario con ID $id_usuario");
            }

            return $usuario;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (nombre, clave, rol, fecha_baja, sueldo) 
                                                        VALUES (:nombre, :clave, :rol, :fecha_baja, :sueldo)"); 

        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave);
        $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
        $consulta->bindValue(':sueldo', $this->sueldo, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_baja', $this->fecha_baja, PDO::PARAM_STR);
        
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function modificarUsuario($user)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET nombre = :nombre, clave = :clave,
                                                            fecha_baja = :fecha_baja, rol = :rol, sueldo = :sueldo 
                                                            WHERE id_usuario = :id_usuario");
        $consulta->bindValue(':nombre', $user->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $user->clave, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_baja', $user->fecha_baja, PDO::PARAM_STR);
        $consulta->bindValue(':rol', $user->rol, PDO::PARAM_STR);
        $consulta->bindValue(':sueldo', $user->sueldo, PDO::PARAM_INT);

        $consulta->bindValue(':id_usuario', $user->id_usuario, PDO::PARAM_INT);

        $consulta->execute();
    }
    
    public static function borrarUsuario($id_usuario)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fecha_baja = :fecha_baja WHERE id_usuario = :id_usuario");
        $fecha = new DateTime(date("Y-m-d"));
        $consulta->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_baja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
        echo "Usuario borrado correctamente";
    }

    public static function obtenerPorClave($usuario, $clave)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("SELECT id_usuario, nombre, clave,fecha_baja, rol, sueldo FROM usuarios WHERE clave = :clave AND nombre = :nombre");
        $consulta->bindValue(':nombre', $usuario, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $clave, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchObject('Usuario');
    }

    public static function ExportarPDF($path = "./usuarios.pdf")
    {
        $pdf = new PDF();
        $pdf->AddPage();

        $usuarios = Usuario::obtenerTodos();

       
        foreach ($usuarios as $usuario) {
            $pdf->ChapterTitle($usuario->nombre, $usuario->sueldo);
            $pdf->ChapterBody("Rol: " . $usuario->rol);
            $pdf->Ln();
        }

        $pdf->Output($path, 'D');
    }
    public static function ExportarPDFLOGO($path = "./logo.pdf")
    {
        $pdf = new PDF();
        $pdf->AddPage();
        $logo = "./utilities/logo.jpg";
        $pdf->Image($logo, 10, 10, 50);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Logo del restoran', 0, 1, 'C');
        $pdf->Output($path, 'D');
    }
    
}
?>
