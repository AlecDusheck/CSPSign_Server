<?php

namespace sign\controllers;

use Slim\Views\Twig as View, PDO as PDO, Twig_SimpleFilter as custFunc;

class Credit extends Controller
{

    public function index($request, $response)
    {
        @$this->data->page->name = "Credit";

        $this->container->view->getEnvironment()->addGlobal('data', $this->data);
        return $this->view->render($response, "credit.twig");
    }
}