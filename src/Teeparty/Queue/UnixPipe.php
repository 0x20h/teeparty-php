<?php

namespace Teeparty\Queue;

use Teeparty\Queue;
use Teeparty\Message;

class UnixPipe implements Queue {

    private $pipes = array();

    public function __construct()
    {
    }

    public function pop(array $channels = array(), $timeout = 0)
    {
        
    }

    public function push(Message $message, Channel $channel)
    {

    }
}
