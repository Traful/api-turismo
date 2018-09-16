<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Tipos de Valorizaciones y Sub Tipos

    //Obtener todos los Tipos de Valorizaciones
    $app->get("/valorizaciones/tipos", function (Request $request, Response $response, array $args) {
        $respuesta = dbGet("SELECT * FROM tiposcategorias ORDER BY descripcion");
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Obtener todos los Sub Tipos de Valorizaciones por Tipo de Valorizacion
    $app->get("/valorizaciones/tipo/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT id, descripcion FROM valortipcat";
        $xSQL .= " WHERE idtipcat = " . $args["id"];
        $xSQL .= " ORDER BY descripcion";
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });
?>