<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Atractivos

    //Datos de un atractivo Particular
    $app->get("/atractivo/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT * FROM atractivos WHERE id = " . $args["id"];
        $respuesta = dbGet($xSQL);
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