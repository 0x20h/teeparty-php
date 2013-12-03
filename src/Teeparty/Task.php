<?php
namespace Teeparty;

use Teeparty\Task\Result;

class Task implements \Serializable, \JsonSerializable {

    private $id;
    private $job;
    private $context;
    
    public function __construct(Job $job, array $context = array(), $id = null)
    {
        $this->id = $id ? $id : uniqid();
        $this->job = $job;
        $this->context = $context;
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
     * Retrieve task context.
     *
     * @return array Parameters for the task.
     */
    public function getContext() {
        return $this->context;
    }
    
    
    public function getName()
    {
        return $this->job->getName() . '@' . $this->getId();
    }

    /**
     * Execute the attached job with the given context and report results.
     *
     * @return Teeparty\Task\Result Task Result.
     */
    public function execute()
    {
        try {
            $start = microtime(true);
            $data = $this->job->run($this->context);
            $status = $data !== false ? Result::STATUS_OK : Result::STATUS_FAILED;
        } catch (\Exception $e) {
            $status = Result::STATUS_EXCEPTION;
            $data = $e;
        }

        $result = new Result($this, $status, $data);
        $result->setExecutionTime(microtime(true) - $start);
        return $result;
    }


    public function serialize()
    {
        return serialize(array(
            'id' => $this->id,
            'job' => get_class($this->job),
            'context' => $this->context,
        ));
    }


    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->id = $data['id'];
        $this->job = new $data['job'];
        $this->context = $data['context'];
    }


    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'job' => get_class($this->job),
            'context' => $this->context,
        );
    }
}
