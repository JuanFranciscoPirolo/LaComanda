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

$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{id_usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
    $group->put('[/]', \UsuarioController::class . ':ModificarUno')->add(\AuthUsuarios::class . ':ValidarCampos');
    $group->delete('[/]', \UsuarioController::class . ':BorrarUno')->add(new AuthMiddleware("usuario"));
});

$app->group('/productos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->get('/{id_producto}', \ProductoController::class . ':TraerUno');
    $group->post('[/]', \ProductoController::class . ':CargarUno');
    $group->put('[/]', \ProductoController::class . ':ModificarUno')->add(\AuthProductos::class . ':ValidarRol');
    $group->delete('[/]', \ProductoController::class . ':BorrarUno')->add(\AuthProductos::class . ':ValidarRol');
});

$app->group('/mesas', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->get('/{id_mesa}', \MesaController::class . ':TraerUno');
    $group->post('[/]', \MesaController::class . ':CargarUno')->add(\AuthUsuarios::class.':ValidarPermisosDeRol');
    $group->put('[/]', \MesaController::class . ':ModificarUno')->add(\AuthMesas::class.':ValidarMesa');
    $group->delete('[/]', \MesaController::class . ':BorrarUno')->add(\AuthMesas::class.':ValidarMesa');

});
$app->group('/pedidos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \PedidoController::class . ':TraerTodos');
    $group->get('/{id_pedido}', \PedidoController::class . ':TraerUno');
    $group->post('[/]', \PedidoController::class . ':CargarUno');
    $group->put('[/]', \PedidoController::class . ':ModificarUno');
    $group->delete('[/]', \PedidoController::class . ':BorrarUno');
});
$app->get('/', function (Request $request, Response $response, array $args) 
{
	$response->getBody()->write("Funciona!");
    return $response;
});

$app->get("/test", function(Request $request, Response $response, array $args)
{
    $params = $request->getQueryParams();
    $response->getBody()->write(json_encode($params));
    return $response;
});

$app->run();
?>