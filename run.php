<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2018\12\17 0017
 * Time: 11:39
 */


/**
 * 启动方式： php run.php project_name processor_name [storage_name = elastic]
 *
 * project_name 项目名称，Task每个项目的根目录在Task目录下，根目录的首字母必须大写
 * processor_name 某个队列消息的处理器名称
 * storage_name 存储器操作类的名称，保存在Storage文件夹下。默认是elastic，可选参数。
 * project_name、processor_name、storage_name 长度不能多于50个字符
 */
\error_reporting(E_WARNING|E_ERROR);
define('ROOT', dirname(__FILE__));
chdir(ROOT);
require_once  'autoload.php';
require_once  'vendor/autoload.php';

if (!isset($argv[1])) {
    echo 'error: missing project name',PHP_EOL;
    exit(0);
}
if (!isset($argv[2])) {
    echo 'error: missing processor name',PHP_EOL;
    exit(0);
}
if (!isset($argv[3])) {
    $argv[3] = 'elastic';
}
$regex = '/^[a-zA-Z]+$/i';
if(!\preg_match($regex, $argv[1])){
    echo 'error: project name is only allowed english letters',PHP_EOL;
    exit(0);
}
if(!\preg_match($regex, $argv[2])){
    echo 'error: processor name is only allowed english letters',PHP_EOL;
    exit(0);
}
if(!\preg_match($regex, $argv[3])){
    echo 'error: storage name is only allowed english letters',PHP_EOL;
    exit(0);
}
if(!\strlen($argv[1]) > 50) {
    echo 'error: project name length must be less than 50 letters',PHP_EOL;
    exit(0);
}
if(!\strlen($argv[2]) > 50) {
    echo 'error: processor name length must be less than 50 letters',PHP_EOL;
    exit(0);
}
if(!\strlen($argv[3]) > 50) {
    echo 'error: storage name length must be less than 50 letters',PHP_EOL;
    exit(0);
}
$projectName = \lcfirst($argv[1]);
$processorName = \ucfirst($argv[2]);
$storageName = \ucfirst($argv[3]);
//mq connector init
$mqConfigFilePath = 'task' . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'messageQueue.php';
if (!\file_exists($mqConfigFilePath)) {
    echo 'error: cannot find message-queue config file ', PHP_EOL . 'file path: ' . $mqConfigFilePath . PHP_EOL .  'base path: ' . getcwd() .  PHP_EOL;
    exit(0);
}
$mqConfig =  require_once $mqConfigFilePath;
$mqConnector = Core\MqConnector::connect($mqConfig);
//storage init
$storageConfigFilePath = 'task' . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR .  'storage.php';
if (!\file_exists($storageConfigFilePath)) {
    echo 'error: cannot find storage config file ', PHP_EOL . 'file path: ' . $storageConfigFilePath;
    exit(0);
}
$storageConfig = require_once $storageConfigFilePath;
$storageFullyQualifiedName = 'task\\' .$projectName. '\\storage\\'. $storageName;
if (!\class_exists($storageFullyQualifiedName)) {
    echo 'storage model not exist';
    exit(0);
}
$storage = new $storageFullyQualifiedName($storageConfig);
if (!$storage instanceof \Core\Storage) {
    echo 'storage model not instance of Storage Class';
    exit(0);
}
//db init
$db = null;
$dbConfigFilePath = 'task' . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR .  'mysql.php';
if(\file_exists($dbConfigFilePath)) {
    $dbConfig = require_once $dbConfigFilePath;
    if(!empty($dbConfig)) {
        $db  = \core\Db::instance($dbConfig);
    }
}
//processor init
$processorConfigFilePath = 'task' . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $argv[2] . '.php';
if (!\file_exists($processorConfigFilePath)) {
    echo 'error: cannot find processor config file ', PHP_EOL . 'file path: ' . $processorConfigFilePath;
    exit(0);
}
$processorConfig = require_once $processorConfigFilePath;
$processorFullyQualifiedName = 'task\\' .$projectName. '\\processor\\'. $processorName;
if (!\class_exists($processorFullyQualifiedName)) {
    echo 'business processor not exist';
    exit(0);
}
$processor = new $processorFullyQualifiedName($storage, $processorConfig, $db);
if (!$processor instanceof \core\Processor) {
    echo 'processor not instance of Processor Class';
    exit(0);
}

unset($argv, $regex);
unset($projectName, $processorName, $storageName);
unset($mqConfigFilePath, $storageConfigFilePath, $dbConfigFilePath, $processorConfigFilePath);
unset($mqConfig, $storageConfig, $dbConfig, $processorConfig);
unset($storageFullyQualifiedName, $processorFullyQualifiedName);


try{
    $dispatcher = new \core\Dispatcher($processor, $mqConnector);
    $dispatcher->run();
} catch(\Exception $e) {
    echo 'error: ' . $e->getMessage(),PHP_EOL;
}
