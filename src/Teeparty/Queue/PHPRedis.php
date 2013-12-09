<?php

namespace Teeparty\Queue;

use Teeparty\Task;
use Teeparty\Queue;
use Teeparty\Task\Result;
use Teeparty\Task\Factory;
use Teeparty\Schema\Validator;

/**
 * Queue implementation using the PHP redis extension.
 */
class PHPRedis implements Queue {

    private static $scriptSHAs = array();
    protected static $scriptSources = array(
        'ack' => 'ack.lua',
        'pop' => 'pop.lua',
        'push' => 'push.lua',
    );

    private $client;

    private $validator;

    private $idle = 0;

    /**
     * Worker Id.
     *
     * The worker id is used to associate the current task with
     * the processing worker. If the worker dies unexpectedly the
     * task can be rescheduled.
     *
     * @var string
     */
    private $workerId;
    
    public function __construct(
        \Redis $client,
        $workerId,
        Validator $validator = null)
    {
        $this->client = $client;
        $this->workerId = $workerId;
        $this->validator = $validator ? $validator : new Validator;

        if (!$this->client->isConnected()) {
            throw new \RuntimeException('\Redis client is not connected!');
        }

        $this->scripts = $this->registerScripts();
    }

    /**
     * Pop a task and assign workerId to the received task.
     *
     * @param array $channels Channels to fetch a task from, prioritized by index.
     * @param int $timeout Timeout in ms.
     *
     * @return Task A task from on of the provided channels. 
     *              null if no Task was available.
     */
    public function pop(array $channels, $timeout = 2000)
    {
        $now = time();

        while(time() < $now + $timeout) {
            $item = $this->client->evalSHA(
                self::$scriptSHAs['pop'],
                array_merge($channels, array('worker.'.$this->workerId)),
                // use worker_id as key in order to be prefixed correctly
                count($channels) + 1
            );
            
            if (!$item) {
                $error = $this->client->getLastError();

                if ($error) {
                    throw new Exception('redis error: ' . $error);
                }
                  
                if ($this->idle > 5) {
                    $backoff = 2 * 10E5;
                } else {
                    $backoff = pow(2, $this->idle++) * 50000;
                }
                
                usleep($backoff);
            } else if ($item) {
                $this->idle = 0;
                
                try {
                    $task = Task::fromJSON($item);
                    return $task;
                } catch(Exception $e) {
                    throw $e;
                }
            }
            
        }

        return null;
    }

    /**
     * Push a new task to the queue.
     *
     * Use different channels for priorities or groups of workers.
     *
     * @param Task $task The task to accomplish.
     * @param string $channel Channel to push task to.
     *
     * @return string The id of the pushed task.
     * @throws Exception if the task could not be pushed.
     */
    public function push(Task $task, $channel)
    {
        try {
            $result = $this->client->evalSHA(
                self::$scriptSHAs['push'],
                array(
                    $channel,
                    'task.' . $task->getId(),
                    json_encode($task->jsonSerialize()), // 5.3 compat
                ),
                2
            );
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), null, $e);
        }

        if (!$result) {
            $error = $this->client->getLastError();

            if ($error) {
                throw new Exception('redis error: ' . $error);
            }
        }

        return $task->getId();
    }

    
    /**
     * Ack/Nack task results.
     *
     * @param Result $result The task result to ack.
     * 
     */
    public function ack(Result $result)
    {
        $taskId = $result->getTaskId();

        $this->client->evalSHA(
            self::$scriptSHAs['ack'],
            array(
                'result.' . $taskId,
                'task.' . $taskId,
                json_encode($result->jsonSerialize()), // 5.3 compat
            ),
            2
        );
    }

    /**
     * Set a global prefix for keys.
     *
     * A good use case for this is when you have different 
     * applications on the same redis instance.
     *
     * @param string $prefix Global prefix to use.
     */
    public function setPrefix($prefix)
    {
        $this->client->setOption(\Redis::OPT_PREFIX, $prefix);
    }


    public function result($taskId) {
        $resultKey = 'result.' . $taskId;

        $results = $this->client->hgetall($resultKey);

        if (!$results) {
            return false;
        }
        
        $return = array();

        foreach ($results as $key => $result) {
            $return[$key] = Result::fromJSON($result);
        }

        ksort($return);
        return $return;
    }
    /**
     * Register lua scripts and make the SHAs available via self::$scriptSHAs.
     *
     * @return bool True, if the scripts were registered successfully.
     */
    private function registerScripts()
    {
        if (!empty(self::$scripts)) {
            return true;
        }
        
        $multi = $this->client->multi();

        foreach (self::$scriptSources as $script) {
            $multi->script('load', file_get_contents(__DIR__ . '/lua/' . $script));
        }

        $result = $multi->exec();

        if ($result != array_filter($result)) {
            $i = 0;
            $msg = '';

            foreach (self::$scriptSources as $key => $script) {
                if (!$result[$i++]) {
                    $msg .= 'Failed to register script "'. $script . '".'; 
                }
            }
            
            throw new Exception('ERRORS: ' . $msg);
        }

        self::$scriptSHAs = array_combine(
            array_keys(self::$scriptSources), 
            $result
        );
       
        return true;
    }
}
