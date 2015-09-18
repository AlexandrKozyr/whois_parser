<?php

/*
 * @ author: Alexandr Kozyr;
 * @ email: kozyr1av@gmail.com;
 * class Information works with information after parsing
 */

class Information {

    private $mySqlConnection;
    

    public function __construct($connection) {
        $this->mySqlConnection = $connection;
    }

    public function GetInformation() {
        $stm   = $this->mySqlConnection->prepare(
                "
                SELECT 
                    h.`id` AS `hostid`,
                    h.`hostname` AS `host`,
                    wh.`status`,
                    wh.`mnt-by`,
                    wh.`updated`,
                    wh.`expires` 
                  FROM
                    `host` AS h 
                    INNER JOIN `whois_info` AS wh 
                      ON wh.`host_id` = h.`id`
                "
        );
        $stm->execute();
        $info = $stm->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($info);
    }

}
