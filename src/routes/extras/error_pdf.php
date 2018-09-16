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
    .error {
      background: #dc3545;
      padding: 14px;
      font-size: 22px;
      font-weight: 800;
      color: #fff;
      text-align: center;
      margin-bottom: 15px; }
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
<div class="error">
    <p>Lo siento</p>
    <p>No hay registros que cumplan los criterios seleccionados.</p>
</div>
</page>

