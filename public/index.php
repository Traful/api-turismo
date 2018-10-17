<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    use Firebase\JWT\JWT;

    require "../vendor/autoload.php";
    require "../src/config/db.php";
    require "../src/config/dbActions.php";
    require "../src/config/Validate.php";

    $dotenv = new Dotenv\Dotenv(__DIR__);
    $dotenv->load();


    date_default_timezone_set("America/Argentina/San_Luis");
    $configuration = [
        'settings' => [
            'displayErrorDetails' => true
        ],
    ];
    $c = new \Slim\Container($configuration);

    $app = new \Slim\App($c);
    //$app = new \Slim\App;

    $container = $app->getContainer();
    $container["upload_directory"] = __DIR__ . DIRECTORY_SEPARATOR . "imagenes";
    $container["upload_directory_logo"] = __DIR__ . DIRECTORY_SEPARATOR . "logos";
    $container["upload_directory_atractivo"] = __DIR__ . DIRECTORY_SEPARATOR . "atractivos";

    //Zonas
    $container["upload_directory_mapa"] = __DIR__ . DIRECTORY_SEPARATOR . "mapas";
    //Fotos de las Zonas (Menu)
    $container["upload_directory_zonas"] = __DIR__ . DIRECTORY_SEPARATOR . "recursos" . DIRECTORY_SEPARATOR . "zonas";


    //$container["api_host"] = "http://hansjal.esy.es/api-turismo/public";
    $container["api_host"] = "http:" . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . "hansjal.esy.es" . DIRECTORY_SEPARATOR . "api-turismo" . DIRECTORY_SEPARATOR . "public";


    $container["max_file_size"] = 4194304; //4 MB
    $container["allow_file_format"] = ["image/jpg", "image/png", "image/jpeg", "image/gif", "image/bmp", "image/svg", "image/ico"]; //Imagenes

    /**
    * Moves the uploaded file to the upload directory and assigns it a unique name
    * to avoid overwriting an existing uploaded file.
    *
    * @param string $directory directory to which the file is moved
    * @param UploadedFile $uploaded file uploaded file to move
    * @return string filename of moved file
    */
    //function moveUploadedFile($directory, UploadedFile $uploadedFile) {
    function moveUploadedFile($directory, Slim\Http\UploadedFile $uploadedFile, $idGoG, $id) {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        //$basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php (No funciona solo en PHP 7)
        //$filename = sprintf('%s.%0.8s', $basename, $extension);
        $filename = $idGoG . "_" . $id . "_" . date("YmdHis") . "." . $extension;
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        return $filename;
    }

    //Cors
    $app->options('/{routes:.+}', function ($request, $response, $args) {
        return $response;
    });

    /*
    header ("Access-Control-Allow-Origin: *");
    header ("Access-Control-Expose-Headers: Content-Length, X-JSON");
    header ("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
    header ("Access-Control-Allow-Headers: *");
    */
    $app->add(function($req, $res, $next) {
        $response = $next($req, $res);
        return $response
            /*
                ->withHeader("Access-Control-Allow-Credentials", "true");
            */
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
    });

    //.htaccess
    //RewriteRule .* - [env=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    //SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
    //Auth
    /*
    $app->add(function($req, $res, $next) {
        $path = $req->getUri()->getPath();
        if($path === "/user/login") {
            $res = $next($req, $res);
            return $res;
        } else {
            //See $request->getServerParam('HTTP_NOT_EXIST', 'default_value_here');
            if($req->hasHeader("HTTP_AUTHORIZATION")) {
                $token = null;
                try {
                    $authorization = $req->getHeader("HTTP_AUTHORIZATION")[0];
                    //$authorization = "JpYXQiOjE1MzQ2NTg0NzMsImV4cCI6MTUzNDY4NzI3MywianRpIjoiPz8_Iiwic3ViIjoiPz8_Iiwic2NvcGUiOnsiaWQiOiIxIiwicGVybWlzb3MiOnsiR0VUIjoiMSIsIlBPU1QiOiIxIiwiUFVUIjoiMSIsIlBBVENIIjoiMSIsIkRFTEVURSI6IjEifSwiaWR0aXBvIjoiMiJ9fQ.z3Y9OImDjgdiiBD9dfs_QHC_RTy0rJBCYaxNlEEi3SE";
                    //$token = JWT::decode($authorization, getenv("JWT_SECRET_KEY"), array("HS256"));
                } catch(\Exception $e) {
                    return $res
                        ->withStatus(401)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode(array("Error" => "Unauthorized (" . $e->getMessage() . ") Auth: " . $authorization), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                }
                $res = $next($req, $res);
                return $res;
            } else {
                return $res
                    ->withStatus(401)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode(array("Error" => "Unauthorized"), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            }
        }
    });
    */

    $app->get('/', function (Request $request, Response $response, array $args) {
        $response->getBody()->write("Welcome");
        return $response;
    });

    $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
        $name = $args['name'];
        $response->getBody()->write("Hello, $name");

        return $response;
    });

    //Usuarios
    require "../src/routes/users.php";
    //Departamentos
    require "../src/routes/departamentos.php";
    //Ciudades
    require "../src/routes/ciudades.php";
    //Tipos de Alojamiento
    require "../src/routes/tipos.php";
    //Guias
    require "../src/routes/guias.php";
    //Redes
    require "../src/routes/redes.php";
    //Tipo de Valorizaciones y Sub Tipos
    require "../src/routes/valorizaciones.php";
    //Servicios
    require "../src/routes/servicios.php";
    //Imagenes
    require "../src/routes/imagenes.php";
    //Tarifas
    require "../src/routes/tarifas.php";

    //Consultas
    require "../src/routes/consultas.php";

    //Página Web

    //Zonas
    require "../src/routes/zonas.php";
    //Atractivos
    require "../src/routes/atractivos.php";

    $app->run();
?>