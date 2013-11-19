<?php
namespace Teeparty\Task;

/**
 * Interface for workers.
 */
interface Worker {

    /**
     * Empty constructor is needed.
     */
//    public function __construct();

    /**
     * Perform an execution using the given context.
     *
     * @param Context $context Context information for the job.
     * 
     * @return void
     * @throws Job\Exception
     */
    public function work(Context $context);
}
