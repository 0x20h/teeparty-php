<?php
namespace Teeparty\Task;

use Teeparty\Task;

class Factory {

    /**
     * Create a new task.
     * 
     * @param string $worker worker class.
     * @param array $context context to run worker in.
     *
     * @return Task A new Task.
     */
    public static function create($worker, array $context = array(), $id = null)
    {
        if (!class_exists($worker)) {
            throw new Exception('unknown class: ' . $worker);
        }

        $w = new $worker;

        if (!$w instanceof Worker) {
            throw new Exception($worker.' must implement \Teeparty\Task\Worker');
        }
        
        $c = new Context($context);
        return new Task($w, $c, $id);
    }
}
