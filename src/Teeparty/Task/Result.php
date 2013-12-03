<?php
namespace Teeparty\Task;

use Teeparty\Task;

/**
 * Represent task returnValues and status information.
 */
class Result implements \JsonSerializable {

    const STATUS_OK = 'ok';
    const STATUS_FAILED = 'fail';
    const STATUS_EXCEPTION = 'exception';
    const STATUS_FATAL = 'fatal';

    private $returnValue;
    private $executionTime = -1;
    private $status;
    private $task;

    public function __construct(Task $task, $status, $returnValue = null)
    {
        $this->task = $task;
        $this->setStatus($status);
        $this->validate($returnValue);
        $this->returnValue = $returnValue;
    }


    public function setResult($returnValue)
    {
        $this->returnValue = $returnValue;
    }


    public function getResult()
    {
        return $this->returnValue;
    }

    /**
     * Set the returnValue status.
     *
     * @param int $status Status identifier.
     */
    protected function setStatus($status)
    {
        if (!in_array($status, $this->states())) {
            return;
        }

        $this->status = $status;
    }

    /**
     * Return result status.
     *
     * @return int Status identifier.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the execution time for this result.
     *
     * @param float $time execution time in msecs.
     */
    public function setExecutionTime($time) {
        $this->executionTime = (float) $time;
    }

    /**
     * Retrieve the execution time for this result.
     *
     * @return float execution time in msecs.
     */
    public function getExecutionTime() {
        return $this->executionTime;
    }
    
    
    public function getTask()
    {
        return $this->task;
    }


    /**
     * JSON serialization format
     *
     * @return array 
     */
    public function jsonSerialize() {
        return array(
            'status' => $this->status,
            'task_id' => $this->task->getId(),
            'execution_time' => $this->executionTime,
            'returnValue' => json_encode($this->returnValue)
        );
    }

   
    /**
     * Retrieve valid result states.
     *
     * @return array Valid states
     */
    public static function states() {
       return array(
            self::STATUS_OK, 
            self::STATUS_FAILED, 
            self::STATUS_EXCEPTION,
            self::STATUS_FATAL
        );
    }


    private function validate($data)
    {
        if (is_array($data)) {
            foreach ($data as $item) {
                $this->validate($item);
            }
        }

        if (is_object($data) && !($data instanceof \JsonSerializable)) {
            throw new Exception('cannot store object of type '.get_class($data));
        }
    }
}
