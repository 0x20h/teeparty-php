<?php
namespace Teeparty;

use Teeparty\Task\Context;
use Teeparty\Task\Worker;
use Teeparty\Task\Exception;

class Task implements \Serializable, \JsonSerializable {

    private $worker;
    private $context;

    public function __construct(Worker $worker, Context $context = null)
    {
        $this->worker = $worker;
        $this->context = $context ? $context : new Context(array());
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


    public function jsonSerialize()
    {
        return array(
            'worker' => get_class($this->worker),
            'context' => $this->context
        );
    }
}
