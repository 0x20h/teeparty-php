<?php

namespace Teeparty;

interface Queue {
    public function pop(array $channels = array(), $timeout);
    public function push(Message $message, $channel);
}

