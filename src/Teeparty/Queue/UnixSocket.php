<?php

namespace Teeparty\Queue;

use Teeparty\Queue;
use Teeparty\Message;

class UnixSocket implements Queue {

    private $pipes = array();

    public function pop(array $channels = array(), $timeout = 0)
    {
       // open stream sockets non-blocking and listen for items
    }

    public function push(Message $message, $channel)
    {

    }
}
