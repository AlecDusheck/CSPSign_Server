<?php

namespace sign\middleware;

class gsMiddleware extends Middleware{
    function __invoke($request, $response, $next) {
        $this->container->view->getEnvironment()->addGlobal('session', $_SESSION);
        $this->container->view->getEnvironment()->addGlobal('get', $_GET);
        $this->container->view->getEnvironment()->addGlobal('post', $_POST);
        return $next($request, $response);
    }
}