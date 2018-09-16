<?php
    class Auth {
        /**
            * Middleware invokable class
            *
            * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
            * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
            * @param  callable                                 $next     Next middleware
            *
            * @return \Psr\Http\Message\ResponseInterface
        */
        public function __invoke($request, $response, $next)
        {
            //$JWT_Token = $request->getHeaders();
            $JWT_Token = $request->getHeader("Authorization");
            $response->getBody()->write("Token: " . var_dump($JWT_Token) . "<br/>");
            $response = $next($request, $response);
            return $response;

            /*
            $authorized = false;

            if($authorized) {
                // authorized, call next middleware
                return $next($request, $response);
            }

            // not authorized, don't call next middleware and return our own response
            $body = new Body(fopen('php://temp', 'r+'));
            $body->write('Forbidden');

            return $response
                ->withBody($body)
                ->withStatus(403);
            */
        }
    }
?>