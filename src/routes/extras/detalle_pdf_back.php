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
<style>
    .titulo {
      background: #444;
      padding: 14px;
      font-size: 22px;
      font-weight: 800;
      color: #fff;
      text-align: center;
      margin-bottom: 15px; }

    .txt, .txt-data {
      margin-bottom: 15px;
    }

    .txt-data-bg {
        background: #444;
        padding: 8px;
        color: #fff;
    }

    .logo {
        width: 200px;
        height: 200px;
    }

    table.presentacion {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px; }
      table.presentacion th, table.presentacion td {
        border: 1px solid #000;
        padding: 8px; }
        table.presentacion th.tdth-bg, table.presentacion td.tdth-bg {
          background: #444;
          color: #fff;
          text-align: right; }
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

<div class="titulo"><?php echo($guia->nombre); ?></div>
<div class="txt">Última actualización: <?php echo(ffecha($guia->lupdate)); ?> - Usuario: <?php echo($guia->usuario); ?></div>
<table class="presentacion">
    <colgroup>
        <col style="width: 20%">
        <col style="width: 30%">
        <col style="width: 20%">
        <col style="width: 30%">
    </colgroup>
    <tbody>
        <tr>
            <td rowspan="5" colspan="2"><img class="logo" src='<?php echo($directory); ?>\<?php echo($guia->logo); ?>' height="150" width="320" alt="Logo" /></td>
            <td class="tdth-bg">Departamento</td>
            <td><?php echo($guia->departamento); ?> (<?php echo($guia->cp); ?>)</td>
        </tr>
        <tr>
            <td class="tdth-bg">Ciudad</td>
            <td><?php echo($guia->ciudad); ?></td>
        </tr>
        <tr>
            <td class="tdth-bg">Legajo</td>
            <td><?php echo($guia->legajo); ?></td>
        </tr>
        <tr>
            <td class="tdth-bg">Tipo</td>
            <td><?php echo($guia->tipo); ?></td>
        </tr>
        <tr>
            <td class="tdth-bg">Teléfono</td>
            <td>(<?php echo($guia->caracteristica); ?>) <?php echo($guia->telefono); ?></td>
        </tr>

        <tr>
            <td class="tdth-bg">T. Valorización</td>
            <td><?php echo($guia->tipocategorianombre); ?></td>
            <td class="tdth-bg">Valorización</td>
            <td><?php echo($guia->valortipcatdescripcion); ?></td>
        </tr>

        <tr>
            <td class="tdth-bg">Domicilio</td>
            <td colspan="3"><?php echo($guia->domicilio); ?></td>
            
        </tr>
        <tr>
            <td class="tdth-bg">Email</td>
            <td colspan="3"><?php echo($guia->mail); ?></td>
        </tr>
        <tr>
            <td class="tdth-bg">Página Web</td>
            <td colspan="3">http://<?php echo($guia->web); ?></td>
        </tr>
    </tbody>
</table>
<table class="presentacion">
    <colgroup>
        <col style="width: 15%">
        <col style="width: 15%">
        <col style="width: 15%">
        <col style="width: 15%">
        <col style="width: 15%">
        <col style="width: 25%">
    </colgroup>
    <tbody>
        <tr>
            <td class="tdth-bg">Habitaciones</td>
            <td><?php echo($guia->habitaciones); ?></td>
            <td class="tdth-bg">Camas</td>
            <td><?php echo($guia->camas); ?></td>
            <td class="tdth-bg">Plazas</td>
            <td><?php echo($guia->plazas); ?></td>
        </tr>
    </tbody>
</table>
<div class="txt">Datos del Propietario:</div>
<table class="presentacion">
    <colgroup>
        <col style="width: 25%">
        <col style="width: 45%">
        <col style="width: 10%">
        <col style="width: 20%">
    </colgroup>
    <tbody>
        <tr>
            <td class="tdth-bg">Apellido y Nombre</td>
            <td><?php echo($guia->p_nombre); ?></td>
            <td class="tdth-bg">Teléfono</td>
            <td><?php echo($guia->p_telefono); ?></td>
        </tr>
        <tr>
            <td class="tdth-bg">Domicilio</td>
            <td><?php echo($guia->p_domicilio); ?></td>
            <td class="tdth-bg">DNI</td>
            <td><?php echo($guia->p_dni); ?></td>
        </tr>
        <tr>
            <td class="tdth-bg">EMail</td>
            <td colspan="3"><?php echo($guia->p_mail); ?></td>
        </tr>
    </tbody>
</table>
<div class="txt">Datos del Responsable:</div>
<table class="presentacion">
    <colgroup>
        <col style="width: 25%">
        <col style="width: 40%">
        <col style="width: 15%">
        <col style="width: 20%">
    </colgroup>
    <tbody>
        <tr>
            <td class="tdth-bg">Apellido y Nombre</td>
            <td><?php echo($guia->r_nombre); ?></td>
            <td class="tdth-bg">Teléfono</td>
            <td><?php echo($guia->r_telefono); ?></td>
        </tr>
        <tr>
            <td class="tdth-bg">Domicilio</td>
            <td><?php echo($guia->r_domicilio); ?></td>
            <td class="tdth-bg">DNI</td>
            <td><?php echo($guia->r_dni); ?></td>
        </tr>
        <tr>
            <td class="tdth-bg">Cargo</td>
            <td><?php echo($guia->r_cargo); ?></td>
            <td class="tdth-bg">Vencimiento</td>
            <td><?php echo(ffecha($guia->r_vencimiento)); ?></td>
        </tr>
        <tr>
            <td class="tdth-bg">EMail</td>
            <td colspan="3"><?php echo($guia->r_mail); ?></td>
        </tr>
    </tbody>
</table>
<div class="txt">Servicios:</div>
<div class="txt-data txt-data-bg">
    <?php
        if($servicios) {
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
        } else {
            echo("<div class='txt'>No se ha cargado ningún Servicio. O el alojamiento no posee ninguno.</div>");
        }
    ?>
</div>
<div class="txt">Redes Sociales:</div>
<?php
    if($redes) {
        echo("<table class='presentacion'>");
        echo("<colgroup>");
        echo("<col style='width: 25%'>");
        echo("<col style='width: 75%'>");
        echo("</colgroup>");
        echo("<tbody>");
        for($i=0; $i<count($redes); $i++) {
            echo("<tr>");
            echo("<td class='tdth-bg'>" . $redes[$i]->nombre . "</td>");
            echo("<td>" . $redes[$i]->link . "</td>");
            echo("</tr>");
        }
        echo("</tbody>");
        echo("</table>");
    } else {
            echo("<div class='txt'>No se ha cargado ninguna Red Social. O el alojamiento no posee ninguna.</div>");
    }
?>
<div class="txt">Tarifas:</div>
<?php
    if($tarifas) {
        echo("<table class='presentacion'>");
        echo("<colgroup>");
        echo("<col style='width: 50%'>");
        echo("<col style='width: 25%'>");
        echo("<col style='width: 25%'>");
        echo("</colgroup>");
        echo("<tbody>");
        for($i=0; $i<count($tarifas); $i++) {
            echo("<tr>");
            echo("<td class='tdth-bg'>" . $tarifas[$i]->descripcion . "</td>");
            echo("<td>$ " . number_format($tarifas[$i]->importe, 0, ",", ".") . "</td>");
            if($tarifas[$i]->desayuno) {
                echo("<td>Con Desayuno</td>");
            } else {
                echo("<td>Sin Desayuno</td>");
            }
            echo("</tr>");
        }
        echo("</tbody>");
        echo("</table>");
    } else {
            echo("<div class='txt'>No se ha cargado ninguna Tarifa. O el alojamiento no ha suministrado dichos datos.</div>");
    }
?>
<div class="txt">Notas Internas:</div>
<div class="txt-data">
    <?php
        if(strlen(trim($guia->notas)) > 0) {
            echo($guia->notas);
        } else {
            echo("Sin observaciones.");
        }
    ?>
</div>
</page>

