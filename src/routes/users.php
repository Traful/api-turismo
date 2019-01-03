<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    use Firebase\JWT\JWT;
    //use Tuupola\Base62;
    
    $app->post("/user/login", function (Request $request, Response $response, array $args) {
        $reglas = array(
            "email" => array(
                "tag" => "Email"
            ),
            "password" => array(
                "tag" => "Contraseña"
            )
        );
        $validar = new Validate();
        if($validar->validar($request->getParsedBody(), $reglas)) {
            $email = $request->getParsedBody()["email"];
            $pass = $request->getParsedBody()["password"];
            $xSQL = "SELECT id, email, password, nombre, p_get, p_post, p_put, p_patch, p_delete, ingreso, idtipo FROM usuarios";
            $xSQL .= " WHERE activo = 1";
            $xSQL .= " AND email = '" . $email . "'";
            $xSQL .= " ORDER BY id";
            $resp = dbGet($xSQL);
            if($resp->data["count"] > 0) {
                $pass_valido = password_verify($pass, $resp->data["registros"][0]->password);
                if($pass_valido) {
                    //Generar Token
                    $now = new DateTime();
                    $future = new DateTime("now +8 hours");
                    //$server = $request->getServerParams();
                    //$jti = (new Base62)->encode(random_bytes(16));
                    $payload = [
                        "iat" => $now->getTimeStamp(),
                        "exp" => $future->getTimeStamp(),
                        //"jti" => (new Base62)->encode(random_bytes(16)), //No funciona con la versión PHP del servidor de Hostinger (5.6.38)
                        "jti" => "???",
                        //"sub" => $server["PHP_AUTH_USER"],
                        "sub" => "???",
                        "scope" => array(
                            "id" => $resp->data["registros"][0]->id,
                            "permisos" => array(
                                "GET" => $resp->data["registros"][0]->p_get,
                                "POST" => $resp->data["registros"][0]->p_get,
                                "PUT" => $resp->data["registros"][0]->p_get,
                                "PATCH" => $resp->data["registros"][0]->p_get,
                                "DELETE" => $resp->data["registros"][0]->p_get
                            ),
                            "idtipo" => $resp->data["registros"][0]->idtipo
                        )
                    ];
                    $secret = getenv("JWT_SECRET_KEY");
                    $token = JWT::encode($payload, $secret, "HS256");
                    //Devolución
                    $resperr = new stdClass();
                    $resperr->err = false;
                    $resperr->errMsg = "";
                    $resperr->errMsgs = [];
                    $resperr->data = array(
                        "id" => $resp->data["registros"][0]->id,
                        "email" => $resp->data["registros"][0]->email,
                        "nombre" => $resp->data["registros"][0]->nombre,
                        "permisos" => array(
                            "GET" => $resp->data["registros"][0]->p_get,
                            "POST" => $resp->data["registros"][0]->p_post,
                            "PUT" => $resp->data["registros"][0]->p_put,
                            "PATCH" => $resp->data["registros"][0]->p_patch,
                            "DELETE" => $resp->data["registros"][0]->p_delete
                        ),
                        "ingreso" => $resp->data["registros"][0]->ingreso,
                        "idtipo" => $resp->data["registros"][0]->idtipo,
                        "token" => "Bearer " . $token,
                        "expires" => $future->getTimeStamp()
                    );
                    return $response
                        ->withStatus(200) //Ok
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                } else {
                    $resperr = new stdClass();
                    $resperr->err = true;
                    $resperr->errMsg = "Contraseña no válida!";
                    $resperr->errMsgs = ["Contraseña no válida!"];
                    return $response
                        ->withStatus(409) //Conflicto
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($resperr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                }
            } else {
                $resperr = new stdClass();
                $resperr->err = true;
                $resperr->errMsg = "Email: " . $email . " no válido!";
                $resperr->errMsgs = ["Email: " . $email . " no válido!"];
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

    //Crear Nuevo Usuario
    $app->post("/user", function (Request $request, Response $response, array $args) {
        //Hay que asegurarse de que no se creen 2 usuarios con el mismo mail!!!
        $reglas = array(
            "email" => array(
                "min" => 5,
                "max" => 100,
                "tag" => "EMail"
            ),
            "password" => array(
                "max" => 15,
                "tag" => "Contraseña"
            ),
            "nombre" => array(
                "max" => 50,
                "tag" => "Nombre"
            )
        );
        $validar = new Validate();
        $parsedBody = $request->getParsedBody();
        if($validar->validar($parsedBody, $reglas)) {
            $data = array(
                "email" => $parsedBody["email"],
                "password" => password_hash($parsedBody["password"], PASSWORD_BCRYPT),
                "nombre" => $parsedBody["nombre"],
                "p_get" => true,
                "p_post" => true,
                "p_put" => true,
                "p_patch" => true,
                "p_delete" => true,
                "ingreso" => date("Y-m-d"),
                "idtipo" => 1,
                "activo" => 1
            );
            $respuesta = dbPostWithData("usuarios", $data);
            return $response
                ->withStatus(200) //Ok
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
    });

    //Eliminar un Usuario
    $app->delete("/user/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
        $respuesta = dbDelete("usuarios", $arg["id"]);
        return $response
            ->withStatus(200) //Ok
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });
?>