<?php

/*
 * @ author: Alexandr Kozyr;
 * @ email: kozyr1av@gmail.com;
 * this file aggregates all moves for parsing and saving data to db
 */



if (isset($argv[0])) {
    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', 'On');
    ini_set('max_execution_time', '3600');
    require_once 'class/DbConnect.class.php';
    require_once 'class/Parser.class.php';
    
    $test = new Parser(DbConnect::MySqlConnecton($config));
    $test->process();
} else {
    die('Script should start with shell');
}







