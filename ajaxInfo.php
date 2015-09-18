<?php

/*
 * @ author: Alexandr Kozyr;
 * @ email: kozyr1av@gmail.com;
 * this file aggregates all moves for parsing and saving data to db
 */
header('Content-Type:aplication/json');
require_once 'class/DbConnect.class.php';
require_once 'class/Information.class.php';

$test = new Information(DbConnect::MySqlConnecton($config));
echo $test->GetInformation();






