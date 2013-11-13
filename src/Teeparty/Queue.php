<?php

namespace Teeparty;

interface Queue {
    public function pop(array $channels, $timeout = 0);
    public function push(Message $message, $channel);
}

