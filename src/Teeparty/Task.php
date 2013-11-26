<?php
namespace Teeparty;

use Teeparty\Task\Context;
use Teeparty\Task\Worker;
use Teeparty\Task\Result;

class Task implements \Serializable, \JsonSerializable {

    private $id;
    private $worker;
    private $context;
    private $tries = 0;
    private $maxTries = 10;
    
    
    public function __construct(
        Worker $worker, 
        Context $context = null, 
        $id = null)
    {
        $this->id = $id ? $id : uniqid();
        $this->worker = $worker;
        $this->context = $context ? $context : new Context(array());
    }

    /**
     * Set the number of times this task was executed.
     *
     * @param int $tries The number of times this task was executed.
     */
    public function setTries($tries)
    {
        $this->tries = (int) $tries;
    }

    /**
     * Get the number of times this task was executed.
     *
     * @return int number of times this task was executed.
     */
    public function getTries()
    {
        return $this->tries;
    }

    
    /**
     * Set the maximum number of times this task will be executed.
     *
     * @param int $tries The maximum number of times this task will be executed.
     */
    public function setMaxTries($maxTries)
    {
        $this->maxTries = (int) $maxTries;
    }

    /**
     * Get the maximum number of times this task will be executed.
     *
     * @return int maximum number of times this task will be executed.
     */
    public function getMaxTries()
    {
        return $this->maxTries;
    }


    /**
     * Retrieve the task id.
     *
     * @return string Task ID.
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Execute the attached worker with the given context and report results.
     *
     * @return Teeparty\Task\Result Task Result.
     */
    public function execute()
    {
        try {
            $start = microtime(true);
            $data = $this->worker->run($this->context);
            $status = $data !== false ? Result::STATUS_OK : Result::STATUS_FAILED;
        } catch (\Exception $e) {
            $status = Result::STATUS_EXCEPTION;
            $data = $e;
        }

        $result = new Result($this, $status, $data);
        $result->setExecutionTime(microtime(true) - $start);
        $this->setTries($this->getTries() + 1);
        return $result;
    }


    public function serialize()
    {
        return serialize(array(
            'id' => $this->id,
            'worker' => get_class($this->worker),
            'context' => $this->context,
            'tries' => $this->tries,
            'max_tries' => $this->maxTries,
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
            'context' => $this->context,
            'tries' => $this->tries,
            'max_tries' => $this->maxTries,
        );
    }
}
