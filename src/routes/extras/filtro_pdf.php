<?php
    $meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    $mes = $meses[date("m") -1];
    $dia = date("d");
    $anio = date("Y");
    function ffecha($fecha) {
        $res = explode("-", $fecha);
        return $res[2] . "/" . $res[1] . "/" . $res[0];
    }
?>
<style>
    div {
  margin-bottom: 20px; }

.container {
  width: 800px;
  margin: 10px auto; }

.titulo {
  padding: 10px;
  background: #67223D;
  color: #fff;
  text-align: center;
  text-transform: uppercase;
  /*font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;*/
  font-weight: 800;
  font-size: 1.4em; }

.subtitulo {
  color: #fff;
  font-weight: 600;
  background: #67223D;
  padding: 8px; }

.txt-info {
  font-size: 1.1em;
  border-bottom: 1px solid #ccc; }
  .txt-info span {
    padding: 6px;
    font-weight: 800;
    font-style: italic;
    color: #04050B; }

.table-container {
  font-size: 1.1em;
  border-bottom: 1px solid #ccc; }
  .table-container .t-dato-titulo {
    background-color: #141B3D;
    text-align: center;
    color: #fff;
    padding: 8px; }
  .table-container table {
    width: 100%; }
</style>
<page backtop="10mm" backbottom="17mm" backleft="10mm" backright="10mm">
<page_header>
    <div class="header">
        <table style="width: 100%;">
            <colgroup>
                <col style="width: 50%">
                <col style="width: 50%">
            </colgroup>
            <tbody>
                <tr>
                    <td>Argentina / San Luis / Ministerio de Turismo</td>
                    <td align="right"><?php echo($dia); ?> de <?php echo($mes); ?> de <?php echo($anio); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</page_header>
<page_footer>SisTur &copy; <?php echo($anio); ?></page_footer>

<!-- Titulo -->
<div class="titulo">Ministerio de Turismo</div>
<!-- Opcones Filtro -->
<div class="subtitulo">Opciones de Filtrado</div>
<?php
    $fil = new stdClass();
    $fil->departamento = array("nombre" => "Departamento:", "valor" => "Todos los departamentos");
    $fil->ciudad = array("nombre" => "Ciudad:", "valor" => "Todas las ciudades");
    $fil->tipo = array("nombre" => "Tipo:", "valor" => "Todos los tipos");
    $fil->tval = array("nombre" => "Tipo de Categoría:", "valor" => "Todos los Tip. de Categoría");
    $fil->val = array("nombre" => "Categoría:", "valor" => "Todas las Categorías");
    $fil->habitaciones = array("nombre" => "Habitaciones:", "valor" => "Las que posean");
    $fil->camas = array("nombre" => "Camas:", "valor" => "Las que posean");
    $fil->plazas = array("nombre" => "Plazas:", "valor" => "Las que posean");
    //var_dump($pdf_filtros);
    for($i=0; $i<count($pdf_filtros); $i++) {
        if($pdf_filtros[$i][1] !== false) {
            switch($pdf_filtros[$i][0]) {
                case "Departamento";
                    $fil->departamento["valor"] = "Solo " .$pdf_registros[0]->{$pdf_filtros[$i][1]};
                    break;
                case "Ciudad";
                    $fil->ciudad["valor"] = "Solo " . $pdf_registros[0]->{$pdf_filtros[$i][1]};
                    break;
                case "Tipo";
                    $fil->tipo["valor"] = "Solo " . $pdf_registros[0]->{$pdf_filtros[$i][1]};
                    break;
                case "Tipo Valorización";
                    $fil->tval["valor"] = "Solo " . $pdf_registros[0]->{$pdf_filtros[$i][1]};
                    break;
                case "Valorización";
                    $fil->val["valor"] = "Solo " . $pdf_registros[0]->{$pdf_filtros[$i][1]};
                    break;
                case "Habitaciones";
                    $fil->habitaciones["valor"] = "Al menos " . $pdf_filtros[$i][1] . " habitaciones";
                    break;
                case "Camas";
                    $fil->camas["valor"] = "Al menos " . $pdf_filtros[$i][1] . " camas";
                    break;
                case "Plazas";
                    $fil->plazas["valor"] = "Al menos " . $pdf_filtros[$i][1] . " plazas";
                    break;
            }
        }
    }
?>
<div>
    <table style="width: 100%;">
        <colgroup>
            <col style="width: 50%">
            <col style="width: 50%">
        </colgroup>
        <tbody>
            <tr>
                <td>
                    <div class="txt-info"><span><?php echo($fil->departamento["nombre"]); ?></span> <?php echo($fil->departamento["valor"]); ?></div>
                    <div class="txt-info"><span><?php echo($fil->ciudad["nombre"]); ?></span> <?php echo($fil->ciudad["valor"]); ?></div>
                    <div class="txt-info"><span><?php echo($fil->tipo["nombre"]); ?></span> <?php echo($fil->tipo["valor"]); ?></div>
                    <div class="txt-info"><span><?php echo($fil->tval["nombre"]); ?></span> <?php echo($fil->tval["valor"]); ?></div>
                </td>
                <td>
                    <div class="txt-info"><span><?php echo($fil->val["nombre"]); ?></span> <?php echo($fil->val["valor"]); ?></div>
                    <div class="txt-info"><span><?php echo($fil->habitaciones["nombre"]); ?></span> <?php echo($fil->habitaciones["valor"]); ?></div>
                    <div class="txt-info"><span><?php echo($fil->camas["nombre"]); ?></span> <?php echo($fil->camas["valor"]); ?></div>
                    <div class="txt-info"><span><?php echo($fil->plazas["nombre"]); ?></span> <?php echo($fil->plazas["valor"]); ?></div>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="txt-info">
        <span>Servicios Seleccionados:</span>
        <?php
            if(count($servicios) > 0) {
                for($i=0;$i<count($servicios);$i++) {
                    echo("<p>" . $servicios[$i] . "</p>");
                }
            } else {
                echo("No se seleccionaron servicios a filtrar.");
            }
        ?>
    </div>
</div>
<!-- Registros -->
<div class="subtitulo">Registros que cumplen las opciones de filtrado</div>
<!-- Tabla -->

<?php
    $registros = count($pdf_registros);
    $sta = new stdClass();
    $sta->habitaciones = 0;
    $sta->camas = 0;
    $sta->plazas = 0;

    foreach($pdf_registros as $guia) {
        echo("<div class='table-container'>");
        echo("<div class='t-dato-titulo'>(" . $guia->legajo . ") " . $guia->nombre . "</div>");
        echo("<table>");
        echo("<colgroup>");
        echo("<col style='width: 50%'>");
        echo("<col style='width: 50%'>");
        echo("</colgroup>");
        echo("<tbody>");
        echo("<tr>");
        echo("<td>");
        echo("<div class='t-dato'>Departamento: " . $guia->departamento . "</div>");
        echo("<div class='t-dato'>Ciudad: " . $guia->ciudad . "</div>");
        echo("<div class='t-dato'>Tipo: " . $guia->tipo . "</div>");
        for($i=0; $i<count($pdf_tipos); $i++) {
            if($pdf_tipos[$i]["id"] === $guia->idtipo) {
                $pdf_tipos[$i]["cantidad"]++;
                break;
            }
        }
        echo("<div class='t-dato'>T. de Categoría: " . $guia->tipocategorianombre . "</div>");
        echo("<div class='t-dato'>Categoría: " . $guia->valortipcatdescripcion . "</div>");
        echo("</td>");
        echo("<td>");
        echo("<div class='t-dato'>Domicilio: " . $guia->domicilio . "</div>");
        echo("<div class='t-dato'>Teléfono: (" . $guia->caracteristica . ") " . $guia->telefono . "</div>");
        echo("<div class='t-dato'>EMail: " . $guia->mail . "</div>");
        echo("<div class='t-dato'>Web: " . $guia->web . "</div>");
        echo("<div class='t-dato'>Habitaciones: " . $guia->habitaciones . " // Camas: " . $guia->camas . " // Plazas: " . $guia->plazas . "</div>");
        $sta->habitaciones = $sta->habitaciones + $guia->habitaciones;
        $sta->camas = $sta->camas + $guia->camas;
        $sta->plazas = $sta->plazas + $guia->plazas;
        echo("</td>");
        echo("</tr>");
        echo("</tbody>");
        echo("</table>");
        echo("<div class='t-dato-servicios'>Servicios: ");
        //Obtener todos los servicios
        $xSQL = "SELECT guiaservicios.capacidad, servicios.descripcion FROM guiaservicios";
        $xSQL .= " INNER JOIN servicios ON guiaservicios.idservicio = servicios.id";
        $xSQL .= " WHERE guiaservicios.idguia = " . $guia->id;
        $xSQL .= " ORDER BY servicios.descripcion";
        $reg_servicios = dbGet($xSQL);
        $x = 0;
        foreach($reg_servicios->data["registros"] as $servs) {
            if($servs->capacidad > 0) {
                echo($servs->descripcion . " (" . $servs->capacidad . ")");
            } else {
                echo($servs->descripcion);
            }
            if($x < (count($reg_servicios->data["registros"]) - 1)) {
                echo(", ");
            }
            $x++;
        }
        echo("</div>");
        echo("</div>");
    }
?>
<!-- Estadísticas -->
<div class="subtitulo">Estadísticas</div>
<div class="table-container">
    <table style="width: 100%;">
        <colgroup>
            <col style="width: 25%">
            <col style="width: 25%">
            <col style="width: 25%">
            <col style="width: 25%">
        </colgroup>
        <tbody>
            <tr>
                <td>Registros: <?php echo($registros); ?></td>
                <td>Habitaciones: <?php echo($sta->habitaciones); ?></td>
                <td>Camas: <?php echo($sta->camas); ?></td>
                <td>Plazas: <?php echo($sta->plazas); ?></td>
            </tr>
        </tbody>
    </table>
</div>
<?php
    foreach($pdf_tipos as $tipo) {
        if($tipo["cantidad"] > 0) {
            echo("<div class='txt-info'>");
            $porcentaje = ($tipo["cantidad"] * 100) / $registros;
            $porcentaje = number_format($porcentaje, 2, ",", ".");
            echo("Total " . $tipo["descripcion"] . ": " . $tipo["cantidad"] . " ( " . $porcentaje . "% )");
            echo("</div>");
        }
    }
?>
</page>