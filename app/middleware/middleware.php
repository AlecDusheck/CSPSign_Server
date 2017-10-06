<?php

namespace sign\middleware;

class Middleware{
    protected $container;
    public function __construct($container) {
        $this->container = $container;
    }
}