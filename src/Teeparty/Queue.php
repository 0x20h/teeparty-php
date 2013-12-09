<?php
namespace Teeparty;

use Teeparty\Task\Result;

/**
 * Interface for queue implementations.
 */
interface Queue {
    /**
     * Retrieve a new task from the queue.
     *
     * @param string[] $channels channels to pop from
     * @param int $timeout timeout for listening for new items.
     *
     * @return array Task, channel. null if no task is pending.
     * @throws Teeparty\Queue\Exception If the Task could not be fetched.
     */
    public function pop(array $channels, $timeout = 0);

    /**
     * Put a new Task into the queue.
     *
     * @param Task $task Task to be processed
     * @param string $channel put task into the given channel.
     * 
     * @return string The Task ID.
     * @throws Teeparty\Queue\Exception If the Task could not be pushed.
     */
    public function push(Task $task, $channel);


    /**
     * Set results for a task.
     *
     * @param Result $result Task result.
     */
    public function ack(Result $result);


    /**
     * set a global prefix to use for all keys/queues.
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
