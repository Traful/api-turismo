<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    use Spipu\Html2Pdf\Html2Pdf;

    //Filtros (PDF)
    $app->post("/filtro", function (Request $request, Response $response, array $args) {
    	$parsedBody = $request->getParsedBody();
    	$xSQL = "SELECT guias.id, guias.idtipo, guias.nombre, guias.legajo, guias.domicilio, guias.telefono, guias.habitaciones, guias.camas, guias.plazas, guias.mail, guias.web";
        $xSQL .= ", guias.latitud, guias.longitud, guias.logo, guias.notas, guias.lupdate, guias.activo, guias.p_nombre, guias.p_telefono, guias.p_mail, guias.p_domicilio";
        $xSQL .= ", guias.p_dni, guias.r_nombre, guias.r_telefono, guias.r_mail, guias.r_domicilio, guias.r_dni, guias.r_cargo, guias.r_vencimiento";
        $xSQL .= ", ciudades.nombre as ciudad, ciudades.caracteristica, ciudades.cp, departamentos.nombre as departamento, usuarios.nombre as usuario";
        $xSQL .= ", tipos.descripcion as tipo, tiposcategorias.descripcion as tipocategorianombre, valortipcat.descripcion as valortipcatdescripcion";
        //Servicios
        $servicios = [];
        $having = "";
        $has_having = false;
        if(isset($parsedBody["idsChecked"]) && (trim($parsedBody["idsChecked"]) != "")) {
        	$campos = explode (",", $parsedBody["idsChecked"]);
        	if(is_array($campos)) {
        		foreach($campos as $valor) {
        			if(trim($valor) != "0") {
        				if(!$has_having) {
							$having .= " HAVING Total" . $valor . " > 0";
							$has_having = true;
						} else {
							$having .= " AND Total" . $valor . " > 0";
						}
        				if(isset($parsedBody["ServCant-" . $valor]) && (trim($parsedBody["ServCant-". $valor]) != "0") && (is_numeric($parsedBody["ServCant-". $valor]))) {
    						$xSQL .= ", (SELECT COUNT(*) FROM guiaservicios WHERE guiaservicios.idguia = guias.id AND guiaservicios.idservicio = " . $valor . " AND guiaservicios.capacidad >= " . $parsedBody["ServCant-" . $valor] . ") AS Total" . $valor;
    						//El nombre del Servicio
    						$ns = dbGet("SELECT descripcion FROM servicios WHERE id = " . $valor);
    						$servicios[] = "Que posea: " . $ns->data["registros"][0]->descripcion . " con capacidad mayor o igual a " . trim($parsedBody["ServCant-". $valor]);
    					} else {
    						$xSQL .= ", (SELECT COUNT(*) FROM guiaservicios WHERE guiaservicios.idguia = guias.id AND guiaservicios.idservicio = " . $valor . ") AS Total" . $valor;
    						//El nombre del Servicio
    						$ns = dbGet("SELECT descripcion FROM servicios WHERE id = " . $valor);
    						$servicios[] = "Que posea: " . $ns->data["registros"][0]->descripcion;
    					}
        			}
        		}
        	}
        }
        $xSQL .= " FROM guias";
        $xSQL .= " INNER JOIN ciudades ON guias.idciudad = ciudades.id";
        $xSQL .= " INNER JOIN departamentos ON ciudades.iddepartamento = departamentos.id";
        $xSQL .= " INNER JOIN usuarios ON guias.iduser = usuarios.id";
        $xSQL .= " INNER JOIN tipos ON guias.idtipo = tipos.id";
        $xSQL .= " INNER JOIN valortipcat ON guias.idvalortipcat = valortipcat.id";
        $xSQL .= " INNER JOIN tiposcategorias ON valortipcat.idtipcat = tiposcategorias.id";
        $xSQL .= " WHERE guias.activo = 1";
		$filtros = [];
		function addxSQL(&$xSQL, $field, $value, array $opt_filtro, &$arr_filtro, $comparacion = "=") {
			if((trim($value) != "") && ($value != "0")) {
       			$xSQL .=" AND " .  $field . " " . $comparacion . " " . $value;
       			$arr_filtro[] = array($opt_filtro["nombre"], $opt_filtro["campo"]);
			} else {
				$arr_filtro[] = array($opt_filtro["nombre"], false);
			}
		}
		//Departamento
		addxSQL($xSQL, "departamentos.id", $parsedBody["idDepartamento"], array("nombre" => "Departamento", "campo" => "departamento"), $filtros);
        //Ciudad
        addxSQL($xSQL, "guias.idciudad", $parsedBody["idCiudad"], array("nombre" => "Ciudad", "campo" => "ciudad"), $filtros);
        //Tipo
        addxSQL($xSQL, "guias.idtipo", $parsedBody["idTipo"], array("nombre" => "Tipo", "campo" => "tipo"), $filtros);
        //Tipo de Valorizacion
        addxSQL($xSQL, "tiposcategorias.id", $parsedBody["idTipoValorizacion"], array("nombre" => "Tipo Valorización", "campo" => "tipocategorianombre"), $filtros);
        //Valorizacion
        addxSQL($xSQL, "guias.idvalortipcat", $parsedBody["idValorizacion"], array("nombre" => "Valorización", "campo" => "valortipcatdescripcion"), $filtros);
        //Habitaciones
        addxSQL($xSQL, "guias.habitaciones", $parsedBody["habitaciones"], array("nombre" => "Habitaciones", "campo" => $parsedBody["habitaciones"]), $filtros, ">=");
        //Camas
        addxSQL($xSQL, "guias.camas", $parsedBody["camas"], array("nombre" => "Camas", "campo" => $parsedBody["camas"]), $filtros, ">=");
        //Plazas
        addxSQL($xSQL, "guias.plazas", $parsedBody["plazas"], array("nombre" => "Plazas", "campo" => $parsedBody["plazas"]), $filtros, ">=");
        //Having
        $xSQL .= $having;
        //Orden
        $xSQL .= " ORDER BY departamentos.nombre, ciudades.nombre, guias.nombre";
        $resultado = dbGet($xSQL);
        if((!$resultado->err) && $resultado->data["count"] > 0) {
            $reg_tipos = dbGet("SELECT id, descripcion FROM tipos ORDER BY descripcion");
            $reg_tipos = $reg_tipos->data["registros"];
            $buffer_tipos = [];
            foreach($reg_tipos as $tipo) {
                $buffer_tipos[] = array("id" => $tipo->id, "descripcion" => $tipo->descripcion, "cantidad" => 0); 
            }
        	ob_start();
	        $pdf_registros = $resultado->data["registros"];
	        $pdf_filtros = $filtros;
            $pdf_servicios = $servicios;
            $pdf_tipos = $buffer_tipos;
	        include dirname(__FILE__) . "/extras/filtro_pdf.php";
	        $header = ob_get_clean();

	        $html2pdf = new Html2Pdf("P", "A4", "es");
	        $html2pdf->writeHTML($header);
	        $html2pdf->output();
        } else {
        	ob_start();
	        include dirname(__FILE__) . "/extras/error_pdf.php";
	        $header = ob_get_clean();

	        //$html2pdf = new Html2Pdf("P", "A4", "es");
	        $html2pdf = new Html2Pdf("L", "A4", "es");
	        $html2pdf->writeHTML($header);
	        $html2pdf->output();
        }
	    return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/pdf");   
    });

    //Detalles de una Guia (PDF)
    $app->get("/detalle/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        //Datos de la Guía
        $xSQL = "SELECT guias.nombre, guias.legajo, guias.domicilio, guias.telefono, guias.habitaciones, guias.camas, guias.plazas, guias.mail, guias.web";
        $xSQL .= ", guias.latitud, guias.longitud, guias.logo, guias.notas, guias.lupdate, guias.activo, guias.p_nombre, guias.p_telefono, guias.p_mail, guias.p_domicilio";
        $xSQL .= ", guias.p_dni, guias.r_nombre, guias.r_telefono, guias.r_mail, guias.r_domicilio, guias.r_dni, guias.r_cargo, guias.r_vencimiento";
        $xSQL .= ", ciudades.nombre as ciudad, ciudades.caracteristica, ciudades.cp, departamentos.nombre as departamento, usuarios.nombre as usuario";
        $xSQL .= ", tipos.descripcion as tipo, tiposcategorias.descripcion as tipocategorianombre, valortipcat.descripcion as valortipcatdescripcion";
        $xSQL .= " FROM guias INNER JOIN ciudades ON guias.idciudad = ciudades.id";
        $xSQL .= " INNER JOIN departamentos ON ciudades.iddepartamento = departamentos.id";
        $xSQL .= " INNER JOIN usuarios ON guias.iduser = usuarios.id";
        $xSQL .= " INNER JOIN tipos ON guias.idtipo = tipos.id";
        $xSQL .= " INNER JOIN valortipcat ON guias.idvalortipcat = valortipcat.id";
        $xSQL .= " INNER JOIN tiposcategorias ON valortipcat.idtipcat = tiposcategorias.id";
        $xSQL .= " WHERE guias.id = " . $args["id"];
        $reg_guia = dbGet($xSQL);
        if($reg_guia->data["count"] > 0) {
            $reg_guia = $reg_guia->data["registros"][0];
        } else {
            $reg_guia = false;
        }
        $reg_servicios = false;
        $reg_redes = false;
        $reg_tarifas = false;
        if($reg_guia) {
            //Servicios de la Guía
            $xSQL = "SELECT guiaservicios.capacidad, servicios.descripcion FROM guiaservicios";
            $xSQL .= " INNER JOIN servicios ON guiaservicios.idservicio = servicios.id";
            $xSQL .= " WHERE guiaservicios.idguia = " . $args["id"];
            $xSQL .= " ORDER BY servicios.descripcion";
            $reg_servicios = dbGet($xSQL);
            if($reg_servicios->data["count"] > 0) {
                $reg_servicios = $reg_servicios->data["registros"];
            } else {
                $reg_servicios = false; 
            }
            //Redes Sociales de la Guía
            $xSQL = "SELECT guia_redes.link, redes.nombre, redes.icono FROM guia_redes";
            $xSQL .= " INNER JOIN redes ON guia_redes.idred = redes.id";
            $xSQL .= " WHERE guia_redes.idguia = " . $args["id"];
            $xSQL .= " ORDER BY redes.nombre";
            $reg_redes = dbGet($xSQL);
            if($reg_redes->data["count"] > 0) {
                $reg_redes = $reg_redes->data["registros"];
            } else {
                $reg_redes = false; 
            }
            //Tarifas de la Guía
            $xSQL = "SELECT guia_tarifas.importe, guia_tarifas.desayuno, tipo_tarifas.descripcion FROM guia_tarifas";
            $xSQL .= " INNER JOIN tipo_tarifas ON guia_tarifas.idtarifa = tipo_tarifas.id";
            $xSQL .= " WHERE guia_tarifas.idguia = " . $args["id"];
            $xSQL .= " ORDER BY tipo_tarifas.descripcion";
            $reg_tarifas = dbGet($xSQL);
            if($reg_tarifas->data["count"] > 0) {
                $reg_tarifas = $reg_tarifas->data["registros"];
            } else {
                $reg_tarifas = false; 
            }
        }

        ob_start();
        $guia = $reg_guia;
        $servicios = $reg_servicios;
        $redes = $reg_redes;
        $tarifas = $reg_tarifas;
        $directory = $this->get("upload_directory_logo");
        include dirname(__FILE__) . "/extras/detalle_pdf.php";
        $header = ob_get_clean();

        $html2pdf = new Html2Pdf("P", "A4", "es");
        $html2pdf->writeHTML($header);
        $html2pdf->output();

        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/pdf");
    });

?>