<?php
namespace Teeparty;

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
     * @return array message, channel. null if no task is pending.
     */
    public function pop(array $channels, $timeout = 0);

    /**
     * Put a new Task into the queue.
     *
     * @param Task $task Task to be processed
     * @param string $channel put task into the given channel.
     * 
     * @return void
     * @throws Queue\Exception If the Task could not be pushed.
     */
    public function push(Task $task, $channel);
}
