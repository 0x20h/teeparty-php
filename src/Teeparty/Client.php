<?php
namespace Teeparty;

use Teeparty\Task\Result;

/**
 * Interface for queue implementations.
 */
interface Client {
    /**
     * Retrieve a new task from one of the given channels.
     *
     * This method requests the given channels (in order) for a new task. If no 
     * task is pending, the method returns null after $timeout msecs. The first 
     * task that is obtained by one of the channels is returned.
     *
     * @param string $channel Channel to get the next item from.
     * @param float $timeout timeout for listening for new items. 0 will return 
     *                       immediately if no items are present.
     *
     * @return array Task, channel. null if no task is pending.
     * @throws Teeparty\Client\Exception If the Task could not be fetched.
     */
    public function get($channel, $timeout = 0.);

    /**
     * Put a new Task into the channel.
     *
     * @param Task $task Task to be processed
     * @param string $channel put task into the given channel.
     * @param int $execution_time schedule task to be executed at the given unix 
     *                            timestamp.
     * 
     * @return string The Task ID.
     * @throws Teeparty\Client\Exception If the Task could not be inserted.
     */
    public function put(Task $task, $channel, $execution_time = null);


    /**
     * Acknowledge results for a task.
     *
     * @param Result $result Task result.
     */
    public function ack(Result $result);


    /**
     * Remove the task from the system.
     *
     * @param string $taskId The task ID to remove
     */
    public function delete($taskId);

    /**
     * Set a global prefix to use for all keys/channels.
     *
     * @param string $prefix A prefix (e.g. `myapp.`).
     */
    public function setPrefix($prefix);


    /**
     * Return task results.
     *
     * @param string $taskId The task results.
     * @return Teeparty\Task\Result[] Array of results for every try.
     *                                False if no result found.
     */
    public function result($taskId);


    /**
     * Expire the given task in $timeout seconds. 
     * 
     * All task related keys will be removed after the timeout is reached.
     *
     * @param string $taskId The task to expire.
     * @param int $timeout number of seconds until the task expires.
     */
    public function expire($taskId, $timeout);
}
