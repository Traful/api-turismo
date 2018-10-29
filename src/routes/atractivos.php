<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Atractivos

    //Datos de un atractivo Particular
    $app->get("/atractivo/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT * FROM atractivos WHERE id = " . $args["id"];
        $respuesta = dbGet($xSQL);
        //Color?
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Imagenes de un atractivo Particular
    $app->get("/atractivo/{id:[0-9]+}/imagenes", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT * FROM atractivo_imgs WHERE idatractivo = " . $args["id"];
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //[POST]

    //Agregar un Atractivo
    $app->post("/atractivo/new/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "idlocalidad" => array(
                "mayorcero" => true, 
                "numeric" => true,
                "tag" => "Identificador de Localidad"
            ),
            "tipo" => array(
                "max" => 30, 
                "tag" => "Tipo de Atractivo"
            ),
            "nombre" => array(
                "min" => 5,
                "max" => 50, 
                "tag" => "Nombre del Atractivo"
            ),
            "domicilio" => array(
                "max" => 50, 
                "tag" => "Domicilio del Atractivo"
            ),
            "descripcion" => array(
                "tag" => "Descripcion del Atractivo"
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
                "tag" => "Latitud º"
            ),
            "longitudg" => array(
                "max" => 25,
                "tag" => "Longitud º"
            ),
            "telefono" => array(
                "max" => 25,
                "tag" => "Teléfono"
            ),
            "mail" => array(
                "max" => 100,
                "tag" => "Email"
            ),
            "web" => array(
                "max" => 100,
                "tag" => "Web"
            ),
            "costo" => array(
                "numeric" => true,
                "tag" => "Costo"
            ),
            "lunes" => array(
                "max" => 100,
                "tag" => "Horario Lunes"
            ),
            "martes" => array(
                "max" => 100,
                "tag" => "Horario Martes"
            ),
            "miercoles" => array(
                "max" => 100,
                "tag" => "Horario Miércoles"
            ),
            "jueves" => array(
                "max" => 100,
                "tag" => "Horario Jueves"
            ),
            "viernes" => array(
                "max" => 100,
                "tag" => "Horario Viernes"
            ),
            "sabado" => array(
                "max" => 100,
                "tag" => "Horario Sábado"
            ),
            "domingo" => array(
                "max" => 100,
                "tag" => "Horario Domingo"
            ),
            "imperdible" => array(
                "tag" => "Imperdible"
            )
        );
        $validar = new Validate();
        if($validar->validar($request->getParsedBody(), $reglas)) {
            $parsedBody = $request->getParsedBody();
            $respuesta = dbPostWithData("atractivos", $parsedBody);
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

    //Agregar una imagen a un Atractivo en particular
    $app->post("/atractivo/{id:[0-9]+}/imagen", function (Request $request, Response $response, array $args) {
        $resperr = new stdClass();
        $resperr->err = true;
        $directory = $this->get("upload_directory_atractivo");
        $tamanio_maximo = $this->get("max_file_size");
        $formatos_permitidos = $this->get("allow_file_format");
        $uploadedFiles = $request->getUploadedFiles();
        if(isset($uploadedFiles["imgup"])) {
            // handle single input with single file upload
            $uploadedFile = $uploadedFiles["imgup"];
            if($uploadedFile->getError() === UPLOAD_ERR_OK) {
                if($uploadedFile->getSize() <= $tamanio_maximo) {
                    if(in_array($uploadedFile->getClientMediaType(), $formatos_permitidos)) {
                        $filename = moveUploadedFile($directory, $uploadedFile, 0, $args["id"]);
                        $data = array(
                            "idatractivo" => $args["id"],
                            "imagen" => $filename
                        );
                        $respuesta = dbPostWithData("atractivo_imgs", $data);
                        if(!$respuesta->err) {
                            return $response
                                ->withStatus(201)
                                ->withHeader("Content-Type", "application/json")
                                ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                        } else {
                            return $response
                                ->withStatus(409) //Conflicto
                                ->withHeader("Content-Type", "application/json")
                                ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                        }
                    } else {
                        $resperr->errMsg = "No es un formato de imagen admitido.";
                    }
                } else {
                    $resperr->errMsg = "La imagen no debe superar los 4 MB.";
                }
            }
        } else {
            $resperr->errMsg = "No se suministro ninguna imagen.";
        }
        return $response
            ->withStatus(409) //Conflicto
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //[PATCH]
    
    //Actualizar los datos de un atractivo
    $app->patch("/atractivo/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "id" => array(
                "mayorcero" => true, 
                "numeric" => true,
                "tag" => "Identificador de Atractivo"
            ),
            "idlocalidad" => array(
                "mayorcero" => true, 
                "numeric" => true,
                "tag" => "Identificador de Localidad"
            ),
            "tipo" => array(
                "max" => 30, 
                "tag" => "Tipo de Atractivo"
            ),
            "nombre" => array(
                "min" => 5,
                "max" => 50, 
                "tag" => "Nombre del Atractivo"
            ),
            "domicilio" => array(
                "max" => 50, 
                "tag" => "Domicilio del Atractivo"
            ),
            "descripcion" => array(
                "tag" => "Descripcion del Atractivo"
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
                "tag" => "Latitud º"
            ),
            "longitudg" => array(
                "max" => 25,
                "tag" => "Longitud º"
            ),
            "telefono" => array(
                "max" => 25,
                "tag" => "Teléfono"
            ),
            "mail" => array(
                "max" => 100,
                "tag" => "Email"
            ),
            "web" => array(
                "max" => 100,
                "tag" => "Web"
            ),
            "costo" => array(
                "numeric" => true,
                "tag" => "Costo"
            ),
            "lunes" => array(
                "max" => 100,
                "tag" => "Horario Lunes"
            ),
            "martes" => array(
                "max" => 100,
                "tag" => "Horario Martes"
            ),
            "miercoles" => array(
                "max" => 100,
                "tag" => "Horario Miércoles"
            ),
            "jueves" => array(
                "max" => 100,
                "tag" => "Horario Jueves"
            ),
            "viernes" => array(
                "max" => 100,
                "tag" => "Horario Viernes"
            ),
            "sabado" => array(
                "max" => 100,
                "tag" => "Horario Sábado"
            ),
            "domingo" => array(
                "max" => 100,
                "tag" => "Horario Domingo"
            ),
            "imperdible" => array(
                "tag" => "Imperdible"
            )
        );
        $validar = new Validate();
        $parsedBody = $request->getParsedBody();
        if($validar->validar($parsedBody, $reglas)) {
            //$respuesta = dbPatchWithData("atractivos", $args["id"], $parsedBody);
            $respuesta = dbPatchWithData("atractivos", $parsedBody["id"], $parsedBody);
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

    $app->delete("/atractivo/imagen/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $archivo = dbGet("SELECT imagen FROM atractivo_imgs WHERE id = " . $args["id"]);
        if($archivo->err == false && $archivo->data["count"] > 0) {
            $fileX = $archivo->data["registros"][0]->imagen;
            @unlink($this->get("upload_directory_atractivo") . "\\$fileX");
        }
        $respuesta = dbDelete("atractivo_imgs", $args["id"]);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

?>