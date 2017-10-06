<?php

// Twig view dependency
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig("../app/views", [
        "cache" => false,
    ]);

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));
    $view->addExtension(new Twig_Extension_Debug());
    $twig = $view->getEnvironment();
    return $view;
};

//Normal User Routes (logged in and logged out)
$container['home'] = function ($container) {
    return new \sign\controllers\Home($container);
};
$container['history'] = function ($container) {
    return new \sign\controllers\History($container);
};
$container['credit'] = function ($container) {
    return new \sign\controllers\Credit($container);
};
$container['api'] = function ($container) {
    return new \sign\controllers\Api($container);
};

$container['notFoundHandler'] = function ($c) {
    return new \sign\handlers\NotFound($c->get('view'), function ($request, $response) use ($c) {
        return $c['response']
            ->withStatus(404);
    });
};
