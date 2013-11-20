<?php
namespace Teeparty;

use Teeparty\Task\Context;
use Teeparty\Task\Worker;
use Teeparty\Task\Exception;

class Task implements \Serializable, \JsonSerializable {

    private $id;
    private $worker;
    private $context;
    
    
    public function __construct(
        Worker $worker, 
        Context $context = null, 
        $id = null)
    {
        $this->id = $id ? $id : uniqid();
        $this->worker = $worker;
        $this->context = $context ? $context : new Context(array());
    }


    public function getId()
    {
        return $this->id;
    }

    public function serialize()
    {
        return serialize(array(
            'id' => $this->id,
            'worker' => get_class($this->worker),
            'context' => $context
        ));
    }

    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->id = $data['id'];
        $this->worker = new $data['worker'];
        $this->context = $data['context'];
    }


    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'worker' => get_class($this->worker),
            'context' => $this->context
        );
    }
}
