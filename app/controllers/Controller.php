<?php

namespace sign\controllers;

use PDO as PDO;

class Controller
{
    protected $container;
    protected $db;
    protected $user;
    protected $data;

    public function __construct($container)
    {
        $this->container = $container;

        //Setup database
        $dbsettings = $container->get('settings')['db'];
        $db = new PDO("sqlsrv:Server=" . $dbsettings['host'] . ";Database=" . $dbsettings['name'], $dbsettings['user'], $dbsettings['pass']);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->db = $db;

        //Get variables required to load every page
        $this->data = new \stdClass;

    }

    public function __get($prop)
    {
        if ($this->container->{$prop}) {
            return $this->container->{$prop};
        }
    }

}
