<?php

namespace sign\controllers;

use Slim\Http\Response;
use Slim\Views\Twig as View, PDO as PDO, Twig_SimpleFilter as custFunc;

class Api extends Controller
{
    public function getTime($request, $response)
    {
        $arr = array('currentTime' => date("j M Y G:i:s"));
        header("Content-Type: application/json");
        echo \GuzzleHttp\json_encode($arr);
        exit;
    }
    public function getLatestVersion($request, $response)
    {
        $arr = array('currentVersion' => $this->container->get('settings')['general']['latestClientVersion']);
        header("Content-Type: application/json");
        echo \GuzzleHttp\json_encode($arr);
        exit;
    }
    public function download($request, $response){
        return $this->view->render($response, "load.py");
    }
    public function checkIn($request, $response){
        $key = $_GET['key'];
        $checkKey = $this->db->prepare('SELECT * FROM dbo.apiKeys WHERE keyId = :id');
        $checkKey->execute(array(':id' => $key));

        if ($checkKey->rowCount() != 0) {
            $checkInRemove = $this->db->prepare('DELETE FROM dbo.checkins');
            $checkInRemove->execute();

            $date = date("m/d/Y h:i A", strtotime("+11 minute"));

            $checkIn = $this->db->prepare('INSERT INTO dbo.checkins (timeExpire, ip) VALUES (:timeExpire, :ip)');
            $checkIn->execute(array(':timeExpire' => $date, ':ip' => $this->get_ip_address()));
            $arr = array('status' => 'OK');
            header("Content-Type: application/json");
            echo \GuzzleHttp\json_encode($arr);
            exit;
        } else {
            $arr = array('status' => 'BADKEY');
            header("Content-Type: application/json");
            echo \GuzzleHttp\json_encode($arr);
            exit;
        }
    }
    public function clearDb($request, $response){
        $key = $_GET['key'];
        $checkKey = $this->db->prepare("SELECT * FROM dbo.apiKeys WHERE keyId = :id AND rank = 'SUPERUSER'");
        $checkKey->execute(array(':id' => $key));

        if ($checkKey->rowCount() != 0) {
            $checkIn = $this->db->prepare('DELETE FROM dbo.animationSchedule');
            $checkIn->execute();
            $arr = array('status' => 'OK');
            header("Content-Type: application/json");
            echo \GuzzleHttp\json_encode($arr);
            exit;
        } else {
            $arr = array('status' => 'BADKEY');
            header("Content-Type: application/json");
            echo \GuzzleHttp\json_encode($arr);
            exit;
        }
    }
    public function getCurrentAnimation($request, $response){
        $getAnimation = $this->db->prepare('SELECT animationName FROM dbo.animationSchedule WHERE playDate = :tdate');
        $getAnimation->execute(array(':tdate' => date("m/d/Y")));

        if ($getAnimation->rowCount() != 0) {
            $row = $getAnimation->fetch(PDO::FETCH_ASSOC);
            $animation = $row['animationName'];
        } else {
            $animation = 'none';
        }

        $arr = array('currentAnimation' => $animation);
        header("Content-Type: application/json");
        echo \GuzzleHttp\json_encode($arr);
        exit;
    }

    private function get_ip_address()
    {
        // check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        // check for IPs passing through proxies
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // check if multiple ips exist in var
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($iplist as $ip) {
                    if ($this->validate_ip($ip))
                        return $ip;
                }
            } else {
                if ($this->validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_X_FORWARDED']))
            return $_SERVER['HTTP_X_FORWARDED'];
        if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && $this->validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
            return $_SERVER['HTTP_FORWARDED_FOR'];
        if (!empty($_SERVER['HTTP_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_FORWARDED']))
            return $_SERVER['HTTP_FORWARDED'];

        // return unreliable ip since all else failed
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Ensures an ip address is both a valid IP and does not fall within
     * a private network range.
     */
    private function validate_ip($ip)
    {
        if (strtolower($ip) === 'unknown')
            return false;

        // generate ipv4 network address
        $ip = ip2long($ip);

        // if the ip is set and not equivalent to 255.255.255.255
        if ($ip !== false && $ip !== -1) {
            // make sure to get unsigned long representation of ip
            // due to discrepancies between 32 and 64 bit OSes and
            // signed numbers (ints default to signed in PHP)
            $ip = sprintf('%u', $ip);
            // do private network range checking
            if ($ip >= 0 && $ip <= 50331647) return false;
            if ($ip >= 167772160 && $ip <= 184549375) return false;
            if ($ip >= 2130706432 && $ip <= 2147483647) return false;
            if ($ip >= 2851995648 && $ip <= 2852061183) return false;
            if ($ip >= 2886729728 && $ip <= 2887778303) return false;
            if ($ip >= 3221225984 && $ip <= 3221226239) return false;
            if ($ip >= 3232235520 && $ip <= 3232301055) return false;
            if ($ip >= 4294967040) return false;
        }
        return true;
    }
}