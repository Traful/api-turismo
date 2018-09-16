<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Tipos de Alojamiento

    //Obtener todos los Tipos
    $app->get("/tipos", function (Request $request, Response $response, array $args) {
        $respuesta = dbGet("SELECT * FROM tipos ORDER BY descripcion");
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Editar un Tipo en particular
    $app->patch("/tipo/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "idtipcat" => array(
                "numeric" => true,
                "mayorcero" => true,
                "tag" => "Identificador de Tipo de Categoría"
            ),
            "descripcion" => array(
                "min" => 3,
                "max" => 50,
                "tag" => "Descripción"
            )
        );
        $respuesta = dbPatch("tipos", $args["id"], $request->getParsedBody(), $reglas);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });    
?>