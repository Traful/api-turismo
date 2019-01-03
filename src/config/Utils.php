<?php
	function validar_fecha($fecha = "") {
		$tempDate = explode("-", $fecha);
		if(count($tempDate) == 3) {
			if(checkdate($tempDate[1], $tempDate[2], $tempDate[0])) {
				return $tempDate;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
?>