<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Eventos

    //[GET]

    //Todos los Eventos
    $app->get("/eventos", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT eventos.*, ciudades.nombre AS localidad FROM eventos";
        $xSQL .= " INNER JOIN ciudades ON eventos.idlocalidad = ciudades.id";
        $xSQL .= " ORDER BY eventos.dfecha";
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Todos los datos de un Evento en particular
    $app->get("/evento/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT eventos.*, ciudades.nombre AS localidad FROM eventos";
        $xSQL .= " INNER JOIN ciudades ON eventos.idlocalidad = ciudades.id";
        $xSQL .= " WHERE eventos.id = " . $args["id"];
        $xSQL .= " ORDER BY eventos.dfecha";
        $respuesta = dbGet($xSQL);
        //Obtener el color si la localidad pertenece a una zona
        if($respuesta->data["count"] > 0) {
            $xSQL = "SELECT color from zonas";
            $xSQL .= " INNER JOIN zonas_ciudades ON zonas.id = zonas_ciudades.idzona";
            $xSQL .= " WHERE zonas_ciudades.idciudad = " . $respuesta->data["registros"][0]->idlocalidad;
            $color = dbGet($xSQL);
            if($color->data["count"] > 0) {
                $respuesta->data["registros"][0]->color = $color->data["registros"][0]->color;
            } else { //No pertenece a una zona
                $respuesta->data["registros"][0]->color = "000000";
            }
        }
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Obtener los últimos X Eventos
    $app->get("/eventos/{cantidad:[0-9]+}", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT eventos.*, ciudades.nombre AS localidad FROM eventos";
        $xSQL .= " INNER JOIN ciudades ON eventos.idlocalidad = ciudades.id";
        $xSQL .= " ORDER BY eventos.id DESC LIMIT 0, " . $args["cantidad"];
        $respuesta = dbGet($xSQL);
        //Obtener el color si la localidad pertenece a una zona
        if($respuesta->data["count"] > 0) {
            for($i = 0; $i <  $respuesta->data["count"]; $i++) {
                $xSQL = "SELECT color from zonas";
                $xSQL .= " INNER JOIN zonas_ciudades ON zonas.id = zonas_ciudades.idzona";
                $xSQL .= " WHERE zonas_ciudades.idciudad = " . $respuesta->data["registros"][$i]->idlocalidad;
                $color = dbGet($xSQL);
                if($color->data["count"] > 0) {
                    $respuesta->data["registros"][$i]->color = $color->data["registros"][0]->color;
                } else { //No pertenece a una zona
                    $respuesta->data["registros"][$i]->color = "000000";
                }
            }
        }
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Todos Los Eventos de una determinada Fecha
    $app->get("/eventos/date/{fecha}", function (Request $request, Response $response, array $args) {
        $fecha = validar_fecha($args["fecha"]);
        if($fecha) {
            $xSQL = "SELECT eventos.*, ciudades.nombre AS localidad FROM eventos";
            $xSQL .= " INNER JOIN ciudades ON eventos.idlocalidad = ciudades.id";
            $xSQL .= " WHERE eventos.dfecha <= '" .  $args["fecha"] . "' AND eventos.hfecha >= '" .  $args["fecha"] . "'";
            $respuesta = dbGet($xSQL);
            return $response
                ->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        } else {
            $resperr = new stdClass();
            $resperr->err = true;
            $resperr->errMsg = "Hay errores en los datos suministrados";
            return $response
                ->withStatus(409) //Conflicto
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    });

    //Todos Los Eventos de una determinada Fecha en adelante
    $app->get("/eventos/date/all/{fecha}", function (Request $request, Response $response, array $args) {
        $fecha = validar_fecha($args["fecha"]);
        if($fecha) {
            $xSQL = "SELECT eventos.*, ciudades.nombre AS localidad FROM eventos";
            $xSQL .= " INNER JOIN ciudades ON eventos.idlocalidad = ciudades.id";
            $xSQL .= " WHERE eventos.hfecha >= '" .  $args["fecha"] . "'";
            $respuesta = dbGet($xSQL);
            return $response
                ->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        } else {
            $resperr = new stdClass();
            $resperr->err = true;
            $resperr->errMsg = "Hay errores en los datos suministrados";
            return $response
                ->withStatus(409) //Conflicto
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    });

    //[POST]

    //Agregar un Evento
    $app->post("/evento", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "idlocalidad" => array(
                "numeric" => true, 
                "tag" => "Identificador de Localidad"
            ),
            "titulo" => array(
                "min" => 5,
                "max" => 100, 
                "tag" => "Título"
            ),
            "lugar" => array(
                "max" => 100, 
                "tag" => "Lugar"
            ),
            "direccion" => array(
                "max" => 100, 
                "tag" => "Dirección"
            ),
            "dfecha" => array(
                "date" => true,
                "tag" => "Desde Fecha"
            ),
            "hfecha" => array(
                "date" => true,
                "tag" => "Hasta Fecha"
            ),
            "dhora" => array(
                "time" => true,
                "tag" => "Desde Hora"
            ),
            "hhora" => array(
                "time" => true,
                "tag" => "Hasta Hora"
            ),
            "descripcion" => array(
                "tag" => "Descripción"
            ),
            "costo" => array(
                "numeric" => true,
                "tag" => "Costo"
            ),
            "invita" => array(
                "max" => 50,
                "tag" => "Invita"
            ),
            "organiza" => array(
                "max" => 50,
                "tag" => "Organiza"
            )
        );
        $validar = new Validate();
        if($validar->validar($request->getParsedBody(), $reglas)) {
            $parsedBody = $request->getParsedBody();
            //Imágenes
            $directory = $this->get("upload_directory_eventos");
            $tamanio_maximo = $this->get("max_file_size");
            $formatos_permitidos = $this->get("allow_file_format");
            $uploadedFiles = $request->getUploadedFiles();
            //img-uno
            $img_uno = "default.jpg";
            if(isset($uploadedFiles["img-uno"])) {
                // handle single input with single file upload
                $uploadedFile = $uploadedFiles["img-uno"];
                if($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    if($uploadedFile->getSize() <= $tamanio_maximo) {
                        if(in_array($uploadedFile->getClientMediaType(), $formatos_permitidos)) {
                            $img_uno = moveUploadedFile($directory, $uploadedFile, 0, 0);
                        }
                    }
                }
            }
            //img-dos
            $img_dos = "default.jpg";
            if(isset($uploadedFiles["img-dos"])) {
                // handle single input with single file upload
                $uploadedFile = $uploadedFiles["img-dos"];
                if($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    if($uploadedFile->getSize() <= $tamanio_maximo) {
                        if(in_array($uploadedFile->getClientMediaType(), $formatos_permitidos)) {
                            $img_dos = moveUploadedFile($directory, $uploadedFile, 0, 1);
                        }
                    }
                }
            }
            $parsedBody["foto_uno"] = $img_uno;
            $parsedBody["foto_dos"] = $img_dos;
            $respuesta = dbPostWithData("eventos", $parsedBody);
            $respuesta->foto_uno = $img_uno;
            $respuesta->foto_dos = $img_dos;
            if($respuesta->err) {
                return $response
                    ->withStatus(409) //Conflicto
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            } else {
                return $response
                    ->withStatus(201) //Created
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
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

    //[PATCH]

    //Guardar los cambios de un Evento (el método es post debido a las imágenes [no funciona con patch si tiene imágenes])
    $app->post("/evento/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "idlocalidad" => array(
                "numeric" => true, 
                "tag" => "Identificador de Localidad"
            ),
            "titulo" => array(
                "min" => 5,
                "max" => 100, 
                "tag" => "Título"
            ),
            "lugar" => array(
                "max" => 100, 
                "tag" => "Lugar"
            ),
            "direccion" => array(
                "max" => 100, 
                "tag" => "Dirección"
            ),
            "dfecha" => array(
                "date" => true,
                "tag" => "Desde Fecha"
            ),
            "hfecha" => array(
                "date" => true,
                "tag" => "Hasta Fecha"
            ),
            "dhora" => array(
                "time" => true,
                "tag" => "Desde Hora"
            ),
            "hhora" => array(
                "time" => true,
                "tag" => "Hasta Hora"
            ),
            "descripcion" => array(
                "tag" => "Descripción"
            ),
            "costo" => array(
                "numeric" => true,
                "tag" => "Costo"
            ),
            "invita" => array(
                "max" => 50,
                "tag" => "Invita"
            ),
            "organiza" => array(
                "max" => 50,
                "tag" => "Organiza"
            )
        );
        $validar = new Validate();
        if($validar->validar($request->getParsedBody(), $reglas)) {
            $parsedBody = $request->getParsedBody();
            //Imágenes
            $directory = $this->get("upload_directory_eventos");
            $tamanio_maximo = $this->get("max_file_size");
            $formatos_permitidos = $this->get("allow_file_format");
            $uploadedFiles = $request->getUploadedFiles();
            //img-uno
            $img_uno = $parsedBody["foto_uno"];
            if(isset($uploadedFiles["img-uno"])) {
                $uploadedFile = $uploadedFiles["img-uno"];
                if($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    if($uploadedFile->getSize() <= $tamanio_maximo) {
                        if(in_array($uploadedFile->getClientMediaType(), $formatos_permitidos)) {
                            $img_uno = moveUploadedFile($directory, $uploadedFile, 0, 0);
                            if($img_uno == true) {
                                //Eliminar la vieja imagen uno si no es default.jpg
                                $eliminar = $parsedBody["foto_uno"];
                                if($eliminar != "default.jpg") {
                                    @unlink($this->get("upload_directory_eventos") . "\\$eliminar");
                                }
                            }
                        }
                    }
                }
            }
            //img-dos
            $img_dos = $parsedBody["foto_dos"];
            if(isset($uploadedFiles["img-dos"])) {
                // handle single input with single file upload
                $uploadedFile = $uploadedFiles["img-dos"];
                if($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    if($uploadedFile->getSize() <= $tamanio_maximo) {
                        if(in_array($uploadedFile->getClientMediaType(), $formatos_permitidos)) {
                            $img_dos = moveUploadedFile($directory, $uploadedFile, 0, 1);
                            if($img_dos == true) {
                                //Eliminar la vieja imagen dos si no es default.jpg
                                $eliminar = $parsedBody["foto_dos"];
                                if($eliminar != "default.jpg") {
                                    @unlink($this->get("upload_directory_eventos") . "\\$eliminar");
                                }
                            }
                        }
                    }
                }
            }
            $parsedBody["foto_uno"] = $img_uno;
            $parsedBody["foto_dos"] = $img_dos;
            //Eliminar de $parsedBody id localidad y color
            unset($parsedBody["id"]);
            unset($parsedBody["localidad"]);
            unset($parsedBody["color"]);
            $respuesta = dbPatchWithData("eventos", $args["id"], $parsedBody);
            $respuesta->foto_uno = $img_uno;
            $respuesta->foto_dos = $img_dos;
            if($respuesta->err) {
                return $response
                    ->withStatus(409) //Conflicto
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            } else {
                return $response
                    ->withStatus(200) //Ok
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
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

    //Eliminar un Evento
    $app->delete("/evento/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $archivo = dbGet("SELECT foto_uno, foto_dos FROM eventos WHERE id = " . $args["id"]);
        if($archivo->err == false && $archivo->data["count"] > 0) {
            $fileX = $archivo->data["registros"][0]->foto_uno;
            if($fileX != "default.jpg") {
                @unlink($this->get("upload_directory_eventos") . "\\$fileX");
            }
            $fileX = $archivo->data["registros"][0]->foto_dos;
            if($fileX != "default.jpg") {
                @unlink($this->get("upload_directory_eventos") . "\\$fileX");
            }
        }
        $respuesta = dbDelete("eventos", $args["id"]);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

    });
?>