<?php
namespace Teeparty;

use Teeparty\Task\Context;
use Teeparty\Task\Worker;

class Task implements \Serializable, \JsonSerializable {

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


    public function jsonSerialize()
    {
        return array(
            'worker' => get_class($this->worker),
            'context' => $this->context
        );
    }


    /**
     * Create a new task.
     * 
     * @param string $worker worker class.
     * @param array $context context to run worker in.
     *
     * @return Task A new Task.
     */
    public static function create($worker, array $context = array())
    {
        if (!class_exists($worker)) {
            throw new Exception('unknown class: ' . $worker);
        }

        $w = new $worker;

        if (!$w instanceof Worker) {
            throw new Exception($worker.' must implement \Teeparty\Task\Worker');
        }
        
        $c = new Context($context);
        return new Task($w, $c);
    }
}
