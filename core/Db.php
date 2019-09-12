<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2019\1\7 0007
 * Time: 14:54
 */

namespace core;

use Medoo\Medoo;

class Db
{
    protected static $instance = null;
    private function __construct(){}
    private function __clone(){}

    public static function instance(array $config)
    {
        if(!self::$instance instanceof Medoo || self::$instance == null) {
            self::$instance =  new Medoo($config);
        }
        return self::$instance;
    }

}