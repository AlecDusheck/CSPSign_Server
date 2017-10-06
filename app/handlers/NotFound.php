<?php

namespace sign\handlers;

use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class NotFound extends \Slim\Handlers\NotFound {
    private $view;

    public function __construct(Twig $view) {
        $this->view = $view;
    }
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) {
        parent::__invoke($request, $response);

        $this->view->render($response, '404.twig');

        return $response->withStatus(404);
    }

}