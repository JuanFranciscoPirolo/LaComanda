<?php
// Error Handling
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
 require_once './Middleware/Logger.php';
use Dotenv\Dotenv;


require_once '../vendor/autoload.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './DataBase/AccesoDatos.php';

// Load ENV
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();


$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();

$app->group('/login', function (RouteCollectorProxy $group){
    $group->post('[/]', \UsuarioController::class . ':LogIn')->add(\Logger::class . ':ValidarLogin');
});

$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{id_usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
    $group->put('[/]', \UsuarioController::class . ':ModificarUno')->add(\AuthUsuarios::class . ':ValidarCampos');
    $group->delete('[/]', \UsuarioController::class . ':BorrarUno')->add(new AuthMiddleware("usuario"));
})->add(ValidarToken::class. ':ValidarSocio');

$app->group('/productos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->get('/{id_producto}', \ProductoController::class . ':TraerUno');
    $group->post('[/]', \ProductoController::class . ':CargarUno')->add(\AuthProductos::class. ':ValidarCampos');
    $group->put('[/]', \ProductoController::class . ':ModificarUno')->add(\AuthProductos::class . ':ValidarRol');
    $group->delete('[/]', \ProductoController::class . ':BorrarUno')->add(\AuthProductos::class . ':ValidarRol');
    $group->post('/upload-csv', \ProductoController::class . ':UploadCSV');
})->add(ValidarToken::class. ':ValidarSocio');

$app->get('/productos-csv/download', \ProductoController::class . ':DownloadCSV');

$app->group('/archivos', function (RouteCollectorProxy $group) {
    $group->get('/PDF', \UsuarioController::class . ':DescargarPDF');
});
$app->group('/mesas', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->get('/{id_mesa}', \MesaController::class . ':TraerUno');
    $group->post('[/]', \MesaController::class . ':CargarUno')->add(\AuthMesas::class. ':ValidarMozoExistente'); //->add(\AuthMesas::class. ':validarCampos');
    $group->put('[/]', \MesaController::class . ':ModificarUno')->add(\AuthMesas::class.':ValidarMesa');
    $group->delete('[/]', \MesaController::class . ':BorrarUno')->add(\AuthMesas::class.':ValidarMesa');
});
$app->group('/pedidos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \PedidoController::class . ':TraerTodos');//ValidarMesaCodigoMesa
    $group->get('/{codigo_pedido}', \PedidoController::class . ':TraerUno');
    $group->post('[/]', \PedidoController::class . ':CargarUno')->add(\AuthMesas::class.':ValidarMesaCodigoMesa');
    $group->put('[/]', \PedidoController::class . ':ModificarUno');
    $group->delete('[/]', \PedidoController::class . ':BorrarUno');
});

$app->run();
?>