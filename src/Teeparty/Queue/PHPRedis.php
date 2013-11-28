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
        'pop' => 'pop.lua',
        'push' => 'push.lua',
    );

    private $client;

    private $validator;

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
        $idle = 0;

        while(time() < $now + $timeout) {
            $item = $this->client->evalSHA(
                self::$scriptSHAs['pop'],
                array_merge($channels, array($this->workerId)),
                count($channels)
            );
            
            if (!$item) {
                $error = $this->client->getLastError();

                if ($error) {
                    throw new Exception('redis error: ' . $error);
                }
                    
                $backoff = pow(2, $idle++) * 50000;
                usleep($backoff);
            } else if ($item) {
                $msg = json_decode($item);
                
                if (!$this->validator->validate('task', $msg)) {
                    throw new Exception('invalid task: ' . print_r(
                        $this->validator->getLastErrors(),
                        true
                    ));
                }

                try {
                    $task = Factory::createFromMessage($msg);
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
        $result = $this->client->evalSHA(
            self::$scriptSHAs['push'],
            array(
                $channel,
                'task.' . $task->getId(),
                json_encode($task),
            ),
            2
        );

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

        if ($result) {
            self::$scriptSHAs = array_combine(
                array_keys(self::$scriptSources), 
                $result
            );
           
            return true;
        }

        return false;
    }


    /**
     * Retrieve the sha1 hash for the requested script.
     *
     * @return string SHA1 sum.
     */
    private function script($name)
    {
        return isset(self::$scriptSHAs[$name]) ? self::$scriptSHAs[$name] : null;
    }
}

