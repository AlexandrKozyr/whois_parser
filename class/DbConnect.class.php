<?php
require_once '/config.php';
/*
 * @ autor Alexandr Kozyr ;
 * @ email: kozyr1av@gmail.com;
 * @ DbConnect creates a connection to data base using pattern Singleton;
 */

class DbConnect {
    
    private static $MySql = null;

    private function __construct() {
        
    }
    private function __wakeup() {
        
    }
    private function __clone() {
        
    }

    /**
     * 
     * @return object PDO connection
     */
    public static function MySqlConnecton($config) {

        if (is_null(self::$MySql)) {
            //переменная $connectionStr содержит настройки для подключения к базе данных - 
            //рецепиенту TecDoc - tecdoc(MySQL)
            $connectionStr = 'mysql:host='.$config['host'].';dbname='.$config['dbname'];
            $user          = $config['user'];
            $password      = $config['password'];
            $options       = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',);

            self::$MySql = new PDO($connectionStr, $user, $password, $options);
        }
        return self::$MySql;
    }

}
