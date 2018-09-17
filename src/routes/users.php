<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    use Firebase\JWT\JWT;

    //Crear Nuevo Usuario
    $app->post("/user", function (Request $request, Response $response, array $args) {
        //Hay que asegurarse de que no se creen 2 usuarios con el mismo mail!!!
        $body = $request->getParsedBody();
        $data = array(
            "email" => $body["email"],
            "password" => password_hash($body["password"], PASSWORD_BCRYPT),
            "nombre" => $body["nombre"],
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
            ->withStatus(201) //Created
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });

    //Login (cambiar a post y verificar user y pass en la DB!)
    $app->post("/user/login", function (Request $request, Response $response, array $args) {
        $respuesta = new stdClass();
        $respuesta->err = false;
        $respuesta->errMsg = array(); //Ojo con esto!
        $respuesta->data = array(
            "id" => 0,
            "email" => "",
            "nombre" => "",
            "permisos" => array(
                "GET" => false,
                "POST" => false,
                "PUT" => false,
                "PATCH" => false,
                "DELETE" => false
            ),
            "ingreso" => "",
            "idtipo" => 0,
            "token" => "",
            "expires" => 0
        );
        $validar = new Validate();
        $reglas = array(
            "email" => array(
                "min" => 3,
                "tag" => "EMail"
            ),
            "password" => array(
                "min" => 3,
                "tag" => "Contraseña"
            )
        );
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
                    //$future = new DateTime("now +2 hours");
                    $future = new DateTime("now +8 hours");
                    //$server = $request->getServerParams();
                    //$jti = (new Base62)->encode(random_bytes(16));
                    $payload = [
                        "iat" => $now->getTimeStamp(),
                        "exp" => $future->getTimeStamp(),
                        //"jti" => $jti,
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
                    $respuesta->data = array(
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
                        "token" => $token,
                        "expires" => $future->getTimeStamp()
                    );
                } else {
                    $respuesta->err = true;
                    $respuesta->errMsg[] = "Contraseña no válida.";
                }
            } else {
                $respuesta->err = true;
                $respuesta->errMsg[] = "EMail no válido.";
            }
        } else {
            $respuesta->err = true;
            $respuesta->errMsg = $validar->errors(); //$validar->errors() ya es un array
        }
        return $response
            ->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($respuesta, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    });
?>