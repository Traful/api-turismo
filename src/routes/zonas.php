<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    //Zonas

    //Todas las Zonas (Activas)
    $app->get("/zonas", function (Request $request, Response $response, array $args) {
        $respuesta = dbGet("SELECT * FROM zonas WHERE activo = 1 ORDER BY nombre");
        //Settings Path imágenes y fotos
        $host = $this->get("api_host");
        for($i=0; $i < count($respuesta->data["registros"]); $i++) {
            $respuesta->data["registros"][$i]->mapa = $host . DIRECTORY_SEPARATOR . "mapas" . DIRECTORY_SEPARATOR . $respuesta->data["registros"][$i]->mapa;
            $respuesta->data["registros"][$i]->foto = $host . DIRECTORY_SEPARATOR . "recursos" . DIRECTORY_SEPARATOR . "zonas" . DIRECTORY_SEPARATOR . $respuesta->data["registros"][$i]->foto;
        }
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
        $xSQL = "SELECT zonas_ciudades.id, zonas_ciudades.idciudad, ciudades.nombre as ciudad, departamentos.nombre as departamento FROM zonas_ciudades";
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
                "foto" => "default.jpg",
                "mini" => "default.jpg",
                "color" => "000000",
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
            "foto" => array(
                "tag" => "Foto"
            ),
            "mini" => array(
                "tag" => "Mini Mapa San Luis"
            ),
            "activo" => array(
                "tag" => "Activo"
            ),
            "color" => array(
                "max" => 10,
                "tag" => "Color"
            )
        );
        $validar = new Validate();
        if($validar->validar($request->getParsedBody(), $reglas)) {
            $parsedBody = $request->getParsedBody();
            
            $tamanio_maximo = $this->get("max_file_size");
            $formatos_permitidos = $this->get("allow_file_format");
            $uploadedFiles = $request->getUploadedFiles();
            
            //Mapa
            $directory_mapa = $this->get("upload_directory_mapa");
            $filename_mapa = "";
            if(isset($uploadedFiles["imgmapa"])) {
                $uploadedFile = $uploadedFiles["imgmapa"];
                if($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    if($uploadedFile->getSize() <= $tamanio_maximo) {
                        if(in_array($uploadedFile->getClientMediaType(), $formatos_permitidos)) {
                            $filename_mapa = moveUploadedFile($directory_mapa, $uploadedFile, 1, $args["id"]);
                        }
                    }
                }
            }
            $eliminar_viejo_mapa = false;
            $nombre_viejo_mapa = $parsedBody["mapa"];
            if($filename_mapa == "") {
                $filename_mapa = $nombre_viejo_mapa;
            } else {
                $eliminar_viejo_mapa = true;
            }

            //Foto
            $directory_foto = $this->get("upload_directory_zonas");
            $filename_foto = "";
            if(isset($uploadedFiles["imgfoto"])) {
                $uploadedFile = $uploadedFiles["imgfoto"];
                if($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    if($uploadedFile->getSize() <= $tamanio_maximo) {
                        if(in_array($uploadedFile->getClientMediaType(), $formatos_permitidos)) {
                            $filename_foto = moveUploadedFile($directory_foto, $uploadedFile, 1, $args["id"]);
                        }
                    }
                }
            }
            $eliminar_vieja_foto = false;
            $nombre_vieja_foto = $parsedBody["foto"];
            if($filename_foto == "") {
                $filename_foto = $nombre_vieja_foto;
            } else {
                $eliminar_vieja_foto = true;
            }

            //Mini (Mini Mapa San Luis)
            $directory_mini = $this->get("upload_directory_mapa") . "/mini";
            $filename_mini = "";
            if(isset($uploadedFiles["imgmini"])) {
                $uploadedFile = $uploadedFiles["imgmini"];
                if($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    if($uploadedFile->getSize() <= $tamanio_maximo) {
                        if(in_array($uploadedFile->getClientMediaType(), $formatos_permitidos)) {
                            $filename_mini = moveUploadedFile($directory_mini, $uploadedFile, 1, $args["id"]);
                        }
                    }
                }
            }
            $eliminar_viejo_mini = false;
            $nombre_viejo_mini = $parsedBody["mini"];
            if($filename_mini == "") {
                $filename_mini = $nombre_viejo_mini;
            } else {
                $eliminar_viejo_mini = true;
            }

            $data = array(
                "nombre" => $parsedBody["nombre"],
                "descripcion" => $parsedBody["descripcion"],
                "mapa" => $filename_mapa,
                "foto" => $filename_foto,
                "mini" => $filename_mini,
                "color" => $parsedBody["color"],
                "activo" => $parsedBody["activo"]
            );
            $respuesta = dbPatchWithData("zonas", $args["id"], $data);
            if($respuesta->err == false) {
                //Eliminar la vieja imagen del Mapa
                if(($eliminar_viejo_mapa == true) && ($nombre_viejo_mapa <> "default.jpg")) {
                    @unlink($directory_mapa . DIRECTORY_SEPARATOR . $nombre_viejo_mapa);
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
                $respuesta->mapa = $filename_mapa;
                //Eliminar la vieja foto
                if(($eliminar_vieja_foto == true) && ($nombre_vieja_foto <> "default.jpg")) {
                    @unlink($directory_foto . DIRECTORY_SEPARATOR . $nombre_vieja_foto);
                }
                $respuesta->foto = $filename_foto;
                //Eliminar la vieja foto de Mini Mapa san Luis
                if(($eliminar_viejo_mini == true) && ($nombre_viejo_mini <> "default.jpg")) {
                    @unlink($directory_mini . DIRECTORY_SEPARATOR . $nombre_viejo_mini);
                }
                $respuesta->mini = $filename_mini;
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