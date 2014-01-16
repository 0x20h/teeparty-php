<?php
/**
 * This file is part of the teeparty library.
 *
 * Copyright (c) 2013 Jan Kohlhof
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this 
 * software and associated documentation files (the "Software"), to deal in the Software 
 * without restriction, including without limitation the rights to use, copy, modify, merge, 
 * publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons 
 * to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or 
 * substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE 
 * FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, 
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
 * SOFTWARE.
 */
namespace Teeparty;

use Teeparty\Job;
use Teeparty\Client\PHPRedis;
use Teeparty\Task\Result;

Class TaskFlowTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $this->redis = new \Redis();

        if(!$this->redis->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT'])) {
            return $this->markTestSkipped('connect to redis ' .
                $_ENV['REDIS_HOST'] . ':' . $_ENV['REDIS_PORT'] . ' failed');
        }
        
        $this->workerId = uniqid('integration_tests_worker_id_');
        $this->prefix = uniqid('integration_tests_') . '.';
        $this->client = new PHPRedis($this->redis, $this->workerId);
        $this->client->setPrefix($this->prefix);
    }


    public function tearDown()
    {
        $keys = $this->redis->keys($this->prefix. '*');
        $this->redis->del($keys);
    }


    public function testTaskLifecycle()
    {
        $channel = uniqid('integration_tests_');
        $task1 = new Task(new Job\Test, array());
        $this->client->put($task1, $channel);
        // lpush task

        $task2 = $this->client->get($channel);
        $this->assertEquals($task1->getContext(), $task2->getContext());
        $this->assertEquals($task1->getId(), $task2->getId());
        // var_dump($task2->meta());

        $result = $task2->execute();
        $this->assertEquals(Result::STATUS_OK, $result->getStatus());

        $this->client->ack($result);
        $this->client->delete($task1->getId());
        $this->assertEmpty($this->redis->keys($this->prefix. '*'));
    }


    public function testDelayedTaskInsertion()
    {
        $channel = uniqid('integration_tests_');
        $task1 = new Task(new Job\Test, array('exception' => 1));
        $execution_time = time() + 20;
        $task_id = $this->client->put($task1, $channel, $execution_time);
        $this->assertEquals($task1->getId(), $task_id);

        $task2 = $this->client->get($channel, .1);
        $this->assertNull($task2);

        // check that schedule zset contains the task
        $tasks = $this->redis->zrangebyscore($this->prefix . 'scheduler',
            $execution_time, $execution_time);

        $this->assertContains($task_id, $tasks);
    }


    /**
     * When there is not item in the queue, get() will block for timeout 
     * seconds, then return null.
     */
    public function testTaskGetTimeout()
    {
        $s = microtime(true);
        $this->assertNull($this->client->get(uniqid('foo_empty_queue'), .3));
        $blockedFor = microtime(true) - $s;
        $this->assertEquals(0.3, round($blockedFor, 1));
    }
}
