<?php

namespace Teeparty\Queue;

use Teeparty\Queue;
use Teeparty\Message;

class Redis implements Queue {

    private $client;

    public function __construct(\Redis $client)
    {
        $this->client = $client;
    }

    public function pop(array $channels = array(), $timeout = 0)
    {
    }

    public function push(Message $message, $channel)
    {
    }
}
