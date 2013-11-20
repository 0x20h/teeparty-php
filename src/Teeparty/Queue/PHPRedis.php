<?php

namespace Teeparty\Queue;

use Teeparty\Queue;
use Teeparty\Task;

class PHPRedis implements Queue {
    private $client;

    public function __construct(\Redis $client)
    {
        $this->client = $client;

        if (!$this->client->isConnected()) {
            throw new \RuntimeException('\Redis client is not connected!');
        }
    }

    public function pop(array $channels, $timeout = 0)
    {
        $item = $this->client->brpop($channels, $timeout);
        return $item;
    }

    public function push(Task $task, $channel)
    {
    }


    public function ack(Task $task, $result = Task::STATUS_OK)
    {
        $this->client->hset($task->getId(), Task::STATUS, $result);
    }


    public function setPrefix($prefix)
    {
        $this->client->setOption(\Redis::OPT_PREFIX, $prefix);
    }
}
