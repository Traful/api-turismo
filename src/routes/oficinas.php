<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Oficinas de Turísmo

    //[GET]

    //Todas las Oficinas Turísticas
    $app->get("/oficinas", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT * FROM oficinas";
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Una Oficina en particular
    $app->get("/oficina/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT * FROM oficinas WHERE id = " . $args["id"];
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Todas las Oficinas de una Localidad
    $app->get("/oficinas/localidad/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT * FROM oficinas WHERE idlocalidad = " . $args["id"];
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //[PATCH]

    //Actualizar los datos de una Oficina
    $app->patch("/oficina/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "id" => array(
                "mayorcero" => true, 
                "numeric" => true,
                "tag" => "Identificador de Oficina"
            ),
            "idlocalidad" => array(
                "mayorcero" => true, 
                "numeric" => true,
                "tag" => "Identificador de Localidad"
            ),
            "domicilio" => array(
                "max" => 50, 
                "tag" => "Domicilio"
            ),
            "telefono" => array(
                "max" => 20, 
                "tag" => "Número de Teléfono"
            ),
            "interno" => array(
                "max" => 15, 
                "tag" => "Número de interno"
            ),
            "mail" => array(
                "max" => 100,
                "tag" => "Email"
            ),
            "web" => array(
                "max" => 100,
                "tag" => "Página Web"
            ),
            "responsable" => array(
                "max" => 100,
                "tag" => "Datos del Responsable"
            ),
            "latitud" => array(
                "max" => 25,
                "tag" => "Latitud"
            ),
            "longitud" => array(
                "max" => 25,
                "tag" => "Longitud"
            ),
            "latitudg" => array(
                "max" => 25,
                "tag" => "Latitud º"
            ),
            "longitudg" => array(
                "max" => 25,
                "tag" => "Longitud º"
            )
        );
        $validar = new Validate();
        $parsedBody = $request->getParsedBody();
        if($validar->validar($parsedBody, $reglas)) {
            $respuesta = dbPatchWithData("oficinas", $parsedBody["id"], $parsedBody);
            return $response
                ->withStatus(200) //Ok
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        } else {
            $resperr = new stdClass();
            $resperr->err = true;
            $resperr->errMsg = "Hay errores en los datos suministrados";
            $resperr->errMsgs = $validar->errors();
            return $response
                ->withStatus(409) //Conflicto
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    });

    //[POST]

    //Agregar una Oficina a una Localidad en particular
    $app->post("/oficina/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "idlocalidad" => array(
                "mayorcero" => true, 
                "numeric" => true,
                "tag" => "Identificador de Localidad"
            ),
            "domicilio" => array(
                "max" => 50, 
                "tag" => "Domicilio"
            ),
            "telefono" => array(
                "max" => 20, 
                "tag" => "Número de Teléfono"
            ),
            "interno" => array(
                "max" => 15, 
                "tag" => "Número de interno"
            ),
            "mail" => array(
                "max" => 100,
                "tag" => "Email"
            ),
            "web" => array(
                "max" => 100,
                "tag" => "Página Web"
            ),
            "responsable" => array(
                "max" => 100,
                "tag" => "Datos del Responsable"
            ),
            "latitud" => array(
                "max" => 25,
                "tag" => "Latitud"
            ),
            "longitud" => array(
                "max" => 25,
                "tag" => "Longitud"
            ),
            "latitudg" => array(
                "max" => 25,
                "tag" => "Latitud º"
            ),
            "longitudg" => array(
                "max" => 25,
                "tag" => "Longitud º"
            )
        );
        $validar = new Validate();
        $parsedBody = $request->getParsedBody();
        if($validar->validar($parsedBody, $reglas)) {
            //Elimino la key id que viene en 0
            unset($parsedBody["id"]);
            $respuesta = dbPostWithData("oficinas", $parsedBody);
            return $response
                ->withStatus(200) //Ok
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        } else {
            $resperr = new stdClass();
            $resperr->err = true;
            $resperr->errMsg = "Hay errores en los datos suministrados";
            $resperr->errMsgs = $validar->errors();
            return $response
                ->withStatus(409) //Conflicto
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    });

    //[DELETE]

    //Eliminar los datos de una Oficina
    $app->delete("/oficina/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $respuesta = dbDelete("oficinas", $args["id"]);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });
?>