<?php

namespace Teeparty\Queue;

use Teeparty\Queue;
use Teeparty\Message;

class PHPRedis implements Queue {

    private $client;

    public function __construct(\Redis $client)
    {
        $this->client = $client;

        if (!$this->client->isConnected()) {
            throw new \LogicException('\Redis client is not connected!');
        }
    }

    public function pop(array $channels = array(), $timeout = 0)
    {
        $item = $this->client->brpop($channels, $timeout);

    }

    public function push(Message $message, $channel)
    {
    }
}
