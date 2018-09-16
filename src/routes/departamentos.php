<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Departamentos

    //Todos los Departamentos
    $app->get("/departamentos", function (Request $request, Response $response, array $args) {
        $respuesta = dbGet("SELECT id, nombre FROM departamentos ORDER BY nombre");
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Todos los Departamentos de una determinada Provincia
    /*
        Si bien solo se trabaja en la Provincia de San Luis, el sistema esta preparado para operar en una o más provincias
        Incluso en otros países en donde la división sea provincial y departamental, para el uso actual del sistema
        este llamado es obsoleto.
    */
    $app->get("/departamentos/provincia/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $respuesta = dbGet("SELECT id, nombre FROM departamentos WHERE idprovincia = " . $args["id"] . " ORDER BY nombre");
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Actualizar los datos de un Departamento
    $app->patch("/departamento/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        return $response
            ->withStatus(503)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode(array("Mensaje" => "Service Unavailable (En producción!)"), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    /*
        Hasta el momento (14/08/2018) está cargada la totalidad de los Departamentos de la Provincia de San Luis
        por lo tanto no es necesario agregar nuevos, y por cuestiones relacionales NO SE DEBEN ELIMINAR
    */
?>