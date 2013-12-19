<?php
namespace Teeparty;

use Teeparty\Task\Result;
use Teeparty\JsonSerializable;
use Teeparty\Schema\Validator;

class Task implements \Serializable, JsonSerializable {

    private $id;
    private $job;
    private $context;
    private $created;
    
    public function __construct(Job $job, array $context = array(), $id = null)
    {
        $this->id = $id ? $id : uniqid(true);
        $this->job = $job;
        $this->context = $context;
        $this->created = new \DateTime;
    }


    public function setCreated(\DateTime $created)
    {
        $this->created = $created;
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
        $startDate = new \DateTime;

        try {
            $start = microtime(true);
            $data = $this->job->run($this->context);
            $status = $data !== false ? Result::STATUS_OK : Result::STATUS_FAILED;
        } catch (\Exception $e) {
            $status = Result::STATUS_EXCEPTION;
            $data = $e;
        }

        $result = new Result($this->getId(), $status, $data);
        $result->setExecutionTime(microtime(true) - $start);
        $result->setStartDate($startDate);
        return $result;
    }


    public function serialize()
    {
        return serialize(array(
            'id' => $this->id,
            'job' => get_class($this->job),
            'context' => $this->context,
            'created' => $this->created,
        ));
    }


    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->id = $data['id'];
        $this->job = new $data['job'];
        $this->context = $data['context'];
        $this->created = $data['created'];
    }


    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'job' => get_class($this->job),
            'context' => $this->context,
            'created' => $this->created->format(\DateTime::ISO8601)
        );
    }


    public static function fromJSON($json)
    {
        $validator = new Validator();
        $data = json_decode($json);

        if(!$validator->validate('task', $data)) {
            throw new Exception('Task validation failed: ' .
                json_encode($validator->getLastErrors()));
        }

        $task = new Task(new $data->job, (array) $data->context, $data->id);
        $date = new \DateTime($data->created);
        // restore to local timezone
        $date->setTimezone(new \DateTimezone(date_default_timezone_get()));
        $task->setCreated($date);

        return $task;
    }
}
