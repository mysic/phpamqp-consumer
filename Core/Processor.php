<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2018\12\18 0018
 * Time: 14:18
 */

namespace Core;

use PhpAmqpLib\Message\AMQPMessage;

class Processor
{
    protected $storage = null;
    protected $db = null;
    public $config = [];

    protected function __construct(Storage $storage, array $config, Mysql $db = null)
    {
        $this->config = $config;
        $this->storage = $storage;
        $this->db = $db;
    }
    public function callback(bool $noAck): callable
    {
        return function(AMQPMessage $message) use ($noAck) {
            $isSuccess = static::handle($message);
            if(!$noAck) {
                if ($isSuccess) {
                    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                } else {
                    //失败消息的记录
                    $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag'], false, true);
                }
            }
        };
    }
    protected function run(string $operation, array $doc)
    {
        if(!$doc) {
            //todo error log
            return false;
        }
        try{
            $result = $this->storage->{$operation}($doc);
            if($result['_shards']['successful'] == 0) {
                //todo error log;
                return false;
            }
            return true;
        }catch(\Exception $e){
            echo $e->getMessage();
            //todo error log
        }

    }
}