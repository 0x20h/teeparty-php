<?php
namespace Teeparty;

use Task\Context;
use Task\Job;

class Task implements \Serializable {

    private $worker;
    private $context;

    public function __construct(Worker $worker, Context $context)
    {
        $this->worker = $worker;
        $this->context = $context;
    }

    public function serialize()
    {
        return serialize(array(
            'worker' => get_class($this->worker),
            'context' => $context
        ));
    }

    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->worker = new $data['worker'];
        $this->context = $data['context'];
    }
}
