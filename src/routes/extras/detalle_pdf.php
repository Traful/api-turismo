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

.txt-info-td {
  font-size: 1.1em;
  padding: 6px;
  border-bottom: 1px solid #ccc; }

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
<div class="titulo">(<?php echo($guia->legajo); ?>) <?php echo($guia->nombre); ?></div>
<!-- Última actualización -->
<div class="subtitulo">Última actualización: <?php echo(ffecha($guia->lupdate)); ?> - Usuario: <?php echo($guia->usuario); ?></div>
<!-- Datos Fiscales -->
<table style="width: 100%;">
    <colgroup>
        <col style="width: 70%">
        <col style="width: 30%">
    </colgroup>
    <tbody>
        <tr>
            <td>
                <div class="txt-info"><span>Departamento:</span> <?php echo($guia->departamento); ?> (<?php echo($guia->cp); ?>)</div>
                <div class="txt-info"><span>Ciudad:</span> <?php echo($guia->ciudad); ?></div>
                <div class="txt-info"><span>Tipo:</span> <?php echo($guia->tipo); ?></div>
                <div class="txt-info"><span>Tipo de Categoría:</span> <?php echo($guia->tipocategorianombre); ?></div>
                <div class="txt-info"><span>Categoría:</span> <?php echo($guia->valortipcatdescripcion); ?></div>
                <div class="txt-info"><span>Domicilio:</span> <?php echo($guia->domicilio); ?></div>
                <div class="txt-info"><span>Teléfono:</span> (<?php echo($guia->caracteristica); ?>) <?php echo($guia->telefono); ?></div>
                <div class="txt-info"><span>EMail:</span> <?php echo($guia->mail); ?></div>
                <div class="txt-info"><span>Web:</span> http://<?php echo($guia->web); ?></div>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <img class="logo" src='<?php echo($directory . DIRECTORY_SEPARATOR . $guia->logo); ?>' height="150" width="150" alt="Logo" />
            </td>
        </tr>
    </tbody>
</table>
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
                <td>Registros: 1</td>
                <td>Habitaciones: <?php echo($guia->habitaciones); ?></td>
                <td>Camas: <?php echo($guia->camas); ?></td>
                <td>Plazas: <?php echo($guia->plazas); ?></td>
            </tr>
        </tbody>
    </table>
</div>
<!-- Datos del Propietario -->
<div class="subtitulo">Datos del Propietario</div>
<table style="width: 100%;">
    <colgroup>
        <col style="width: 50%">
        <col style="width: 50%">
    </colgroup>
    <tbody>
        <tr>
            <td>
                <div class="txt-info"><span>Apellido y Nombre:</span> <?php echo($guia->p_nombre); ?></div>
                <div class="txt-info"><span>Domicilio:</span> <?php echo($guia->p_domicilio); ?></div>
                <div class="txt-info"><span>Teléfono:</span> <?php echo($guia->p_telefono); ?></div>
            </td>
            <td>
                <div class="txt-info"><span>DNI:</span> <?php echo($guia->p_dni); ?></div>
                <div class="txt-info"><span>EMail:</span> <?php echo($guia->p_mail); ?></div>
            </td>
        </tr>
    </tbody>
</table>
<!-- Datos del Responsable -->
<div class="subtitulo">Datos del Responsable</div>
<table style="width: 100%;">
    <colgroup>
        <col style="width: 50%">
        <col style="width: 50%">
    </colgroup>
    <tbody>
        <tr>
            <td>
                <div class="txt-info"><span>Apellido y Nombre:</span> <?php echo($guia->r_nombre); ?></div>
                <div class="txt-info"><span>Domicilio:</span> <?php echo($guia->r_domicilio); ?></div>
                <div class="txt-info"><span>Teléfono:</span> <?php echo($guia->r_telefono); ?></div>
            </td>
            <td>
                <div class="txt-info"><span>DNI:</span> <?php echo($guia->r_dni); ?></div>
                <div class="txt-info"><span>EMail:</span> <?php echo($guia->r_mail); ?></div>
                <div class="txt-info"><span>Cargo:</span> <?php echo($guia->r_cargo); ?></div>
                <div class="txt-info"><span>Vencimiento del Cargo:</span> <?php echo(ffecha($guia->r_vencimiento)); ?></div>
            </td>
        </tr>
    </tbody>
</table>
</page>
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
<!-- Servicios -->
<div class="subtitulo">Servicios</div>
<?php
    if($servicios) {
        echo("<div>");
        $x = 0;
        for($i=0; $i<count($servicios); $i++) {
            $x++;
            if($servicios[$i]->capacidad == 0) {
                echo($servicios[$i]->descripcion);
            } else {
                echo($servicios[$i]->descripcion . " (" . $servicios[$i]->capacidad . ")");
            }
            if($x < count($servicios)) {
                echo("   //   ");
            }
        }
        echo("</div>");
    } else {
        echo("<div class='txt-info'>No se ha cargado ningún Servicio.</div>");
    }
?>
<!-- Redes Sociales -->
<div class="subtitulo">Redes Sociales</div>
<?php
    if($redes) {
        for($i=0; $i<count($redes); $i++) {
            echo("<div class='txt-info'><span>" . $redes[$i]->nombre . ":</span> " . $redes[$i]->link . "</div>");
        }
    } else {
         echo("<div class='txt-info'>No se ha cargado ninguna Red Social.</div>");
    }
?>
<!-- Tarifas -->
<div class="subtitulo">Tarifas</div>
<?php
    if($tarifas) {
        echo("<table style='width: 100%;'>");
        echo("<colgroup>");
        echo("<col style='width: 50%'>");
        echo("<col style='width: 25%'>");
        echo("<col style='width: 25%'>");
        echo("</colgroup>");
        echo("<tbody>");
        for($i=0; $i<count($tarifas); $i++) {
            echo("<tr>");
            echo("<td class='txt-info-td'>" . $tarifas[$i]->descripcion . "</td>");
            echo("<td class='txt-info-td'>$ " . number_format($tarifas[$i]->importe, 0, ",", ".") . "</td>");
            if($tarifas[$i]->desayuno) {
                echo("<td class='txt-info-td'>Con Desayuno</td>");
            } else {
                echo("<td class='txt-info-td'>Sin Desayuno</td>");
            }
            echo("</tr>");
        }
        echo("</tbody>");
        echo("</table>");
    } else {
            echo("<div class='txt-info'>No se ha cargado ninguna Tarifa.</div>");
    }
?>
<!-- Notas -->
<div class="subtitulo" style="margin-top: 20px;">Notas</div>
<div class="table-container">
    <?php
        if(strlen(trim($guia->notas)) > 0) {
            echo($guia->notas);
        } else {
            echo("No hay notass cargadas sobre " . $guia->p_nombre . ".");
        }
    ?>
</div>
</page>