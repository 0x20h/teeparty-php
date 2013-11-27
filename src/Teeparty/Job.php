<?php
namespace Teeparty;

/**
 * Interface for jobs.
 */
interface Job {

    public function __construct();

    /**
     * Perform an execution using the given context.
     *
     * @param Context $context Context information for the job.
     * 
     * @return void
     * @throws Exception
     */
    public function run(Task\Context $context);
}
