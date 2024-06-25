<?php
class Pedido {
    public $id_pedido;
    public $nombre_cliente;
    public $tiempo_estimado;
    public $precio_final;
    public $fecha_baja;
    public $codigo_mesa; // Nuevo campo agregado
    public $productos; // Suponiendo que $productos sigue siendo un array asociativo como antes

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
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE codigo_mesa = :codigo_mesa");
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
            "INSERT INTO pedidos (nombre_cliente, tiempo_estimado, precio_final, fecha_baja, codigo_mesa) 
            VALUES (:nombre_cliente, :tiempo_estimado, :precio_final, :fecha_baja, :codigo_mesa)"
        ); 
        $consulta->bindValue(':nombre_cliente', $this->nombre_cliente, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_STR);
        $consulta->bindValue(':precio_final', $this->precio_final, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_baja', $this->fecha_baja, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_INT); // Bind del nuevo campo

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
                codigo_mesa = :codigo_mesa 
            WHERE id_pedido = :id_pedido"
        );

        $consulta->bindValue(':id_pedido', $pedido->id_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':nombre_cliente', $pedido->nombre_cliente, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado', $pedido->tiempo_estimado, PDO::PARAM_STR);
        $consulta->bindValue(':precio_final', $pedido->precio_final, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_baja', $pedido->fecha_baja, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_mesa', $pedido->codigo_mesa, PDO::PARAM_INT); // Bind del nuevo campo

        return $consulta->execute();
    }

    public static function borrarPedido($codigo_mesa) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "UPDATE pedidos SET fecha_baja = NOW() WHERE codigo_mesa = :codigo_mesa"
        );
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_INT);
        echo "Pedido borrado correctamente";
        return $consulta->execute();
    }
    
}
