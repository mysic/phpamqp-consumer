<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\12\17 0017
 * Time: 15:38
 */
spl_autoload_register(function($class){
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $fullFileName = $class . '.php';
    if (!\is_file($fullFileName)) {
        return false;
    }
    include $fullFileName;
});