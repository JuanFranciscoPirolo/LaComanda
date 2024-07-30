<?php

error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as ResponseMw;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
//require_once './middlewares/Auth.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;
require './Middleware/AuthUsuarios.php';
require './Middleware/AuthProductos.php';
require './Middleware/AuthMiddleware.php';
require './Middleware/AuthMesas.php';


 require_once './Middleware/AuthJWT.php';
 require_once './Middleware/ValidarSocio.php';
 require_once './Middleware/ValidarToken.php';
 require_once './Middleware/ValidarMozo.php';
 require_once './Middleware/Logger.php';
use Dotenv\Dotenv;


require_once '../vendor/autoload.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './DataBase/AccesoDatos.php';


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();


$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();

$app->group('/login', function (RouteCollectorProxy $group){
    $group->post('[/]', \UsuarioController::class . ':LogIn')->add(\Logger::class . ':ValidarLogin');
});

$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('/exportar-pdf-logo', \UsuarioController::class . ':DescargarPDFLOGO')->add(ValidarToken::class. ':ValidarSocio');
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{id_usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
    $group->put('[/]', \UsuarioController::class . ':ModificarUno')->add(\AuthUsuarios::class . ':ValidarCampos');
    $group->delete('[/]', \UsuarioController::class . ':BorrarUno')->add(new AuthMiddleware("usuario"));
});

$app->group('/productos', function (RouteCollectorProxy $group) 
{   
    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->post('[/alta]', \ProductoController::class . ':CargarUno');
    $group->put('[/]', \ProductoController::class . ':ModificarUno')->add(\AuthProductos::class . ':ValidarRol');
    $group->delete('[/]', \ProductoController::class . ':BorrarUno')->add(\AuthProductos::class . ':ValidarRol');
    $group->post('/upload-csv', \ProductoController::class . ':UploadCSV');
    $group->get('/listarProductos', \ProductoController::class . ':ListarProductosEmpleado');
    $group->get('/{id_producto}', \ProductoController::class . ':TraerUno');
});

$app->get('/productos-csv/download', \ProductoController::class . ':DescargarCSV');

$app->group('/archivos', function (RouteCollectorProxy $group) {
    $group->get('/PDF', \UsuarioController::class . ':DescargarPDF');
});

$app->group('/mesas', function (RouteCollectorProxy $group) 
{
    $group->get('/mejores-comentarios', \MesaController::class . ':ObtenerComentarios')->add(ValidarToken::class. ':ValidarSocio');
    $group->get('/mesa-mas-usada', \MesaController::class . ':ObtenerMesaUsada')->add(ValidarToken::class. ':ValidarSocio');
    $group->get('/cobro/{codigo}', \MesaController::class . ':ObtenerCobro')->add(ValidarToken::class. ':ValidarMozo');
    $group->get('[/]', \MesaController::class . ':TraerTodos')->add(ValidarToken::class. ':ValidarSocio');
    $group->get('/{id_mesa}', \MesaController::class . ':TraerUno');
    $group->post('/alta', \MesaController::class . ':CargarUno')->add(\AuthMesas::class. ':ValidarMozoExistente');
    $group->put('[/]', \MesaController::class . ':ModificarUno')->add(\AuthMesas::class.':ValidarMesa');
    $group->delete('[/]', \MesaController::class . ':BorrarUno')->add(\AuthMesas::class.':ValidarMesa')->add(ValidarToken::class. ':ValidarSocio');
    $group->post('/encuesta', \MesaController::class . ':PublicarEncuesta'); 
    
});



$app->group('/pedidos', function (RouteCollectorProxy $group) 
{
    $group->post('/cambiarEstadoPreparacion', \PedidoController::class . ':cambiarEstadoAPreparacionn');
    $group->get('/fuera-de-tiempo', \PedidoController::class . ':listarPedidosNoEntregadosATiempo')->add(ValidarToken::class. ':ValidarSocio');
    $group->get('/obtenerPedidosListos', \PedidoController::class . ':listarPedidosListos')->add(ValidarToken::class. ':ValidarMozo');
    $group->get('/listarPedidos', \PedidoController::class . ':listarPedidos')->add(ValidarToken::class. ':ValidarSocio');
    $group->get('[/]', \PedidoController::class . ':TraerTodos');
    $group->get('/{codigo_pedido}', \PedidoController::class . ':TraerUno');
    $group->post('[/alta]', \PedidoController::class . ':CargarUno')->add(\AuthMesas::class.':ValidarMesaCodigoMesa')->add(ValidarToken::class. ':ValidarMozo');
    $group->put('[/]', \PedidoController::class . ':ModificarUno');
    $group->delete('[/]', \PedidoController::class . ':BorrarUno');
    $group->get('/demora/{codigo_mesa}/{id_pedido}', \PedidoController::class . ':obtenerDemora');
    $group->post('/actualizarFoto', \PedidoController::class . ':ActualizarFotos');
    
    
    
});


$app->post('/cambiarEstado', function ($request, $response, $args) {
    $estadoNuevo = 'Listo para servir'; 
    $resultado = Pedido::cambiarEstadoTodosPedidos($estadoNuevo);

    $payload = json_encode(array("mensaje" => $resultado ? "Estado cambiado a Listo para servir" : "Error al cambiar el estado"));
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/estadisticas', \UsuarioController::class . ':ObtenerEstadisticass');


$app->run();
?>