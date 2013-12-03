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
     * @param array $context context for the job.
     * 
     * @return void
     * @throws Exception
     */
    public function run(array $context);
    public function getName();
    public function getDescription();
}
