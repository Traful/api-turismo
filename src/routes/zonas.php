<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Zonas

    //Todas las Zonas (Activas)
    $app->get("/zonas", function (Request $request, Response $response, array $args) {
        $respuesta = dbGet("SELECT id, nombre FROM zonas WHERE activo = 1 ORDER BY nombre");
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Datos de una Zona en particular
    $app->get("/zona/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        //Datos de la Zona
        $respuesta = dbGet("SELECT * FROM zonas WHERE id = " . $args["id"]);
        //$respuesta->ciudades = $ciudades->data["registros"];
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Todas los Ciudades de una determinada Zona
    $app->get("/zona/{idzona:[0-9]+}/ciudades", function (Request $request, Response $response, array $args) {
        //Ciudades de la Zona
        $xSQL = "SELECT zonas_ciudades.id, ciudades.nombre as ciudad, departamentos.nombre as departamento FROM zonas_ciudades";
        $xSQL .= " INNER JOIN ciudades ON zonas_ciudades.idciudad = ciudades.id";
        $xSQL .= " INNER JOIN departamentos ON ciudades.iddepartamento = departamentos.id";
        $xSQL .= " WHERE zonas_ciudades.idzona = " . $args["idzona"];
        $xSQL .= " ORDER BY departamentos.nombre, ciudades.nombre";
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //[POST]

    //Agregar una nueva Zona
    $app->post("/zona", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "nombre" => array(
                "min" => 5,
                "max" => 150,
                "tag" => "Nombre de la Zona"
            )
        );
        $validar = new Validate();
        if($validar->validar($request->getParsedBody(), $reglas)) {
            //Verificar que la zona no existe (Falta!)
            $parsedBody = $request->getParsedBody();
            $data = array(
                "nombre" => $parsedBody["nombre"],
                "descripcion" => "",
                "mapa" => "default.jpg",
                "activo" => 1
            );
            $respuesta = dbPostWithData("zonas", $data);
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

    //Actualizar los datos de una Zona (debería ser patch, pero lleva imágen [ver guias.php])
    $app->post("/zona/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "nombre" => array(
                "min" => 5,
                "max" => 150,
                "tag" => "Nombre de la Zona"
            ),
            "descripcion" => array(
                "tag" => "Descripción"
            ),
            "mapa" => array(
                "tag" => "Mapa"
            ),
            "activo" => array(
                "tag" => "Activo"
            )
        );
        $validar = new Validate();
        if($validar->validar($request->getParsedBody(), $reglas)) {
            $parsedBody = $request->getParsedBody();
            //Imagen
            $directory = $this->get("upload_directory_mapa");
            $tamanio_maximo = $this->get("max_file_size");
            $formatos_permitidos = $this->get("allow_file_format");
            $uploadedFiles = $request->getUploadedFiles();
            $filename = "";
            if(isset($uploadedFiles["imgmapa"])) {
                $uploadedFile = $uploadedFiles["imgmapa"];
                if($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    if($uploadedFile->getSize() <= $tamanio_maximo) {
                        if(in_array($uploadedFile->getClientMediaType(), $formatos_permitidos)) {
                            $filename = moveUploadedFile($directory, $uploadedFile, 1, $args["id"]);
                        }
                    }
                }
            }
            $eliminar_viejo_mapa = false;
            $nombre_viejo_mapa = $parsedBody["mapa"];
            if($filename == "") {
                $filename = $nombre_viejo_mapa;
            } else {
                $eliminar_viejo_mapa = true;
            }
            $data = array(
                "nombre" => $parsedBody["nombre"],
                "descripcion" => $parsedBody["descripcion"],
                "mapa" => $filename,
                "activo" => $parsedBody["activo"]
            );
            $respuesta = dbPatchWithData("zonas", $args["id"], $data);
            if($respuesta->err == false) {
                //Eliminar la vieja imagen del Mapa
                if(($eliminar_viejo_mapa == true) && ($nombre_viejo_mapa <> "default.jpg")) {
                    @unlink($directory . DIRECTORY_SEPARATOR . $nombre_viejo_mapa);
                    /*
                    $resperr = new stdClass();
                    $resperr->err = true;
                    $resperr->errMsg = "Error al eliminar el logo en: " . $this->get("upload_directory_logo") . DIRECTORY_SEPARATOR . $nombre_viejo_logo;
                    return $response
                        ->withStatus(409) //Conflicto
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                    */
                }
                $respuesta->mapa = $filename;
                return $response
                    ->withStatus(200) //Ok
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            } else {
                //En caso de que dbPatchWithData devolviese un error habría que eliminar la imagen subida si es que se subio alguna
                //Inconcluso!!!
                $resperr = new stdClass();
                $resperr->err = true;
                $resperr->errMsg = $respuesta->errMsg;
                return $response
                    ->withStatus(409) //Conflicto
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            }
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

    //Agregar a una Zona una Ciudad
    $app->post("/zona/{id:[0-9]+}/add/ciudad", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "idciudad" => array(
                "numeric" => true,
                "mayorcero" => true,
                "tag" => "Identificador de Ciudad"
            )
        );
        $validar = new Validate();
        if($validar->validar($request->getParsedBody(), $reglas)) {
            $parsedBody = $request->getParsedBody();
            //Veirifcar que no exista ya en las ciudades de la zona
            $xSQL = "SELECT id FROM zonas_ciudades";
            $xSQL .= " WHERE idzona = " . $args["id"];
            $xSQL .= " AND idciudad = " . $parsedBody["idciudad"];
            $zona_ciudades = dbGet($xSQL);
            if($zona_ciudades->data["count"] === 0) {
                $data = array(
                    "idzona" => $args["id"],
                    "idciudad" => $parsedBody["idciudad"],
                );
                $respuesta = dbPostWithData("zonas_ciudades", $data);
                return $response
                    ->withStatus(201) //Created
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            } else {
                $resperr = new stdClass();
                $resperr->err = true;
                $resperr->errMsg = "El Localidad ya está cargada en la Zona";
                return $response
                    ->withStatus(409) //Conflicto
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            }
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

    //Eliminar una Zona (No se eliminan, solo se las desactiva)
    $app->delete("/zona/delete/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        //No probado!
        $data = array(
            "activo" => 0
        );
        $respuesta = dbPatchWithData("zonas", $args["id"], $data);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Eliminar de una Zona una Ciudad
    $app->delete("/zona/delete/ciudad/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $respuesta = dbDelete("zonas_ciudades", $args["id"]);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });
?>