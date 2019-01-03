<?php
    class DB {
        //DB Conection Settings
        private $db_host = "localhost";
        private $db_name = "turismo";
        private $db_user_name = "root";
        private $db_user_password = "";
        
        //Class Settings
        private $conn = null;
        private $err_conn = false;
        private $results = null;
        private $num_registros = 0;
        private $err = false;
        private $err_msg = "";
        private $lastId = 0;

        public function connect() {
            $this->conn = null;
			try {
                $this->conn = new PDO("mysql:host=" . $this->db_host . ";dbname=" . $this->db_name, $this->db_user_name, $this->db_user_password);
                $this->conn->exec("set names utf8");
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch(PDOExeption $e) {
                $this->err_conn = true;
				$this->err_msg = $e->getMessage();
            }
            return $this;
        }

        public function consultar($sql) {
            $this->err = false;
            $this->err_msg = "";
            $this->results = null;
            $this->num_registros = 0;
            $this->lastId = 0;
            if(!$this->err_conn) {
                try {
                    $this->results = $this->conn->prepare($sql);
                    $this->results->execute();
                    $inicio_sql = strtoupper(substr($sql, 0, 6)); //DELETE INSERT SELECT
                    switch($inicio_sql) {
                        case "DELETE":
                            break;
                        case "INSERT":
                            $this->lastId = $this->conn->lastInsertId();
                            break;
                        case "SELECT":
                            $this->num_registros = $this->results->rowCount();
                            break;
                    }
                } catch(PDOExeption $e) {
                    $this->err = true;
                    $this->err_msg = $e->getMessage();
                }
            }
            return $this;
        }

        public function get_rows($opt = PDO::FETCH_OBJ) {
            if(!$this->err) {
                return $this->results->fetchAll($opt);
            } else {
                return false;
            }
        }

        public function getLastId() {
            return $this->lastId;
        }

        public function get_num_rows() {
            return $this->num_registros;
        }

        public function error() {
            return $this->err;
        }

        public function error_msg() {
            return $this->err_msg;
        }

        public function qt($valor) {
            if($valor === false) {
                return 0;
            } else if($valor === true) {
                return 1;
            } else {
                return $this->conn->quote($valor);
            }
        }

        public function close() {
            $this->conn = null;
        }
    }
?>
