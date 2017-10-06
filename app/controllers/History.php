<?php

namespace sign\controllers;

class History extends Controller
{

    public function index($request, $response)
    {
        @$this->data->page->name = "Animation History";

        $getAnimations = $this->db->prepare('SELECT playDate, animationName FROM dbo.animationSchedule');
        $getAnimations->execute();
        $result = $getAnimations->fetchAll();
        $animations = [];
        foreach ($result as $anim) {
            $info = [
                "playDate" => $anim['playDate'],
                "animationName" => $anim['animationName']
            ];

            array_push($animations, $info);

            usort($animations, function($a1, $a2) {
                $v1 = strtotime($a1['playDate']);
                $v2 = strtotime($a2['playDate']);
                return $v2 - $v1;
            });
        }
        @$this->data->animations->list = $animations;

        $this->container->view->getEnvironment()->addGlobal('data', $this->data);
        return $this->view->render($response, "history.twig");
    }
}