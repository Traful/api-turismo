<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Ciudades

    //Todos los Atractivos de una Ciudad (Loclidad)
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
        $color = "722789"; //Violeta Oscuro
        //Para obtener el color (saber si la localidad es parte de alguna zona)
        $xSQL = "SELECT idzona FROM zonas_ciudades WHERE idciudad = " . $args["id"];
        $resp_zona_ciudades = dbGet($xSQL);
        if($resp_zona_ciudades->data["count"] > 0) {
            //Obtener el color de la zona
            $xSQL = "SELECT color FROM zonas WHERE id = " . $resp_zona_ciudades->data["registros"][0]->idzona;
            $resp_zona_color = dbGet($xSQL);
            if($resp_zona_color->data["count"] > 0) {
                $color = $resp_zona_color->data["registros"][0]->color;
            }
        }
        $respuesta->data["registros"][0]->color = $color;
        //Obtener imágenes al azar de los Atractivos
        $buffer_imagenes = array();
        $xSQL = "SELECT id, nombre from atractivos WHERE idlocalidad = " . $args["id"];
        $atractivos = dbGet($xSQL);
        if(count($atractivos->data["registros"]) > 0) {
            for($i=0; $i < count($atractivos->data["registros"]); $i++) {
                $nombre_atractivo = $atractivos->data["registros"][$i]->nombre;
                //Selecciono una imagen de ese atractivo al azar
                $xSQL = "SELECT imagen FROM atractivo_imgs WHERE idatractivo = " . $atractivos->data["registros"][$i]->id;
                $imagenes = dbGet($xSQL);
                if(count($imagenes->data["registros"]) > 0) {
                    //$nro = random_int(0, (count($imagenes->data["registros"]) - 1)); //Solo PHP 7^ 
                    $nro = intval(rand(0, (count($imagenes->data["registros"]) - 1)));
                    array_push($buffer_imagenes, array(
                        "nombre_atractivo" => $nombre_atractivo,
                        "imagen" => $imagenes->data["registros"][$nro]->imagen,
                    ));
                } else { //El Atractivo no tiene fotos
                    array_push($buffer_imagenes, array(
                        "nombre_atractivo" => $nombre_atractivo,
                        "imagen" => "default.jpg",
                    ));
                }
            }
        } else {
            //No tiene atractivos la Localidad
            array_push($buffer_imagenes, array(
                "nombre_atractivo" => "Sin Atractivos",
                "imagen" => "default.jpg",
            ));
        }
        $respuesta->data["registros"][0]->imagenes = $buffer_imagenes;

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

    //Agregar una Cuidad
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
            "latitudg" => array(
                "max" => 25,
                "tag" => "Latitud (º Grados)"
            ),
            "longitudg" => array(
                "max" => 25,
                "tag" => "Longitud (º Grados)"
            ),
            "descripcion" => array(
                "tag" => "Descripción"
            ),
            "video" => array(
                "max" => 250,
                "tag" => "URL Video"
            ),
            "mdireccion" => array(
                "max" => 50,
                "tag" => "Municipio Dirección"
            ),
            "mtelefono" => array(
                "max" => 10,
                "tag" => "Municipio Teléfono"
            ),
            "minterno" => array(
                "max" => 5,
                "tag" => "Municipio Interno"
            ),
            "mweb" => array(
                "max" => 100,
                "tag" => "Municipio Web"
            ),
            "mmail" => array(
                "max" => 100,
                "tag" => "Municipio Email"
            ),
            "mresponsable" => array(
                "max" => 100,
                "tag" => "Municipio Responsable"
            ),
            "odireccion" => array(
                "max" => 50,
                "tag" => "Oficina de Turísmo Dirección"
            ),
            "otelefono" => array(
                "max" => 10,
                "tag" => "Oficina de Turísmo Teléfono"
            ),
            "ointerno" => array(
                "max" => 5,
                "tag" => "Oficina de Turísmo Interno"
            ),
            "oweb" => array(
                "max" => 100,
                "tag" => "Oficina de Turísmo Web"
            ),
            "omail" => array(
                "max" => 100,
                "tag" => "Oficina de Turísmo Email"
            ),
            "oresponsable" => array(
                "max" => 100,
                "tag" => "Oficina de Turísmo Responsable"
            ),
            "fiestas" => array(
                "tag" => "Festejos/Eventos"
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
                "latitudg" => $parsedBody["latitudg"],
                "longitudg" => $parsedBody["longitudg"],
                "descripcion" => $parsedBody["descripcion"],
                "video" => $parsedBody["video"],
                "mdireccion" => $parsedBody["mdireccion"],
                "mtelefono" => $parsedBody["mtelefono"],
                "minterno" => $parsedBody["minterno"],
                "mweb" => $parsedBody["mweb"],
                "mmail" => $parsedBody["mmail"],
                "mresponsable" => $parsedBody["mresponsable"],
                "odireccion" => $parsedBody["odireccion"],
                "otelefono" => $parsedBody["otelefono"],
                "ointerno" => $parsedBody["ointerno"],
                "oweb" => $parsedBody["oweb"],
                "omail" => $parsedBody["omail"],
                "oresponsable" => $parsedBody["oresponsable"],
                "fiestas" => $parsedBody["fiestas"]
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
            "latitudg" => array(
                "max" => 25,
                "tag" => "Latitud (º Grados)"
            ),
            "longitudg" => array(
                "max" => 25,
                "tag" => "Longitud (º Grados)"
            ),
            "descripcion" => array(
                "tag" => "Descripción"
            ),
            "video" => array(
                "max" => 250,
                "tag" => "URL Video"
            ),
            "mdireccion" => array(
                "max" => 50,
                "tag" => "Municipio Dirección"
            ),
            "mtelefono" => array(
                "max" => 10,
                "tag" => "Municipio Teléfono"
            ),
            "minterno" => array(
                "max" => 5,
                "tag" => "Municipio Interno"
            ),
            "mweb" => array(
                "max" => 100,
                "tag" => "Municipio Web"
            ),
            "mmail" => array(
                "max" => 100,
                "tag" => "Municipio Email"
            ),
            "mresponsable" => array(
                "max" => 100,
                "tag" => "Municipio Responsable"
            ),
            "odireccion" => array(
                "max" => 50,
                "tag" => "Oficina de Turísmo Dirección"
            ),
            "otelefono" => array(
                "max" => 10,
                "tag" => "Oficina de Turísmo Teléfono"
            ),
            "ointerno" => array(
                "max" => 5,
                "tag" => "Oficina de Turísmo Interno"
            ),
            "oweb" => array(
                "max" => 100,
                "tag" => "Oficina de Turísmo Web"
            ),
            "omail" => array(
                "max" => 100,
                "tag" => "Oficina de Turísmo Email"
            ),
            "oresponsable" => array(
                "max" => 100,
                "tag" => "Oficina de Turísmo Responsable"
            ),
            "fiestas" => array(
                "tag" => "Festejos/Eventos"
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
                "latitudg" => $parsedBody["latitudg"],
                "longitudg" => $parsedBody["longitudg"],
                "descripcion" => $parsedBody["descripcion"],
                "video" => $parsedBody["video"],
                "mdireccion" => $parsedBody["mdireccion"],
                "mtelefono" => $parsedBody["mtelefono"],
                "minterno" => $parsedBody["minterno"],
                "mweb" => $parsedBody["mweb"],
                "mmail" => $parsedBody["mmail"],
                "mresponsable" => $parsedBody["mresponsable"],
                "odireccion" => $parsedBody["odireccion"],
                "otelefono" => $parsedBody["otelefono"],
                "ointerno" => $parsedBody["ointerno"],
                "oweb" => $parsedBody["oweb"],
                "omail" => $parsedBody["omail"],
                "oresponsable" => $parsedBody["oresponsable"],
                "fiestas" => $parsedBody["fiestas"]
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