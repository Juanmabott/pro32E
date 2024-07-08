<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Slim\Psr7\Response as ResponseMW;



interface IMiddleware {
    public function ValidarParametrosUsuario(Request $request, RequestHandler $RequestHandler);
    public function ChequearJWT(Request $request, RequestHandler $RequestHandler);


}