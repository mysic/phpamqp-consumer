<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2018\12\17 0017
 * Time: 14:41
 */
namespace Core;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class Task 任务调度器
 * @package Task\BA
 */
class Dispatcher
{
    protected $processor = null;
    protected $connector = null;

    public function __construct(Processor $processor, AMQPStreamConnection $connector)
    {
        $this->processor = $processor;
        $this->connector = $connector;
    }

    public function run()
    {
        $channelId = null;
        if(\key_exists('channel', $this->processor->config)) {
            if(\key_exists('channel_id', $this->processor->config['channel']) && !empty($this->processor->config['channel']['channel_id'])) {
                $channelId = $this->processor->config['channel']['channel_id'];
            }
        }
        $channel = $this->channelDeclare($channelId);
        if(\key_exists('channel', $this->processor->config) && \key_exists('basic_qos', $this->processor->config['channel'])) {
            $channel->basic_qos(
                $this->processor->config['channel']['basic_qos']['prefetch_size'],
                $this->processor->config['channel']['basic_qos']['prefetch_count'],
                $this->processor->config['channel']['basic_qos']['global']
            );
        }
        if (!$channel || !$channel instanceof AMQPChannel) {
            throw new \Exception('channel declare fail');
        }
        if (!empty($this->processor->config['exchange_declare']['exchange'])) {
            if (empty($this->processor->config['exchange_declare']['type'])) {
                $this->processor->config['exchange_declare']['type'] = 'direct';
            }
            $this->exchangeDeclare($channel, $this->processor->config['exchange_declare']);
        }

        $this->queueDeclare($channel, $this->processor->config['queue_declare']);
        if(!empty($this->processor->config['queue_binding']))
        {
            $this->queueBinding($channel, $this->processor->config['queue_binding']);
        }

        $this->basicConsume($channel, $this->processor->config['basic_consume'], $this->processor->callback($this->processor->config['basic_consume']['no_ack']));

        register_shutdown_function(function($channel, $connection){
            $channel->close();
            $connection->close();
        }, $channel, $this->connector);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }

    protected function basicConsume(AMQPChannel $channel, array $config, callable $callback): string
    {
        if (empty($config)) {
            return $channel->basic_consume();
        }
        return $channel->basic_consume(
            $config['queue'],
            $config['consumer_tag'],
            $config['no_local'],
            $config['no_ack'],
            $config['exclusive'],
            $config['nowait'],
            $callback,
            $config['ticket'],
            $config['arguments']
        );
    }

    protected function channelDeclare($channelId): AMQPChannel
    {
        return $this->connector->channel($channelId);
    }

    protected function exchangeDeclare(AMQPChannel $channel, array $config)
    {
        return $channel->exchange_declare(
            $config['exchange'],
            $config['type'],
            $config['passive'],
            $config['durable'],
            $config['auto_delete'],
            $config['internal'],
            $config['nowait'],
            $config['arguments'],
            $config['ticket']
        );
    }

    protected function queueBinding(AMQPChannel $channel, array $config)
    {
        return $channel->queue_bind(
            $config['queue'],
            $config['exchange'],
            $config['routing_key'],
            $config['nowait'],
            $config['arguments'],
            $config['ticket']
        );
    }

    protected function queueDeclare(AMQPChannel $channel, array $config)
    {
        if (empty($config)) {
            return $channel->queue_declare();
        }
        return $channel->queue_declare(
            $config['queue'],
            $config['passive'],
            $config['durable'],
            $config['exclusive'],
            $config['auto_delete'],
            $config['nowait'],
            $config['arguments'],
            $config['ticket']
        );
    }
}

