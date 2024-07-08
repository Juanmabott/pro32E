<?php

use Poo\Cd;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

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

// Grupo de rutas para json_bd
$app->group('/json_bd', function(RouteCollectorProxy $group) {
    $group->get('/', \Poo\Cd::class . ':TraerTodos');
    $group->post('/create', \Poo\Cd::class . ':Crear');
    $group->put('/update/{id}', \Poo\Cd::class . ':Actualizar');
    $group->delete('/delete/{id}', \Poo\Cd::class . ':Borrar');
});

// Ejecutar la aplicación
$app->run();
?>
