<?php

namespace Teeparty\Client;

use Teeparty\Task;
use Teeparty\Client;
use Teeparty\Redis\Lua;
use Teeparty\Task\Result;
use Teeparty\Task\Factory;
use Teeparty\Schema\Validator;

/**
 * Client implementation using the PHP redis extension.
 */
class PHPRedis implements Client {

    private static $scriptsRegistered = false;
    private $lua;
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
    
    public function __construct(\Redis $client, $workerId)
    {
        $this->client = $client;
        $this->workerId = $workerId;

        if (!$this->client->isConnected()) {
            throw new \RuntimeException('\Redis client is not connected!');
        }

        $this->validator = new Validator;
        $this->lua = new Lua;

        $this->registerScripts();
    }

    /**
     * Get a task from one of the given channels.
     *
     * Also assign workerId to the received task.
     *
     * @param array $channels Channels to fetch a task from, prioritized by index.
     * @param int $timeout Timeout in ms.
     *
     * @return Task A task from on of the provided channels. 
     *              null if no Task was available.
     */
    public function get(array $channels, $timeout = 2000)
    {
        $now = time();

        while(time() < $now + $timeout) {
            $item = $this->script(
                'task/get',
                array_merge($channels, array('worker.'.$this->workerId)),
                // use worker_id as key in order to be prefixed correctly
                count($channels) + 1
            );

            if ($item) {
                $this->idle = 0;

                try {
                    $task = Task::fromJSON($item);
                    return $task;
                } catch(Exception $e) {
                    throw $e;
                }
            }

            $sleep = pow(2, $this->idle++);
            $backoff = $sleep > $timeout ? $timeout * 1E6 : $sleep * 50000;
            usleep($backoff);
        }

        return null;
    }

    /**
     * Put a new task into the given channel.
     *
     * @param Task $task The task to accomplish.
     * @param string $channel Channel to put task to.
     *
     * @return string The id of the added task.
     * @throws Exception if the task could not be added.
     */
    public function put(Task $task, $channel)
    {
        $this->script(
            'task/put',
            array(
                $channel,
                'task.' . $task->getId(),
                json_encode($task->jsonSerialize()), // 5.3 compat
            ),
            2
        );

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
            $this->lua->getSHA1('task/ack'),
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
     * Register lua scripts.
     *
     * @return bool True, if the scripts were registered successfully.
     */
    private function registerScripts()
    {

        if (self::$scriptsRegistered) {
            return true;
        }
        
        $scripts = $this->lua->getScripts();
        $multi = $this->client->multi();

        foreach ($scripts as $script) {
            $multi->script('load', $script);
        }

        $result = $multi->exec();

        if ($result != array_filter($result)) {
            $i = 0;
            $msg = '';

            foreach ($scripts as $script => $source) {
                if (!$result[$i++]) {
                    $msg .= 'Failed to register script "'. $script . '".'; 
                }
            }
            
            throw new Exception('ERRORS: ' . $msg);
        }

        self::$scriptsRegistered = true;
        return true;
    }


    /**
     * Run the named lua script.
     *
     * @param string $script The named script
     * @param array $args The script arguments
     * @param int $numKeys number of keys (available as KEYS[] in lua)
     *
     * @return mixed Results of executing the script.
     */
    protected function script($script, array $args, $numKeys)
    {
        try {
            $result = $this->client->evalSHA(
                $this->lua->getSHA1($script), $args, $numKeys
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

        return $result;
    }
}
