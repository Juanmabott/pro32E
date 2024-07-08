<?php

namespace Poo;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Cd
{
    public static function traerTodosLosCd()
    {
        echo "asd";

        $objetoAccesoDato = AccesoDatosCD::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->retornarConsulta("SELECT id, titel AS titulo, interpret AS interprete, jahr AS anio FROM cds");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function TraerTodos(Request $request, Response $response, array $args): Response
    {
        $todos = self::traerTodosLosCd();
        $response->getBody()->write(json_encode($todos));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function Crear(Request $request, Response $response, array $args): Response
    {
        $parsedBody = $request->getParsedBody();
        $titulo = $parsedBody['titulo'];
        $interprete = $parsedBody['interprete'];
        $anio = $parsedBody['anio'];
        $objetoAccesoDato = AccesoDatosCD::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->retornarConsulta("INSERT INTO cds (titel, interpret, jahr) VALUES (:titulo, :interprete, :anio)");
        $consulta->bindValue(':titulo', $titulo, PDO::PARAM_STR);
        $consulta->bindValue(':interprete', $interprete, PDO::PARAM_STR);
        $consulta->bindValue(':anio', $anio, PDO::PARAM_INT);
        $consulta->execute();

        $response->getBody()->write(json_encode(['Mensaje' => 'CD creado correctamente']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function Actualizar(Request $request, Response $response, array $args): Response
    {
        $parsedBody = $request->getParsedBody();
        $id = $args['id'];
        $titulo = $parsedBody['titulo'];
        $interprete = $parsedBody['interprete'];
        $anio = $parsedBody['anio'];
        $objetoAccesoDato = AccesoDatosCD::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->retornarConsulta("UPDATE cds SET titel = :titulo, interpret = :interprete, jahr = :anio WHERE id = :id");
        $consulta->bindValue(':titulo', $titulo, PDO::PARAM_STR);
        $consulta->bindValue(':interprete', $interprete, PDO::PARAM_STR);
        $consulta->bindValue(':anio', $anio, PDO::PARAM_INT);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        $response->getBody()->write(json_encode(['Mensaje' => 'CD actualizado correctamente']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function Borrar(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $objetoAccesoDato = AccesoDatosCD::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->retornarConsulta("DELETE FROM cds WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        $response->getBody()->write(json_encode(['Mensaje' => 'CD eliminado correctamente']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}