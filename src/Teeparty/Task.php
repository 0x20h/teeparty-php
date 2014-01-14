<?php
namespace Teeparty;

use Teeparty\Task\Result;
use Teeparty\JsonSerializable;
use Teeparty\Schema\Validator;

class Task implements \Serializable, JsonSerializable {

    private static $meta_keys = array('id' ,'created', 'max_tries', 'tries');
    private $job;
    private $context;
    private $meta = array();

public function __construct(
        Job $job,
        array $context = array(),
        array $meta = array())
    {
        $this->job = $job;
        $this->context = $context;

        $this->meta($meta);
    }


    /**
     * Set and/or retrieve task meta information.
     *
     * @param array $meta Meta information hash.
     * @return array current (updated) meta information.
     */
    public function meta(array $meta = array())
    {
        if (!empty($meta)) {
            foreach (self::$meta_keys as $key) {
                $this->meta[$key] = isset($meta[$key]) ? $meta[$key] : null;
            }
        }

        // set required values

        if (empty($this->meta['id'])) {
            $this->meta['id'] = uniqid();
        }

        if (empty($this->meta['created'])) {
            $this->meta['created'] = date('c');
        }

        if (empty($this->meta['max_tries'])) {
            $this->meta['max_tries'] = 3;
        }

        if (empty($this->meta['tries'])) {
            $this->meta['tries'] = 0;
        }

        return $this->meta;
    }


    /**
     * Retrieve the task id.
     *
     * @return string Task ID.
     */
    public function getId()
    {
        return $this->meta['id'];
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
            'job' => get_class($this->job),
            'context' => $this->context,
            'meta' => $this->meta,
        ));
    }


    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->job = new $data['job'];
        $this->context = $data['context'];
        $this->meta = $data['meta'];
    }


    public function jsonSerialize()
    {
        return array(
            'job' => get_class($this->job),
            'context' => $this->context,
            'meta' => $this->meta()
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

        $task = new Task(
            new $data->job,
            (array) $data->context,
            (array) $data->meta
        );

        return $task;
    }
}
