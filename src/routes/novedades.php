<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Novedades

    //[GET]

    //Obtener las últimas X Novedades
    $app->get("/novedades/{cantidad:[0-9]+}", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT * FROM novedades ORDER BY id DESC LIMIT 0, " . $args["cantidad"];
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //[POST]

    //Agregar una Novedad
    $app->post("/novedad", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "localidad" => array(
                "min" => 3, 
                "max" => 50,
                "tag" => "Localidad"
            ),
            "fecha" => array(
                "tag" => "Fecha"
            ),
            "titulo" => array(
                "max" => 50, 
                "tag" => "Título"
            ),
            "subtitulo" => array(
                "max" => 50, 
                "tag" => "Sub Título"
            ),
            "descripcion" => array(
                "tag" => "Descripción"
            ),
            "latitud" => array(
                "numeric" => true,
                "tag" => "Latitud"
            ),
            "longitud" => array(
                "numeric" => true,
                "tag" => "Longitud"
            )
        );
        $validar = new Validate();
        if($validar->validar($request->getParsedBody(), $reglas)) {
            $parsedBody = $request->getParsedBody();
            $fecha_valida = false;
            if(strpos($parsedBody["fecha"], "-") !== false) {
                $data_fecha = explode("-", $parsedBody["fecha"]);
                //YYYY-MM-DD
                if(count($data_fecha) == 3) {
                    $fecha_valida = checkdate($data_fecha[1], $data_fecha[2], $data_fecha[0]);
                }
            }
            if($fecha_valida == true) {
                //Imágenes
                $directory = $this->get("upload_directory_novedades");
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
                $respuesta = dbPostWithData("novedades", $parsedBody);
                $respuesta->foto_uno = $img_uno;
                $respuesta->foto_dos = $img_dos;
                return $response
                    ->withStatus(201) //Created
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            } else {
                $resperr = new stdClass();
                $resperr->err = true;
                $resperr->errMsg = "Fecha no válida";
                $resperr->errMsgs = ["Fecha no válida"];
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
?>