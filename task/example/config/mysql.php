<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2019\1\7 0007
 * Time: 14:51
 */

/**
 * CatFan/Medoo框架 数据库连接参数结构
 */
return [
    // required
    'database_type' => 'mysql',
    'database_name' => '',
    'server' => '',
    'username' => '',
    'password' => '',

    // [optional]
    'charset' => 'utf8',
    'port' => 3306,

    // [optional] Table prefix
    'prefix' => 'dtq_',

    // [optional] Enable logging (Logging is disabled by default for better performance)
    'logging' => false,

    // [optional] MySQL socket (shouldn't be used with server and port)
//    'socket' => '/tmp/mysql.sock',
    // [optional] driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
    'option' => [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        // PDO::ATTR_PERSISTENT => true
    ],
    // [optional] Medoo will execute those commands after connected to the database for initialization
    'command' => [
        'SET SQL_MODE=ANSI_QUOTES'
    ]
];