<?php

/*
 * @ author: Alexandr Kozyr ;
 * @ email: kozyr1av@gmail.com;
 * @ this class pasing our test task site and saves it to data base;
 */

class Parser {

    private $mySqlConnection;
    private $pageAmount = null;
    private $hostNames  = array();
    private $error      = 0;
    private $info       = 0;
    private $notRegist  = 0;

    public function __construct($connection) {
        $this->mySqlConnection = $connection;
    }

    public function process() {
        //getting amount of pages with needed data
        $this->HowManyPages();
        //parsing whole data and filling our hostname attribute
        $this->MakeParsingAllPages();
        //saving all hosts to data base
        $this->SaveHostNames();
        //getting all hosts without whois information
        $hosts = $this->GetHostsForWhois();
        $r     = $this->LookWhoIsAll($hosts);
        echo "Got information about $this->info domains. //
              Domains without information (non registred): $this->notRegist. //
              Error requests to whois server - $this->error. //
              ";
        foreach ($r as $key => $value) {
            $this->SaveWhoIs($value, $key);
        }
    }

    public function MakeParsingAllPages() {
        for ($i = 1; $i <= $this->pageAmount; $i++) {
            $resultOnePage    = $this->MakeParsingOnePage($i);
            $hostNamesPerPage = $this->GetHostNames($resultOnePage);
            foreach ($hostNamesPerPage as $item) {
                $this->hostNames[] = $item;
            }
        }
    }

    public function GetHostNames($page) {
        $patternAmount = '/<\/td><td>([\.\-_A-Za-z0-9]+?.com.ua)<\/td><td>/';
        preg_match_all($patternAmount, $page, $hosts);
        $result        = array();
        foreach ($hosts[0] as $item) {
            $result[] = trim(strip_tags($item));
        }
        return $result;
    }

    public function MakeParsingOnePage($page) {
        $donor  = curl_init('http://ukrhosting.com/backorder.php?page=' . $page . '&language=ru&zone=com.ua&action=used');
        // Параметры курла
        curl_setopt($donor, CURLOPT_USERAGENT, 'IE20');
        curl_setopt($donor, CURLOPT_HEADER, 0);
        // Следующая опция необходима для того, чтобы функция curl_exec() возвращала значение а не выводила содержимое переменной на экран
        curl_setopt($donor, CURLOPT_RETURNTRANSFER, '1');
        // Получаем html
        $result = iconv('cp1251', 'utf-8', (curl_exec($donor)));
        // Отключаемся
        curl_close($donor);
        return $result;
    }

    private function HowManyPages() {
        $firstPage     = $this->MakeParsingOnePage(1);
        $patternAmount = '/<\/select>(.*)<a/';
        preg_match($patternAmount, $firstPage, $amount);
        $str           = preg_replace("/[^0-9]/", '', $amount[0]);
        if (is_numeric($str)) {
            $this->pageAmount = (int) $str;
        } else {
            die('error');
        }
    }

    private function SaveHostNames() {
        $cnt = 0;
        foreach ($this->hostNames as $item) {

            $stm = $this->mySqlConnection->prepare(
                    "
                    INSERT INTO `host` (hostname)
                    VALUES (:url);
                "
            );
            $stm->bindValue(':url', $item, PDO::PARAM_STR);
            $stm->execute();
            if ($stm->execute()) {
                $cnt++;
            }
        }

        echo 'Saved ' . $cnt . ' hostNames to data base.//';
    }

    public function LookWhoIsAll(array $hosts) {
        $amount = count($hosts);

        $result = array();
        for ($i = 0; $i < $amount; $i++) {
            $result[$hosts[$i]["id"]] = $this->LookWhoIs($hosts[$i]["hostname"]);
        }
        return $result;
    }

    public function LookWhoIs($hostName, $server = 'whois.com.ua') {
        if (trim($hostName) <> "") {
            $hostName = trim($hostName);
            $fp       = fsockopen($server, 43, $errno, $errstr, 10);
            if (!$fp) {
                die('Error: Could not connect to ' . $server . '!');
            } else {
                $response = "";
                fputs($fp, "$hostName\r\n");
                while (!feof($fp)) {
                    $response .= fgets($fp);
                }
                fclose($fp);
            }
            return $this->CleanUpResult($response);
        } else {
            die('Error: Bad name ' . $hostName . '!');
        }
    }

    private function CleanUpResult($result) {
        $response = array();
        if (is_int(strpos($result, 'No entries found'))) {
            $response['status'] = 'Not registred';
            $this->notRegist++;
            $this->info++;
        } else {
            if (preg_match("/status:(.+)\s/", $result, $status) == 1) {
                preg_match("/status:(.+)\s/", $result, $status);
                $response['status']  = trim($status[1]);
                preg_match("/mnt-by:(.+)\s/", $result, $mnt_by);
                $response['mnt_by']  = trim($mnt_by[1]);
                preg_match("/created:(.+)\s/", $result, $updated);
                $response['updated'] = trim($updated[1]);
                preg_match("/expires:(.+)\s/", $result, $expires);
                $response['expires'] = trim($expires[1]);
                $this->info++;
            } else {
                $response['status'] = 'Response error';
                $this->error++;
            }
        }

        return $response;
    }

    private function SaveWhoIs($info, $id) {
        $id2 = $id;
        if ("Not registred" != $info["status"] && is_string($info["status"])) {
            $status  = $info["status"];
            $mnt_by  = $info["mnt_by"];
            $updated = $info["updated"];
            $expires = $info["expires"];

            echo "<pre>";
            var_dump($status, $mnt_by, $updated, $expires);
            echo "</pre>";

            $stm = $this->mySqlConnection->prepare(
                    "INSERT INTO whois_info 
                        (`host_id`, `status`, `mnt-by`, `updated`, `expires`)
                        VALUES
                        (?, ?, ?, ?, ?)
                    "
            );
            $stm->bindParam(1, $id, PDO::PARAM_INT);
            $stm->bindParam(2, $status, PDO::PARAM_STR);
            $stm->bindParam(3, $mnt_by, PDO::PARAM_STR);
            $stm->bindParam(4, $updated, PDO::PARAM_STR);
            $stm->bindParam(5, $expires, PDO::PARAM_STR);
            $res = $stm->execute();
            var_dump($res);
            if ($res) {
                $stm1 = $this->mySqlConnection->prepare(
                        "
                        UPDATE `host` SET whois_done=1 WHERE id=?
                    "
                );
                $stm1->bindParam(1, $id2, PDO::PARAM_INT);
                $stm1->execute();
            }
        } elseif ("Not registred" == $info["status"]) {
            $stm = $this->mySqlConnection->prepare(
                    'INSERT INTO whois_info 
                        (`host_id`, `status`, `mnt-by`, `updated`, `expires`)
                        VALUES
                        (:host_id, "Not registred", "Not registred", "Not registred", "Not registred")
                    '
            );
            $stm->bindParam(':host_id', $id);
            $res = $stm->execute();
            if ($res) {
                $stm1 = $this->mySqlConnection->prepare(
                        "
                        UPDATE `host` SET whois_done=1 WHERE id=:id
                    "
                );
                $stm1->bindParam(':id', $id2);
                $stm1->execute();
            }
        }
    }

    public function GetHostsForWhois() {
        $stm   = $this->mySqlConnection->prepare(
                "
                    SELECT  h.`id`, h.`hostname` FROM `host` AS h
                    WHERE h.`whois_done` = 0;
                "
        );
        $stm->execute();
        $hosts = $stm->fetchAll(PDO::FETCH_ASSOC);
        return $hosts;
    }

}
