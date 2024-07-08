<?php
use FastRoute\RouteCollector;
use Poo\Cd;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

// Asegúrate de incluir el archivo que contiene la clase Verificadora
require_once __DIR__ . '/../src/poo/Verificadora.php';


// Crear la aplicación
$app = AppFactory::create();

// Habilitar modo de depuración
$app->addErrorMiddleware(true, true, true);

// Ruta raíz
$app->get('/', function (Request $request, Response $response, array $args): Response {
    $datos = new stdClass();
    $datos->mensaje = "API => GET";
    $response->getBody()->write(json_encode($datos));
    return $response->withHeader('Content-Type', 'application/json');
});


// Ruta de login
$app->post('/login', \Verificadora::class . ':VerificarUsuario')->add(\Verificadora::class . ':ValidarParametrosUsuario');
$app->get('/login/test', \Verificadora::class . ':ObtenerDataJWT')->add(\Verificadora::class . ':ChequearJWT');

require_once __DIR__ . '/../src/clases/cd.php';



$app->group('/json_bd',function(RouteCollectorProxy $grupo)
{
    $grupo->get('/', Cd::class . ':TraerTodos');
});
// Ejecutar la aplicación
$app->run();
