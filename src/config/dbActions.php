<?php
    //require "db.php";
    function dbGet($xSQL) {
        $respuesta = new stdClass();
        $respuesta->err = false;
        $respuesta->errMsg = "";
        $respuesta->data = array(
            "count" => 0,
            "registros" => []
        );
        $db = new DB();
        $db->connect();
        if(!$db->error()) {
            if(!$db->consultar($xSQL)->error()) {
                $respuesta->data["count"] = $db->get_num_rows();
                $respuesta->data["registros"] = $db->get_rows();
            } else {
                $respuesta->err = true;
                $respuesta->errMsg = $db->error_msg();
            }
        } else {
            $respuesta->err = true;
            $respuesta->errMsg = $db->error_msg();
        }
        $db->close();
        return $respuesta;
    }

    function dbPatch($table, $id, $origen, $reglas) { //Inconclusa
        $respuesta = new stdClass();
        $respuesta->err = false;
        $respuesta->errMsg = "";
        $respuesta->validationErrMsgs = null;
        $validar = new Validate();
        $ok = $validar->validar($origen, $reglas);
        if($ok) {
            $db = new DB();
            $db->connect();
            if(!$db->error()) {
                $values = "";
                $x = 1;
                foreach($reglas as $campo => $val) {
                    $values .= $campo . " = '" . $origen[$campo] . "'";
                    if($x < count($reglas)) {
                        $values .= ", ";
                    }
                    $x++;
                }
                $xSQL = "UPDATE " . $table . " SET " . $values . " WHERE id = " . $id;
                /*
                if($db->consultar($xSQL)->error()) {
                    $respuesta->err = true;
                    $respuesta->errMsg = $db->error_msg();
                }
                */
                $respuesta->err = true;
                $respuesta->errMsg = $xSQL;
            } else {
                $respuesta->err = true;
                $respuesta->errMsg = $db->error_msg();
            }
            $db->close();
        } else {
            $respuesta->err = true;
            $respuesta->errMsg = "No se pasó la validación.";
            $respuesta->validationErrMsgs = $validar->errors();
        }
        return $respuesta;
    }

    function dbPatchWithData($table, $id, $origen) {
        $respuesta = new stdClass();
        $respuesta->err = false;
        $respuesta->errMsg = "";
        $db = new DB();
        $db->connect();
        if(!$db->error()) {
            $values = "";
            $x = 1;
            foreach($origen as $campo => $val) {
                $values .= $campo . " = " . $db->qt($val);
                if($x < count($origen)) {
                    $values .= ", ";
                }
                $x++;
            }
            $xSQL = "UPDATE " . $table . " SET " . $values . " WHERE id = " . $id;
            if($db->consultar($xSQL)->error()) {
                $respuesta->err = true;
                $respuesta->errMsg = $db->error_msg();
            }
        } else {
            $respuesta->err = true;
            $respuesta->errMsg = $db->error_msg();
        }
        $db->close();
        return $respuesta;
    }

    function dbPost($table, $origen, $reglas) { //Inconclusa!!!
        $respuesta = new stdClass();
        $respuesta->err = false;
        $respuesta->errMsg = "";
        $respuesta->validationErrMsgs = null;
        $respuesta->insertId = 0;
        $validar = new Validate();
        $ok = $validar->validar($origen, $reglas);
        if($ok) {
            $db = new DB();
            $db->connect();
            if(!$db->error()) {
                /*
                $values = "";
                $x = 1;
                foreach($reglas as $campo => $val) {
                    $values .= $campo . " = '" . $origen[$campo] . "'";
                    if($x < count($reglas)) {
                        $values .= ", ";
                    }
                    $x++;
                }
                */
                $campos = implode(", ", array_keys($reglas));
                $campos = "(" . $campos . ")";
                $valores = "";
                $x = 1;
                foreach($reglas as $campo => $val) {
                    $valores .= "'" . $origen[$campo] . "'";
                    if($x < count($reglas)) {
                        $values .= ", ";
                    }
                    $x++;
                }
                $valores = "(" . $valores . ")";
                $xSQL = "INSERT INTO " . $table . " " . $campos . " VALUES " . $valores;
                /*
                if($db->consultar($xSQL)->error()) {
                    $respuesta->err = true;
                    $respuesta->errMsg = $db->error_msg();
                }
                */
                $respuesta->err = true;
                $respuesta->errMsg = $xSQL;
            } else {
                $respuesta->err = true;
                $respuesta->errMsg = $db->error_msg();
            }
            $db->close();
        } else {
            $respuesta->err = true;
            $respuesta->errMsg = "No se pasó la validación.";
            $respuesta->validationErrMsgs = $validar->errors();
        }
        return $respuesta;
    }

    function dbPostWithData($table, $origen) {
        $respuesta = new stdClass();
        $respuesta->err = false;
        $respuesta->errMsg = "";
        $respuesta->insertId = 0;
        $db = new DB();
        $db->connect();
        if(!$db->error()) {
            $campos = implode(", ", array_keys($origen));
            $campos = "(" . $campos . ")";
            $valores = "";
            $x = 1;
            foreach($origen as $campo => $val) {
                $valores .= $db->qt($val);
                if($x < count($origen)) {
                    $valores .= ", ";
                }
                $x++;
            }
            $valores = "(" . $valores . ")";
            $xSQL = "INSERT INTO " . $table . " " . $campos . " VALUES " . $valores;
            if($db->consultar($xSQL)->error()) {
                $respuesta->err = true;
                $respuesta->errMsg = $db->error_msg();
            } else {
                $respuesta->insertId = $db->getLastId();
            }
        } else {
            $respuesta->err = true;
            $respuesta->errMsg = $db->error_msg();
        }
        $db->close();
        return $respuesta;
    }

    
    function dbDelete($table, $value, $field = "id", $compare = "=") {
        $respuesta = new stdClass();
        $respuesta->err = false;
        $respuesta->errMsg = "";
        $db = new DB();
        $db->connect();
        if(!$db->error()) {
            $xSQL = "DELETE FROM " . $table . " WHERE " . $field . " " . $compare . " " . $value;
            if($db->consultar($xSQL)->error()) {
                $respuesta->err = true;
                $respuesta->errMsg = $db->error_msg();
            }
        } else {
            $respuesta->err = true;
            $respuesta->errMsg = $db->error_msg();
        }
        $db->close();
        return $respuesta;
    }
?>
