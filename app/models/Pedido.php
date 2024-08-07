<?php
class Pedido {
    public $id_pedido;
    public $nombre_cliente;
    public $tiempo_estimado;
    public $precio_final;
    public $fecha_baja;
    public $codigo_mesa;
    public $productos;
    public $estado; 
    public $foto;

    public static function cambiarEstadoAPreparacion($id_pedido, $tiempo_preparacion) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "UPDATE pedidos 
            SET estado = 'en preparación', 
                tiempo_estimado = :tiempo_preparacion
            WHERE id_pedido = :id_pedido"
        );
        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':tiempo_preparacion', $tiempo_preparacion, PDO::PARAM_STR);

        return $consulta->execute();
    }

    public static function cambiarEstadoListoParaServir($codigoPedido, $rol)
    {
    $estado = 'listo para servir';

    $query = "UPDATE productos_pedido SET estado = :estado 
              WHERE codigo_pedido = :codigo_pedido AND rol = :rol";

    $objetoAccesoDato = AccesoDatos::obtenerInstancia();
    $consulta = $objetoAccesoDato->prepararConsulta($query);
    $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
    $consulta->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_STR);
    $consulta->bindValue(':rol', $rol, PDO::PARAM_STR);
    $consulta->execute();

    return $consulta->rowCount();
    }

    public static function obtenerPedidosNoEntregadosATiempo() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT *,
                TIMESTAMPDIFF(MINUTE, DATE_SUB(NOW(), INTERVAL TIME_TO_SEC(tiempo_estimado) SECOND), NOW()) AS tiempo_real,
                TIME_TO_SEC(tiempo_estimado) / 60 AS tiempo_estimado_minutos
            FROM pedidos
            WHERE fecha_baja IS NOT NULL
            AND TIMESTAMPDIFF(MINUTE, DATE_SUB(NOW(), INTERVAL TIME_TO_SEC(tiempo_estimado) SECOND), NOW()) > (TIME_TO_SEC(tiempo_estimado) / 60)"
        );
        
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }
    

    
    
    public static function actualizarFoto($codigo_mesa, $foto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "UPDATE pedidos SET foto = :foto WHERE codigo_mesa = :codigo_mesa"
        );
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $foto, PDO::PARAM_STR);
        $consulta->execute();
    
        return $consulta->rowCount(); 
    }
    
    

    public static function obtenerTodos() 
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
        $consulta->execute();
        $pedidos = $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');

        foreach ($pedidos as $pedido) {
            $pedido->productos = self::obtenerDetalles($pedido->id_pedido);
        }

        return $pedidos;
    }

    public static function obtenerPedido($codigo_mesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE codigo_mesa = :codigo_mesa ORDER BY id_pedido DESC LIMIT 1");
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_INT);
        $consulta->execute();
        $pedido = $consulta->fetchObject('Pedido');
    
        if ($pedido) {
            $pedido->productos = self::obtenerDetalles($pedido->id_pedido);
            return $pedido;
        }
    
        return null;
    }

    public static function obtenerDetalles($id_pedido) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM detalle_pedido WHERE id_pedido = :id_pedido");
        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearPedido() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "INSERT INTO pedidos (nombre_cliente, tiempo_estimado, precio_final, fecha_baja, codigo_mesa, estado) 
            VALUES (:nombre_cliente, :tiempo_estimado, :precio_final, :fecha_baja, :codigo_mesa, :estado)"
        ); 
        $consulta->bindValue(':nombre_cliente', $this->nombre_cliente, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_STR);
        $consulta->bindValue(':precio_final', $this->precio_final, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_baja', $this->fecha_baja, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':estado', 'pendiente', PDO::PARAM_STR); 

        $consulta->execute();

        $id_pedido = $objAccesoDatos->obtenerUltimoId();

        foreach ($this->productos as $producto) {
            self::crearDetallePedido($id_pedido, $producto['id_producto'], $producto['cantidad']);
        }

        return $id_pedido;
    }

    public static function crearDetallePedido($id_pedido, $id_producto, $cantidad) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad) 
            VALUES (:id_pedido, :id_producto, :cantidad)"
        );
        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
        $consulta->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);

        $consulta->execute();
    }

    public static function modificarPedido($pedido) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "UPDATE pedidos 
            SET nombre_cliente = :nombre_cliente, 
                tiempo_estimado = :tiempo_estimado, 
                precio_final = :precio_final, 
                fecha_baja = :fecha_baja,
                codigo_mesa = :codigo_mesa,
                estado = :estado 
            WHERE id_pedido = :id_pedido"
        );

        $consulta->bindValue(':id_pedido', $pedido->id_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':nombre_cliente', $pedido->nombre_cliente, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado', $pedido->tiempo_estimado, PDO::PARAM_STR);
        $consulta->bindValue(':precio_final', $pedido->precio_final, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_baja', $pedido->fecha_baja, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_mesa', $pedido->codigo_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $pedido->estado, PDO::PARAM_STR);

        return $consulta->execute();
    }


    public static function borrarPedido($codigo_mesa) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "UPDATE pedidos 
            SET fecha_baja = NOW(), 
                estado = 'entregado' 
            WHERE codigo_mesa = :codigo_mesa"
        );
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_INT);
        $resultado = $consulta->execute();
        
        if ($resultado) {
            echo "Pedido borrado correctamente";
        } else {
            echo "Error al borrar el pedido";
        }
        
        return $resultado;
    }
    

    public static function cambiarEstadoTodosPedidos($estadoNuevo) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "UPDATE pedidos SET estado = :estadoNuevo"
        );
        $consulta->bindValue(':estadoNuevo', $estadoNuevo, PDO::PARAM_STR);
        
        return $consulta->execute();
    }

    public static function obtenerPedidosListos() {
        $estado = "Listo para servir";
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT * FROM pedidos WHERE estado = :estado"
        );
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    
    
    
    
}
