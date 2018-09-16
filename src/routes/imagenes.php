<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Galería de Imágenes

    // **** Obtener [GET]

    //Obtener todas las Imágenes
    $app->get("/imagenes", function (Request $request, Response $response, array $args) {
        $respuesta = dbGet("SELECT * FROM galeria ORDER BY id");
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Obtener las imágenes Hospedaje / Gastronomía
    $app->get("/imagenes/{idGoG:[0-9]+}/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        //idGoG: 1 => Hospedaje, 2 => Gastronomía
        $xSQL = "SELECT * FROM galeria";
        $xSQL .= " WHERE idGoG = " . $args["idGoG"];
        $xSQL .= " AND idgaleria = " . $args["id"];
        $xSQL .= " ORDER BY id";
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    // **** Agregar [POST]

    //Agregar una imagen a la galería Hospedaje / Gastronomía
    $app->post("/imagen/{idGoG:[0-9]+}/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $resperr = new stdClass();
        $resperr->err = true;
        $directory = $this->get("upload_directory");
        $tamanio_maximo = $this->get("max_file_size");
        $formatos_permitidos = $this->get("allow_file_format");
        $uploadedFiles = $request->getUploadedFiles();
        if(isset($uploadedFiles["imgup"])) {
            // handle single input with single file upload
            $uploadedFile = $uploadedFiles["imgup"];
            if($uploadedFile->getError() === UPLOAD_ERR_OK) {
                if($uploadedFile->getSize() <= $tamanio_maximo) {
                    if(in_array($uploadedFile->getClientMediaType(), $formatos_permitidos)) {
                        $filename = moveUploadedFile($directory, $uploadedFile, $args["idGoG"], $args["id"]);
                        $data = array(
                            "idGoG" => $args["idGoG"],
                            "idgaleria" => $args["id"],
                            "imagen" => $filename
                        );
                        $respuesta = dbPostWithData("galeria", $data);
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

    // **** Eliminar [DELETE]

    /*
        Eliminar una imagen de la galería
        @type DELETE
        @param {Number} id - Id de la tabla galeria
        @returns
            -> [StatusCode] [200 - OK] []
            -> JSON {
                [Boolean]   ->err
                [String]    ->errMsg
            }
    */
    $app->delete("/imagen/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $archivo = dbGet("SELECT imagen FROM galeria WHERE id = " . $args["id"]);
        if($archivo->err == false && $archivo->data["count"] > 0) {
            $fileX = $archivo->data["registros"][0]->imagen;
            @unlink($this->get("upload_directory") . "\\$fileX");
        }
        $respuesta = dbDelete("galeria", $args["id"]);
        return $response
            ->withStatus(200)
            //->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });


?>