<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Servicios

    //Obtener todos los servicios
    $app->get("/servicios", function (Request $request, Response $response, array $args) {
        $respuesta = dbGet("SELECT * FROM servicios ORDER BY descripcion");
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });
?>