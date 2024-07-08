<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;

require_once __DIR__ . "/./autentificadora.php";
require_once __DIR__ . "/../clases/Usuario.php";
require_once __DIR__ . "/./imiddleware.php";


class Verificadora implements IMiddleware 
{
    public function VerificarUsuario(Request $request, Response $response): Response
    {
        // Obtener el cuerpo de la solicitud
        $parsedBody = json_decode($request->getBody()->getContents(), true);

        $objJson = $parsedBody['obj_json'];
        $correo = $objJson['correo'];
        $clave = $objJson['clave'];

        /*
        $correo =$request->getHeader("correo")[0];
        $clave = $request->getHeader("clave")[0];
        */
        /*
        // Verificar que los parámetros no estén vacíos
        if (empty($correo) && empty($clave)) {
            $response->getBody()->write(json_encode(['error' => 'Faltan clave y correo']));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            
        } else if (empty($correo)) {
            $response->getBody()->write(json_encode(['error' => 'Faltan correo']));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        */

        $usuario = Usuario::ValidarUsu($correo, $clave);

        if (!$usuario) {
            //echo "\n USUARIO no encontrado:".$usuario;

            $responseData = [
                'token_jwt' => null // Incluir el token JWT en la respuesta
            ];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }
        $usuario = Usuario::TraerUno(['correo'=>$correo,'clave'=>$clave]);
        // Crear JWT
        $jwt = Autentificadora::crearJWT($usuario); // Suponiendo que $usuario->id sea la información que quieres incluir en el JWT

        // Preparar la respuesta JSON con el JWT y un mensaje
        $responseData = [
            'mensaje' => 'Usuario verificado',
            'token_jwt' => $jwt // Incluir el token JWT en la respuesta
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
   
    public function ValidarParametrosUsuario(Request $request, RequestHandler $handler): Response
    {
        $body = $request->getBody();

        $parsedBody = json_decode($body, true); // Decodificar JSON a un array asociativo

        $response = new ResponseMW();

        if (!isset($parsedBody['obj_json'])) {
            $responseData = ['error' => 'Falta el parámetro obj_json'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $objJson = $parsedBody['obj_json'];

        if (!isset($objJson['correo']) && !isset($objJson['clave'])) {
            $responseData = ['error' => 'Falta el atributo correo y clave'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }
        if (!isset($objJson['correo'])) {
            $responseData = ['error' => 'Falta el atributo correo'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        if (!isset($objJson['clave'])) {
            $responseData = ['error' => 'Falta el atributo clave'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // Si todos los parámetros están presentes y correctos, proceder con la solicitud
        return $handler->handle($request);
    }

    public function ObtenerDataJWT($request, $response, $next)
    {
        $token = $request->getHeaderLine('token');
        if (!isset($token))
        {
            
            $responseData = ['error' => 'Falta el atributo token'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }
        $payload = Autentificadora::obtenerPayLoad($token);
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    }
    public function ChequearJWT(Request $request, RequestHandler $handler) 
    {
        $token = $request->getHeaderLine('token');
        //echo "token:".$token;
        $response = new ResponseMW();
      
        $datos = Autentificadora::verificarJWT($token);
        if($datos->verificado == false)
        {
            $responseData = [ 'Exito'=>$datos->verificado , 'Mensaje'=> $datos->mensaje];
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }
        return $handler->handle($request);

    }
   



}
