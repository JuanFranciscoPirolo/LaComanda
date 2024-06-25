<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto implements IApiUsable
{
    public function TraerUno($request, $response, $args)
    {
        $id_producto = $args['id_producto'];
        $producto_final = Producto::obtenerProducto($id_producto);
        $payload = json_encode($producto_final);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::obtenerTodos();
        $payload = json_encode(array("listaProducto" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        
        $precio = $parametros['precio'];
        $tipo = $parametros['tipo'];
        $sector = $parametros['sector'];

        $producto = new Producto();
        $producto->precio = $precio;
        $producto->tipo = $tipo;
        $producto->sector = $sector;

        $producto->crearProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        // Validar que todos los campos necesarios están presentes
        $required_fields = ['precio', 'tipo', 'sector','id_producto'];
        foreach ($required_fields as $field) 
        {
            if (!isset($data[$field])) 
            {
                http_response_code(400); // Bad Request
                header('Content-Type: application/json');
                echo json_encode(["mensaje" => "Faltan datos en la solicitud: $field."]);
                return;
            }
        }

        $producto = Producto::obtenerProducto($data['id_producto']);

        $producto->precio = $data['precio'];
        $producto->tipo = $data['tipo'];
        $producto->sector = $data['sector'];
        $producto->fecha_baja = $data['fecha_baja'];

        Producto::modificarProducto($producto);

        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        if ($_SERVER["REQUEST_METHOD"] == "DELETE")
        {
            // Leer datos de la solicitud DELETE
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['id_producto'])) 
            {
                http_response_code(400); // Bad Request
                header('Content-Type: application/json');
                echo json_encode(["mensaje" => "Falta el número de pedido en la solicitud."]);
                return;
            }

            $productoId = $data['id_producto'];
            Producto::borrarProducto($productoId);

            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function UploadCSV($request, $response, $args)
    {
        $uploadedFiles = $request->getUploadedFiles();
        $csvFile = $uploadedFiles['csv'];

        if ($csvFile->getError() === UPLOAD_ERR_OK) {
            $filename = $csvFile->getClientFilename();
            $csvData = $csvFile->getStream()->getContents();

            // Procesar el CSV
            $data = $this->processCSV($csvData);

            // Insertar datos en la base de datos
            $this->insertDataIntoDatabase($data);

            $payload = json_encode(['message' => 'Archivo subido y procesado correctamente']);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $payload = json_encode(['error' => 'Error al subir el archivo']);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    private function processCSV($csvData)
    {
        $rows = explode(PHP_EOL, $csvData);
        $data = [];

        foreach ($rows as $row) {
            $data[] = str_getcsv($row);
        }

        return $data;
    }

    private function insertDataIntoDatabase($data)
    {
        foreach ($data as $row) {
            if (count($row) < 3) continue; // Saltar filas incompletas

            $producto = new Producto();
            $producto->tipo = $row[0];
            $producto->sector = $row[1];
            $producto->precio = $row[2];

            $producto->crearProducto();
        }
    }

    public function DownloadCSV($request, $response, $args)
    {
        // Obtener todos los productos de la base de datos
        $productos = Producto::obtenerTodos();
    
        // Crear el contenido del CSV
        $csvContent = $this->generateCSVContent($productos);
    
        // Enviar el contenido del CSV como descarga al cliente
        $response = $response->withHeader('Content-Type', 'text/csv')
                             ->withHeader('Content-Disposition', 'attachment; filename="productosBDD.csv"')
                             ->withHeader('Pragma', 'no-cache')
                             ->withHeader('Expires', '0');
        
        $response->getBody()->write($csvContent);
    
        return $response;
    }
    
    
    private function generateCSVContent($productos)
    {
        $csvContent = "id_producto,tipo,sector,precio" . PHP_EOL;
    
        foreach ($productos as $producto) {
            $csvContent .= "{$producto->id_producto},{$producto->tipo},{$producto->sector},{$producto->precio}" . PHP_EOL;
        }
    
        return $csvContent;
    }
    
    
}