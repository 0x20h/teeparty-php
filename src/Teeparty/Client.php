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
     * @param int $timeout timeout for listening for new items.
     *
     * @return array Task, channel. null if no task is pending.
     * @throws Teeparty\Client\Exception If the Task could not be fetched.
     */
    public function get($channel, $timeout = 0);

    /**
     * Put a new Task into the channel.
     *
     * @param Task $task Task to be processed
     * @param string $channel put task into the given channel.
     * 
     * @return string The Task ID.
     * @throws Teeparty\Client\Exception If the Task could not be pushed.
     */
    public function put(Task $task, $channel);


    /**
     * Acknowledge results for a task.
     *
     * @param Result $result Task result.
     */
    public function ack(Result $result);


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
}
