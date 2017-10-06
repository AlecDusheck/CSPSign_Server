<?php

namespace sign\controllers;

use PDO as PDO;

class Home extends Controller
{
    public function index($request, $response)
    {
        @$this->data->page->name = "Daily Animation";
        @$this->data->recaptcha->publicKey = $this->container->get('settings')['general']['recaptchaPublicKey'];

        if (isset($_SESSION['msg'])) {
            @$this->data->msgs = $_SESSION['msg'];
            unset($_SESSION['msg']);
        }
        if (isset($_SESSION['error'])) {
            @$this->data->errors = $_SESSION['error'];
            unset($_SESSION['error']);
        }

        $checkSignOnline = $this->db->prepare('SELECT timeExpire FROM dbo.checkins');
        $checkSignOnline->execute();

        if ($checkSignOnline->rowCount() != 0) {
            $row = $checkSignOnline->fetch(PDO::FETCH_ASSOC);
            $expireTime = strtotime($row['timeExpire']);
            if (strtotime(date("m/d/Y h:i A")) > $expireTime) {
                @$this->data->page->signOffline = 'true';
            } else {
                @$this->data->page->signOffline = 'false';
            }
        } else { //If the database is empty, its probably in the middle of refreshing it, so we just say its fine
            @$this->data->page->signOffline = 'false';
        }

        @$this->data->page->date = date("m/d/Y");
        @$this->data->page->tomDate = date("m/d/Y", strtotime('tomorrow'));

        $getAnimation = $this->db->prepare('SELECT animationName FROM dbo.animationSchedule WHERE playDate = :tdate');
        $getAnimation->execute(array(':tdate' => date("m/d/Y")));

        if ($getAnimation->rowCount() != 0) {
            @$this->data->page->emptySchedule = 'false';
            $row = $getAnimation->fetch(PDO::FETCH_ASSOC);
            @$this->data->page->currentAnim = $row['animationName'];
        } else {
            @$this->data->page->emptySchedule = 'true';
        }
        $this->container->view->getEnvironment()->addGlobal('data', $this->data);
        return $this->view->render($response, "home.twig");
    }

    public function scheduleAnimation($request, $response)
    {
        $recaptcha = $_POST['g-recaptcha-response'];
        $animation = $_POST['animation'];

        $object = new \sign\classes\recaptcha($this->container->get('settings')['general']['recaptchaPrivateKey']);
        $cResponse = $object->verifyResponse($recaptcha);

        if (isset($cResponse['success']) and $cResponse['success'] != true) {
            $error[] = "An error occurred while verifying you are human.";
        } else {
            if ($animation != "Rainbow" && $animation != "Hyperloop" && $animation != "Fire" && $animation != "Static") {
                $error[] = "Unknown Animation.";
            }
            $checkSignOnline = $this->db->prepare('SELECT timeExpire FROM dbo.checkins');
            $checkSignOnline->execute();

            if ($checkSignOnline->rowCount() != 0) {
                $row = $checkSignOnline->fetch(PDO::FETCH_ASSOC);
                $expireTime = strtotime($row['timeExpire']);
                if (strtotime(date("m/d/Y h:i A")) > $expireTime) {
                    $error[] = "You cannot schedule an animation while the sign is offline.";
                }
            } else {//If the database is empty, its probably in the middle of refreshing it, so we just say its fine
                $getAnimation = $this->db->prepare('SELECT animationName FROM dbo.animationSchedule WHERE playDate = :tdate');
                $getAnimation->execute(array(':tdate' => date("m/d/Y")));
                if ($getAnimation->rowCount() != 0) {
                    $error[] = "Someone scheduled an animation before you submitted your request.";
                }
            }
        }

        if (!isset($error)) {
            //Make sure the database isn't overfill
            $getSchedule = $this->db->prepare('SELECT * FROM dbo.animationSchedule');
            $getSchedule->execute();
            $num = count($getSchedule->fetchAll());
            if ($num > 40) {
                $checkSchedule = $this->db->prepare('SELECT * FROM dbo.animationSchedule');
                $checkSchedule->execute();
                $result = $checkSchedule->fetchAll();
                $entries = [];
                foreach ($result as $entry) {
                    $entries[] = $entry['id'];
                }
                sort($entries);
                $idToRemove = array_shift(array_values($entries));
                @$this->data->page->tomDate = $idToRemove;

                $purgeRecord = $this->db->prepare('DELETE FROM dbo.animationSchedule WHERE id = :id');
                $purgeRecord->execute(array(':id' => $idToRemove));
            }

            $setAnimation = $this->db->prepare('INSERT INTO dbo.animationSchedule (playDate, animationName) VALUES (:playDate, :animationName)');
            $setAnimation->execute(array(':playDate' => date("m/d/Y"), ':animationName' => $animation));
            $msg[] = "Animation scheduled. It can take up to 10 minutes to take effect.";
            $_SESSION['msg'] = $msg;
            return $response->withRedirect('/');
        }
        $_SESSION['error'] = $error;
        return $response->withRedirect('/');


    }
}