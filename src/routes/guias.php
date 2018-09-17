<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;


    // **** Obtener [GET]

    //Obtener todas las Guias
    $app->get("/guias", function (Request $request, Response $response, array $args) {
        $respuesta = dbGet("SELECT guias.id, guias.legajo, guias.nombre, guias.activo FROM guias ORDER BY guias.nombre");
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Obtener las Guias de un determinado Departamento
    $app->get("/guias/departamento/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT guias.id, guias.legajo, guias.nombre, guias.activo FROM guias";
        $xSQL .= " INNER JOIN ciudades ON guias.idciudad = ciudades.id";
        $xSQL .= " INNER JOIN departamentos ON ciudades.iddepartamento = departamentos.id";
        $xSQL .= " WHERE departamentos.id = " . $args["id"];
        $xSQL .= " ORDER BY guias.nombre";
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Obtener las Guias de una determinada Ciudad
    $app->get("/guias/ciudad/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT guias.id, guias.legajo, guias.nombre, guias.activo FROM guias";
        $xSQL .= " WHERE guias.idciudad = " . $args["id"];
        $xSQL .= " ORDER BY guias.nombre";
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Obtener una Guia en particular
    $app->get("/guia/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT guias.*, usuarios.nombre AS nombreusuario, ciudades.iddepartamento, ciudades.nombre AS nombreciudad, departamentos.nombre AS nombredepartamento, tiposcategorias.id AS idtipocategorias FROM guias";
        $xSQL .= " INNER JOIN ciudades ON guias.idciudad = ciudades.id";
        $xSQL .= " INNER JOIN departamentos ON ciudades.iddepartamento = departamentos.id";
        $xSQL .= " INNER JOIN valortipcat ON guias.idvalortipcat = valortipcat.id";
        $xSQL .= " INNER JOIN tiposcategorias ON valortipcat.idtipcat = tiposcategorias.id";
        $xSQL .= " INNER JOIN usuarios ON guias.iduser = usuarios.id";
        $xSQL .= " WHERE guias.id = " . $args["id"];
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Obtener los Servicios de una Guía en particular
    $app->get("/guia/{id:[0-9]+}/servicios", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT guiaservicios.*, servicios.descripcion FROM guiaservicios";
		$xSQL .= " INNER JOIN servicios ON guiaservicios.idservicio = servicios.id";
		$xSQL .= " WHERE guiaservicios.idguia = " . $args["id"];
		$xSQL .= " ORDER BY servicios.descripcion";
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Obtener la Galería de Imágenes de una Guía en particular
    $app->get("/guia/{id:[0-9]+}/imagenes", function (Request $request, Response $response, array $args) {
        //idGoG: 1 => Hospedaje, 2 => Gastronomía
        $respuesta = dbGet("SELECT id, imagen FROM galeria WHERE idGoG = 1 AND idgaleria = " . $args["id"] . " ORDER BY id");
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Obtener las Redes Sociales de una Guía en particular
    $app->get("/guia/{id:[0-9]+}/redes", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT guia_redes.id, guia_redes.idred, guia_redes.link, redes.nombre, redes.icono FROM guia_redes";
        $xSQL .= " INNER JOIN redes ON guia_redes.idred = redes.id";
        $xSQL .= " WHERE guia_redes.idguia = " .  $args["id"];
        $xSQL .= " ORDER BY redes.nombre";
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Obtener las Tarifas de una Guía en particular
    $app->get("/guia/{id:[0-9]+}/tarifas", function (Request $request, Response $response, array $args) {
        $xSQL = "SELECT guia_tarifas.*, tipo_tarifas.descripcion FROM guia_tarifas";
        $xSQL .= " INNER JOIN tipo_tarifas ON guia_tarifas.idtarifa = tipo_tarifas.id";
        $xSQL .= " WHERE guia_tarifas.idguia = " .  $args["id"];
        $xSQL .= " ORDER BY tipo_tarifas.descripcion";
        $respuesta = dbGet($xSQL);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    // **** Agregar [POST]

    //Agregar una Guia
    $app->post("/guia", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "idciudad" => array(
                "numeric" => true,
                "mayorcero" => true,
                "tag" => "Identificador de Ciudad"
            ),
            "idtipo" => array(
                "numeric" => true,
                "mayorcero" => true,
                "tag" => "Identificador de Tipo"
            ),
            "legajo" => array(
                "min" => 1,
                "max" => 5,
                "tag" => "Legajo"
            ),
            "nombre" => array(
                "min" => 3,
                "max" => 100,
                "tag" => "Nombre"
            )
        );
        $validar = new Validate();
        if($validar->validar($request->getParsedBody(), $reglas)) {
            $parsedBody = $request->getParsedBody();
            //Verificar que el legajo no exista
            $xSQL = "SELECT guias.nombre, ciudades.nombre AS ciudad, departamentos.nombre AS departamento FROM guias";
            $xSQL .= " INNER JOIN ciudades ON guias.idciudad = ciudades.id";
            $xSQL .= " INNER JOIN departamentos ON ciudades.iddepartamento = departamentos.id";
            $xSQL .= " WHERE guias.legajo = '" . $parsedBody["legajo"] . "'";
            $existe = dbGet($xSQL);
            if($existe->data["count"] === 0) {
                $fecha = date("Y-m-d");
                $data = array(
                    "idciudad" => $parsedBody["idciudad"],
                    "idtipo" => $parsedBody["idtipo"],
                    "idvalortipcat" => 1, //Sin Categoría
                    "nombre" => $parsedBody["nombre"],
                    "legajo" => $parsedBody["legajo"],
                    "domicilio" => "",
                    "telefono" => "",
                    "habitaciones" => 0,
                    "camas" => 0,
                    "plazas" => 0,
                    "mail" => "",
                    "web" => "",
                    "latitud" => 0,
                    "longitud" => 0,
                    "descripcion" => "",
                    "logo" => "default.jpg",
                    "notas" => "",
                    "lupdate" => $fecha,
                    "iduser" => 1, //Reparar
                    "activo" => 1,
                    "p_nombre" => "",
                    "p_telefono" => "",
                    "p_mail" => "",
                    "p_domicilio" => "",
                    "p_dni" => "",
                    "r_nombre" => "",
                    "r_telefono" => "",
                    "r_mail" => "",
                    "r_domicilio" => "",
                    "r_dni" => "",
                    "r_cargo" => ""
                    //"r_vencimiento" => ""
                );
                $respuesta = dbPostWithData("guias", $data);
                return $response
                    ->withStatus(201) //Created
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            } else { //El Legajo ya está asigando a otra guía
                $resperr = new stdClass();
                $resperr->err = true;
                $resperr->errMsg = "El legajo " . $parsedBody["legajo"] . " ya esta asignado a " . $existe->data["registros"][0]->nombre . " en la ciudad: " . $existe->data["registros"][0]->ciudad . ", departamento: " . $existe->data["registros"][0]->departamento;
                return $response
                    ->withStatus(409) //Conflicto
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            }
        } else { //Datos insuficientes o incorrectos
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

    //Agregar un Servicio a una Guía en particular

    $app->post("/guia/{idGuia:[0-9]+}/servicio/add/{idServicio:[0-9]+}", function (Request $request, Response $response, array $args) {
        //Verificar que no posee el Servicio
        $xSQL = "SELECT guiaservicios.id, guias.nombre, servicios.descripcion FROM guiaservicios";
        $xSQL .= " INNER JOIN guias ON guiaservicios.idguia = guias.id";
        $xSQL .= " INNER JOIN servicios ON guiaservicios.idservicio = servicios.id";
        $xSQL .= " WHERE idguia = " . $args["idGuia"];
        $xSQL .= " AND idservicio = " . $args["idServicio"];
        $respuesta = dbGet($xSQL);
        if($respuesta->data["count"] === 0) {
        	$capacidad = 0;
        	$parsedBody = $request->getParsedBody();
        	if(isset($parsedBody["capacidad"]) && is_numeric($parsedBody["capacidad"])) {
        		$capacidad = $parsedBody["capacidad"];
        	}
            $data = array(
                "idguia" => $args["idGuia"],
                "idservicio" => $args["idServicio"],
                "capacidad" => $capacidad
            );
            $respuesta = dbPostWithData("guiaservicios", $data);
            return $response
                ->withStatus(201) //Created
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        } else { //La Guía ya posee el Servicio
            $resperr = new stdClass();
            $resperr->err = true;
            $resperr->errMsg = "El Servicio " . $respuesta->data["registros"][0]->descripcion . " ya existe en " . $respuesta->data["registros"][0]->nombre;
            return $response
                ->withStatus(409) //Conflicto
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    });

    //Agregar una Red Social a una Guía en particular

    $app->post("/guia/{idGuia:[0-9]+}/red/add/{idRed:[0-9]+}", function (Request $request, Response $response, array $args) {
        //Verificar que no posee la Red Social
        $xSQL = "SELECT guia_redes.id, redes.nombre, guias.nombre AS nombreguia FROM guia_redes";
        $xSQL .= " INNER JOIN redes ON guia_redes.idred = redes.id";
        $xSQL .= " INNER JOIN guias ON guia_redes.idguia = guias.id";
        $xSQL .= " WHERE guia_redes.idguia = " . $args["idGuia"];
        $xSQL .= " AND guia_redes.idred = " . $args["idRed"];
        $respuesta = dbGet($xSQL);
        if($respuesta->data["count"] === 0) {
            $parsedBody = $request->getParsedBody();
            if(isset($parsedBody["link"]) && (strlen($parsedBody["link"]) > 3)) {
                $inicio = strtoupper(substr($parsedBody["link"], 0, 7)); //HTTP://
                if($inicio !== "HTTP://") {
                    $data =  array(
                        "idguia" => $args["idGuia"],
                        "idred" => $args["idRed"],
                        "link" => "http://" . $parsedBody["link"]
                    );
                    $respuesta = dbPostWithData("guia_redes", $data);
                    return $response
                        ->withStatus(201) //Created
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

                } else {
                    $resperr = new stdClass();
                    $resperr->err = true;
                    $resperr->errMsg = "La dirección web suministrada debe tener al menos 3 caracteres y no debe comenzar con http://";
                    return $response
                        ->withStatus(409) //Conflicto
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                }
                    
            } else { //El link suministrado no es válido
                $resperr = new stdClass();
                $resperr->err = true;
                $resperr->errMsg = "La url (link) suministrada debe tener al menos 3 caracteres y no debe comenzar con http://";
                return $response
                    ->withStatus(409) //Conflicto
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            }
                
        } else { //La Guía ya posee el Servicio
            $resperr = new stdClass();
            $resperr->err = true;
            $resperr->errMsg = "La Red Social " . $respuesta->data["registros"][0]->nombre . " ya existe en " . $respuesta->data["registros"][0]->nombreguia . ".";
            return $response
                ->withStatus(409) //Conflicto
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    });

    //Agregar una Tarifa a una Guía en particular

    $app->post("/guia/{idGuia:[0-9]+}/tarifa/add/{idTarifa:[0-9]+}", function (Request $request, Response $response, array $args) {
        //Verificar que no posee la Tarifa
        $xSQL = "SELECT guia_tarifas.id, tipo_tarifas.descripcion FROM guia_tarifas";
        $xSQL .= " INNER JOIN tipo_tarifas ON guia_tarifas.idtarifa = tipo_tarifas.id";
        $xSQL .= " WHERE guia_tarifas.idguia = " . $args["idGuia"];
        $xSQL .= " AND guia_tarifas.idtarifa = " . $args["idTarifa"];
        $respuesta = dbGet($xSQL);
        if($respuesta->data["count"] === 0) {
        	$reglas = array(
	            "importe" => array(
	                "numeric" => true,
	                "tag" => "Importe"
	            ),
	            "desayuno" => array(
	                "tag" => "Desayuno"
	            )
	        );
	        $validar = new Validate();
	        $parsedBody = $request->getParsedBody();
	        if($validar->validar($parsedBody, $reglas)) {
                $data = array(
	                "idguia" => $args["idGuia"],
	                "idtarifa" => $args["idTarifa"],
	                "importe" => $parsedBody["importe"],
	                "desayuno" => $parsedBody["desayuno"],
                    "banioprivado" => $parsedBody["banioprivado"],
                    "mediapension" => $parsedBody["mediapension"],
                    "pensioncompleta" => $parsedBody["pensioncompleta"]
                );
                $respuesta = dbPostWithData("guia_tarifas", $data);
                //var_dump($respuesta);
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
        } else { //La Guía ya posee el Servicio
            $resperr = new stdClass();
            $resperr->err = true;
            $resperr->errMsg = "La Tarifa " . $respuesta->data["registros"][0]->descripcion . " ya existe";
            return $response
                ->withStatus(409) //Conflicto
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    });

    // **** Update [PATCH]

    //Aparentemente Slim no es muy gauchito con los métodos put y pacth
    //Si hay que subir un formulario que contenga por ej una imagen el metodo debe ser post
    //y se debe especificar el Content-Type a multipart/form-data en la cabecera

    //Actualizar una Guia
    $app->post("/guia/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
    	$parsedBody = $request->getParsedBody();
    	$uploadedFiles = $request->getUploadedFiles();

        //Validar los datos
        $reglas = array(
            "id" => array(
                "numeric" => true,
                "mayorcero" => true,
                "tag" => "Id"
            ),
            "idciudad" => array(
                "numeric" => true,
                "mayorcero" => true,
                "tag" => "Id de Ciudad"
            ),
            "idtipo" => array(
                "numeric" => true,
                "mayorcero" => true,
                "tag" => "Id de Tipo"
            ),
            "idvalortipcat" => array(
                "numeric" => true,
                "mayorcero" => true,
                "tag" => "Id de Valor Tipo de Valorización"
            ),
            "nombre" => array(
                "min" => 3,
                "max" => 100,
                "tag" => "Nombre/Razón Social"
            ),
            "legajo" => array(
                "min" => 1,
                "max" => 5,
                "tag" => "Legajo"
            ),
            "domicilio" => array(
                "max" => 100,
                "tag" => "Domicilio"
            ),
            "telefono" => array(
                "max" => 20,
                "tag" => "Teléfono"
            ),
            "habitaciones" => array(
                "numeric" => true,
                "tag" => "Habitaciones"
            ),
            "camas" => array(
                "numeric" => true,
                "tag" => "Camas"
            ),
            "plazas" => array(
                "numeric" => true,
                "tag" => "Plazas"
            ),
            "mail" => array(
                "max" => 150,
                "tag" => "EMail"
            ),
            "web" => array(
                "max" => 150,
                "tag" => "Web"
            ),
            "latitud" => array(
                "numeric" => true,
                "tag" => "(GEO) Latitud"
            ),
            "longitud" => array(
                "numeric" => true,
                "tag" => "(GEO) Longitud"
            ),
            "descripcion" => array(
                "tag" => "Descripcion"
            ),
            "notas" => array(
                "tag" => "Descripcion"
            ),
            "iduser" => array(
                "numeric" => true,
                "mayorcero" => true,
                "tag" => "Id de Usuario"
            ),
            "p_nombre" => array(
                "max" => 50,
                "tag" => "Nombre de Propietario"
            ),
            "p_telefono" => array(
                "max" => 20,
                "tag" => "Teléfono de Propietario"
            ),
            "p_mail" => array(
                "max" => 150,
                "tag" => "EMail de Propietario"
            ),
            "p_domicilio" => array(
                "max" => 50,
                "tag" => "Domicilio de Propietario"
            ),
            "p_dni" => array(
                "max" => 8,
                "tag" => "DNI de Propietario"
            ),

            "r_nombre" => array(
                "max" => 50,
                "tag" => "Nombre del Responsable"
            ),
            "r_telefono" => array(
                "max" => 20,
                "tag" => "Teléfono del Responsable"
            ),
            "r_mail" => array(
                "max" => 150,
                "tag" => "EMail del Responsable"
            ),
            "r_domicilio" => array(
                "max" => 50,
                "tag" => "Domicilio del Responsable"
            ),
            "r_dni" => array(
                "max" => 8,
                "tag" => "DNI del Responsable"
            ),
            "r_cargo" => array(
                "max" => 50,
                "tag" => "Cargo del Responsable"
            ),
            "r_vencimiento" => array(
                "tag" => "Fecha de vencimiento del cargo del Responsable"
            ),
            "epoca" => array(
                "tag" => "Época de Prestación de Servicio"
            )
        );
        $validar = new Validate();
        if($validar->validar($parsedBody, $reglas)) {
        	//El legajo puede ser modificado solo a un valor que no esté en uso por otro registro
        	$xSQL = "SELECT guias.nombre, ciudades.nombre AS ciudad, departamentos.nombre AS departamento FROM guias";
			$xSQL .= " INNER JOIN ciudades ON guias.idciudad = ciudades.id";
			$xSQL .= " INNER JOIN departamentos ON ciudades.iddepartamento = departamentos.id";
			$xSQL .= " WHERE legajo = '" . $parsedBody["legajo"] . "' AND guias.id <> " . $parsedBody["id"];
        	$respuesta = dbGet($xSQL);
        	if($respuesta->data["count"] === 0) {
        		//Imagen
		    	$directory = $this->get("upload_directory_logo");
		        $tamanio_maximo = $this->get("max_file_size");
		        $formatos_permitidos = $this->get("allow_file_format");
		        $uploadedFiles = $request->getUploadedFiles();
                $filename = "";
                $err = false;
                
		        if(isset($uploadedFiles["logo"])) {
		            $uploadedFile = $uploadedFiles["logo"];
		            if($uploadedFile->getError() === UPLOAD_ERR_OK) {
		                if($uploadedFile->getSize() <= $tamanio_maximo) {
		                    if(in_array($uploadedFile->getClientMediaType(), $formatos_permitidos)) {
		                        $filename = moveUploadedFile($directory, $uploadedFile, 1, $args["id"]);
		                    } else {
                                $err = true;
                            }
		                } else {
                            $err = true;
                        }
		            } else {
                        $err = true;
                    }
		        }

                if($err === false) {
                    $eliminar_viejo_logo = false;
                    $nombre_viejo_logo = $parsedBody["logo"];
                    if($filename == "") {
                        $filename = $nombre_viejo_logo;
                    } else {
                        $eliminar_viejo_logo = true;
                    }

                    //Actualización del registro
                    $fecha = date("Y-m-d");
                    $vencimiento = "2018-01-01";
                    if($parsedBody["r_vencimiento"] <> "") {
                        $vencimiento = $parsedBody["r_vencimiento"];
                    }
                    $data = array(
                        "idciudad" => $parsedBody["idciudad"],
                        "idtipo" => $parsedBody["idtipo"],
                        "idvalortipcat" => $parsedBody["idvalortipcat"],
                        "nombre" => $parsedBody["nombre"],
                        "legajo" => $parsedBody["legajo"],
                        "domicilio" => $parsedBody["domicilio"],
                        "telefono" => $parsedBody["telefono"],
                        "habitaciones" => $parsedBody["habitaciones"],
                        "camas" => $parsedBody["camas"],
                        "plazas" => $parsedBody["plazas"],
                        "mail" => $parsedBody["mail"],
                        "web" => $parsedBody["web"],
                        "latitud" => $parsedBody["latitud"],
                        "longitud" => $parsedBody["longitud"],
                        "descripcion" => $parsedBody["descripcion"],
                        "logo" => $filename,
                        "notas" => $parsedBody["notas"],
                        "lupdate" => $fecha,
                        "iduser" => $parsedBody["iduser"],
                        "activo" => $parsedBody["activo"],
                        "p_nombre" => $parsedBody["p_nombre"],
                        "p_telefono" => $parsedBody["p_telefono"],
                        "p_mail" => $parsedBody["p_mail"],
                        "p_domicilio" => $parsedBody["p_domicilio"],
                        "p_dni" => $parsedBody["p_dni"],
                        "r_nombre" => $parsedBody["r_nombre"],
                        "r_telefono" => $parsedBody["r_telefono"],
                        "r_mail" => $parsedBody["r_mail"],
                        "r_domicilio" => $parsedBody["r_domicilio"],
                        "r_dni" => $parsedBody["r_dni"],
                        "r_cargo" => $parsedBody["r_cargo"],
                        "r_vencimiento" => $vencimiento,
                        "epoca" => $parsedBody["epoca"]
                    );

                    $respuesta = dbPatchWithData("guias", $args["id"], $data);
                    if($respuesta->err == false) {
                        //Eliminar la vieja Imagen
                        if(($eliminar_viejo_logo == true) && ($nombre_viejo_logo <> "default.jpg")) {
                            @unlink($this->get("upload_directory_logo") . DIRECTORY_SEPARATOR . $nombre_viejo_logo);
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
                        $respuesta->logo = $filename;
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
                } else { //Ocurrió un error al subir la imágen
                    $resperr = new stdClass();
                    $resperr->err = true;
                    $resperr->errMsg = "Ocurrió un error al procesar la imágen, inténtelo nuevamente, recuerde que no pude superar los 4MB, debe ser un formato de imagen válido.";
                    return $response
                        ->withStatus(409) //Conflicto
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                }
        	} else {
        		$resperr = new stdClass();
	            $resperr->err = true;
	            $resperr->errMsg = "El Legajo " . $parsedBody["legajo"] . " pertenece a " . $respuesta->data["registros"][0]->nombre . " (Departamento: " . $respuesta->data["registros"][0]->departamento . ", Ciudad: " . $respuesta->data["registros"][0]->ciudad . ")";
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

    
    // **** Eliminar [DELETE]

    //Eliminar un Servicio de una Guía ** Ojo espera el id de la tabla guiaservicios, no el id del servicio que es otra cosa.
    $app->delete("/guia/servicio/{idTablaGuiaServicios:[0-9]+}", function (Request $request, Response $response, array $args) {
        $respuesta = dbDelete("guiaservicios", $args["idTablaGuiaServicios"]);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Eliminar una Red Social de una Guía ** Ojo espera el id de la tabla guia_redes, no el id de la Red que es otra cosa.
    $app->delete("/guia/red/{idTablaGuiaRedes:[0-9]+}", function (Request $request, Response $response, array $args) {
        $respuesta = dbDelete("guia_redes", $args["idTablaGuiaRedes"]);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Eliminar una Tarifa de una Guía ** Ojo espera el id de la tabla guia_tarifas, no el id de la Tarifa que es otra cosa.
    $app->delete("/guia/tarifa/{idTablaGuiaTarifas:[0-9]+}", function (Request $request, Response $response, array $args) {
        $respuesta = dbDelete("guia_tarifas", $args["idTablaGuiaTarifas"]);
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Eliminar una Guia
    //Las guías no se eliminan simplemente se pasa el campo activo a false 
    $app->delete("/guia/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        return $response
            ->withStatus(503)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode(array("Mensaje" => "Service Unavailable (En producción!)"), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });
?>