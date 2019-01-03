<?php
	class Validate {
		private $source = null;
		private $err = false;
		private $errnum = false;
		private $msgsError = array();
		
		public function __construct() {
			
		}

		public function validar($origen, $data = array()) {
			$this->errnum = false;
			unset($this->msgsError);
			if(is_array($origen) && is_array($data)) {
				foreach($data as $item => $reglas) {
					if(!isset($origen[$item])) {
						if(isset($reglas["tag"])) {
							$this->msgsError[] = $reglas["tag"] . " no se encontró en la colección.";
						} else {
							$this->msgsError[] = $item . " no se encontró en la colección.";
						}
					} else {
						foreach($reglas as $regla => $regla_valor) {
							$valor = trim($origen[$item]);
							switch($regla) {
								case "min":
									if(strlen($valor) < $regla_valor) {
										if(isset($reglas["tag"])) {
											$this->msgsError[] = $reglas["tag"] . " debe tener al menos " . $regla_valor . " caracteres";
										} else {
											$this->msgsError[] = $item . " debe tener al menos " . $regla_valor . " caracteres";
										}
									}
									break;
								case "max":
									if(strlen($valor) > $regla_valor) {
										if(isset($reglas["tag"])) {
											$this->msgsError[] = $reglas["tag"] . " debe tener como máximo " . $regla_valor . " caracteres";
										} else {
											$this->msgsError[] = $item . " debe tener como máximo " . $regla_valor . " caracteres";
										}
									}
									break;
								case "numeric":
									if(!is_numeric($valor)) {
										if(isset($reglas["tag"])) {
											$this->msgsError[] = $reglas["tag"] . " no es un número válido.";
										} else {
											$this->msgsError[] = $item . " no es un número válido.";
										}
										$this->errnum = true;
									}
									break;
								case "date":
									//Probar
									$tempDate = explode('-', $valor);
									if(count($tempDate) != 3) {
										if(isset($reglas["tag"])) {
											$this->msgsError[] = $reglas["tag"] . " no es una fecha válida.";
										} else {
											$this->msgsError[] = $item . " no es una fecha válida.";
										}
									} else {
										//checkdate(month, day, year)
										if(!checkdate($tempDate[1], $tempDate[2], $tempDate[0])) {
											if(isset($reglas["tag"])) {
												$this->msgsError[] = $reglas["tag"] . " no es una fecha válida.";
											} else {
												$this->msgsError[] = $item . " no es una fecha válida.";
											}
										}
									}
									break;
								case "time":
									//Probar
									$tempTime = explode(':', $valor);
									if(count($tempTime) != 3) {
										if(isset($reglas["tag"])) {
											$this->msgsError[] = $reglas["tag"] . " no es una hora válida.";
										} else {
											$this->msgsError[] = $item . " no es una hora válida.";
										}
									} else {
										if(is_numeric($tempTime[0]) AND is_numeric($tempTime[1]) AND is_numeric($tempTime[2])) {
											$hora = intval($tempTime[0], 10);
											$minutos = intval($tempTime[1], 10);
											$segundos = intval($tempTime[2], 10);
											if($hora < 0 OR $hora > 23) {
												if(isset($reglas["tag"])) {
													$this->msgsError[] = $reglas["tag"] . " no es una hora válida.";
												} else {
													$this->msgsError[] = $item . " no es una hora válida.";
												}
											} else {
												if($minutos < 0 OR $minutos > 59) {
													if(isset($reglas["tag"])) {
														$this->msgsError[] = $reglas["tag"] . " no es una hora válida.";
													} else {
														$this->msgsError[] = $item . " no es una hora válida.";
													}
												} else {
													if($segundos < 0 OR $segundos > 59) {
														if(isset($reglas["tag"])) {
															$this->msgsError[] = $reglas["tag"] . " no es una hora válida.";
														} else {
															$this->msgsError[] = $item . " no es una hora válida.";
														}
													}
												}
											}
										} else {
											if(isset($reglas["tag"])) {
												$this->msgsError[] = $reglas["tag"] . " no es una hora válida.";
											} else {
												$this->msgsError[] = $item . " no es una hora válida.";
											}
										}
									}
									break;
								case "mayorcero":
									if(!$this->errnum) {
										if($valor <= 0) {
											if(isset($reglas["tag"])) {
												$this->msgsError[] = $reglas["tag"] . " debe ser mayor que cero.";
											} else {
												$this->msgsError[] = $item . " debe ser mayor que cero.";
											}
										}
									}
									break;
							}
						}
					}
				}
			} else {
				$this->msgsError[] = "No se especificaron los datos.";
			}
			if(empty($this->msgsError)) {
				return true;
			} else {
				return false;
			}
		}
		
		public function errors() {
			return $this->msgsError;
		}
	}
?>