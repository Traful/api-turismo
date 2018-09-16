<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Tarifas

    //Obtener todas las Tarifas
    $app->get("/tarifas", function (Request $request, Response $response, array $args) {
        $respuesta = dbGet("SELECT * FROM tipo_tarifas ORDER BY descripcion");
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });
?>