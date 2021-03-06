<?php
namespace Teeparty\Task;

use Teeparty\Task;
use Teeparty\JsonSerializable;
use Teeparty\Schema\Validator;

/**
 * Represent task returnValues and status information.
 */
class Result implements JsonSerializable {

    const STATUS_OK = 'ok';
    const STATUS_FAILED = 'failed';
    const STATUS_EXCEPTION = 'exception';
    const STATUS_FATAL = 'fatal';

    private $returnValue;
    private $executionTime = -1;
    private $startDate = null;
    private $status;
    private $taskId;

    public function __construct($taskId, $status, $returnValue = null)
    {
        $this->taskId = $taskId;
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
        return round($this->executionTime, 4);
    }
    
    
    public function getTaskId()
    {
        return $this->taskId;
    }


    public function setStartDate(\DateTime $date) {
        $this->startDate = $date;
    }


    public function getStartDate() {
        return $this->startDate;
    }
    
   
    /**
     * JSON serialization format
     *
     * @return array 
     */
    public function jsonSerialize() {
        return array(
            'task_id' => $this->getTaskId(),
            'status' => $this->getStatus(),
            'start_date' => $this->getStartDate() ?
                $this->getStartDate()->format(\DateTime::ATOM) :
                null,
            'execution_time' => $this->getExecutionTime(),
            'returnValue' => is_object($this->returnValue)  // PHP 5.3 compat
                ? $this->returnValue->jsonSerialize()
                : $this->returnValue
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


    /**
     * Create a result object from JSON.
     *
     * @param string $json JSON encoded result object.
     * @param Validator $validator JSON schema validator.
     * 
     * @return Result The result object.
     */
    public static function fromJSON($json, Validator $validator = null) {
        $data = json_decode($json);
        $validator = $validator ? $validator : new Validator();

        if (!$validator->validate('result', $data)) {
            throw new Exception('Result validation failed: ' .
                json_encode($validator->getLastErrors()));
        }

        $result = new Result(
            $data->task_id,
            $data->status,
            is_string($data->returnValue) ?
                json_decode($data->returnValue, true) :
                (array) $data->returnValue
        );

        $result->setExecutionTime($data->execution_time);
        $result->setStartDate(new \DateTime($data->start_date));
        return $result;
    }

    private function validate($data)
    {
        if (is_array($data)) {
            foreach ($data as $item) {
                $this->validate($item);
            }
        }

        if (is_object($data) && !($data instanceof JsonSerializable)) {
            throw new Exception('cannot store object of type '.get_class($data));
        }
    }
}
