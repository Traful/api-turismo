<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Ciudades

    //Todas los Atractivos de una Ciudad (Loclidad)
    $app->get("/ciudad/{id:[0-9]+}/atractivos", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT * FROM atractivos";
        $xSQL .= " WHERE atractivos.idlocalidad = " . $args["id"];
        $xSQL .= " ORDER BY atractivos.nombre";
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Todas las Ciudades
    $app->get("/ciudades", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT ciudades.id, ciudades.nombre, departamentos.nombre as departamento FROM ciudades";
        $xSQL .= " INNER JOIN departamentos ON ciudades.iddepartamento = departamentos.id";
        $xSQL .= " ORDER BY ciudades.nombre";
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Una Ciudad en particular
    $app->get("/ciudad/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT ciudades.*, departamentos.nombre as departamento FROM ciudades";
        $xSQL .= " INNER JOIN departamentos ON ciudades.iddepartamento = departamentos.id";
        $xSQL .= " WHERE ciudades.id = " . $args["id"];
        $xSQL .= " ORDER BY ciudades.nombre";
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Todas los Ciudades de una determinado Departamento
    $app->get("/ciudades/departamento/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $respuesta = dbGet("SELECT id, nombre FROM ciudades WHERE iddepartamento = " . $args["id"]. " ORDER BY nombre");
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Agregar una cuidad
    $app->post("/ciudad", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "idprovincia" => array(
                "numeric" => true,
                "mayorcero" => 0,
                "tag" => "Identificador de Provincia"
            ),
            "iddepartamento" => array(
                "numeric" => true,
                "mayorcero" => 0,
                "tag" => "Identificador de Departamento"
            ),
            "nombre" => array(
                "min" => 1,
                "max" => 50,
                "tag" => "Nombre"
            ),
            "caracteristica" => array(
                "max" => 15,
                "tag" => "Característica"
            ),
            "cp" => array(
                "max" => 10,
                "tag" => "Código Postal"
            ),
            "latitud" => array(
                "numeric" => true,
                "tag" => "Latitud"
            ),
            "longitud" => array(
                "numeric" => true,
                "tag" => "Longitud"
            ),
            "descripcion" => array(
                "tag" => "Descripción"
            )
        );
        $validar = new Validate();
        $parsedBody = $request->getParsedBody();
        if($validar->validar($parsedBody, $reglas)) {
            $data = array(
                "idprovincia" => $parsedBody["idprovincia"],
                "iddepartamento" => $parsedBody["iddepartamento"],
                "nombre" => $parsedBody["nombre"],
                "caracteristica" => $parsedBody["caracteristica"],
                "cp" => $parsedBody["cp"],
                "latitud" => $parsedBody["latitud"],
                "longitud" => $parsedBody["longitud"],
                "descripcion" => $parsedBody["descripcion"]
            );
            //Verificaciones!?
            //????
            $respuesta = dbPostWithData("ciudades", $data);
            return $response
                ->withStatus(201) //Created
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

    //[PATCH]

    //Actualizar los datos de una Ciudad
    $app->patch("/ciudad/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "idprovincia" => array(
                "numeric" => true,
                "mayorcero" => 0,
                "tag" => "Identificador de Provincia"
            ),
            "iddepartamento" => array(
                "numeric" => true,
                "mayorcero" => 0,
                "tag" => "Identificador de Departamento"
            ),
            "nombre" => array(
                "min" => 1,
                "max" => 50,
                "tag" => "Nombre"
            ),
            "caracteristica" => array(
                "max" => 15,
                "tag" => "Característica"
            ),
            "cp" => array(
                "max" => 10,
                "tag" => "Código Postal"
            ),
            "latitud" => array(
                "numeric" => true,
                "tag" => "Latitud"
            ),
            "longitud" => array(
                "numeric" => true,
                "tag" => "Longitud"
            ),
            "descripcion" => array(
                "tag" => "Descripción"
            )
        );
        $validar = new Validate();
        $parsedBody = $request->getParsedBody();
        if($validar->validar($parsedBody, $reglas)) {
            $data = array(
                "idprovincia" => $parsedBody["idprovincia"],
                "iddepartamento" => $parsedBody["iddepartamento"],
                "nombre" => $parsedBody["nombre"],
                "caracteristica" => $parsedBody["caracteristica"],
                "cp" => $parsedBody["cp"],
                "latitud" => $parsedBody["latitud"],
                "longitud" => $parsedBody["longitud"],
                "descripcion" => $parsedBody["descripcion"]
            );
            $respuesta = dbPatchWithData("ciudades", $args["id"], $data);
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

    //Eliminar una Ciudad
    //Eliminar una Ciudad por cuestiones relacionales NO SE DEBEN ELIMINAR (a no ser de que no exista ningún dato relacionado)
    $app->delete("/ciudad/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        return $response
            ->withStatus(503)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode(array("Mensaje" => "Service Unavailable (En producción!)"), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });
?>