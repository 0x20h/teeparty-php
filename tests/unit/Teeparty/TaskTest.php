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
use Teeparty\Task\Result;

Class TaskTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {
    }

    public function testGetId() {
        $job = $this->getMock('Teeparty\Job');
        $t1 = new Task($job, array('foo' => 'bar'));
        $t2 = new Task($job, array('foo' => 'bar'));

        // every task must have a unique id
        $this->assertNotEquals($t1->getId(), $t2->getId());
    }

    public function testJsonSerialize()
    {
        $job = $this->getMock('\Teeparty\Job');
        $t = new Task($job, array('foo' => 'bar'));
        $msg = json_encode($t->jsonSerialize()); // PHP 5.3 compat
        $t2 = Task::fromJSON($msg);
        $this->assertEquals($t, $t2);
    }

    public function testSerialize()
    {
        $job = $this->getMock('Teeparty\Job');
        $t = new Task($job, array('foo' => 'bar'));
        $msg = serialize($t);
        $t2 = unserialize($msg);
        $this->assertEquals($t, $t2);
    }

    public function testExecuteTaskFailed()
    {
        $job = $this->getMock('Teeparty\Job');
        $context = array('foo' => 'bar');
        $job->expects($this->once())
            ->method('run')
            ->with($this->equalTo($context))
            ->will($this->returnValue(false));

        $t = new Task($job, $context);
        $result = $t->execute();
        $this->assertEquals($result->getStatus(), Result::STATUS_FAILED);
    }

    public function testExecuteTaskSuccess()
    {
        $job = $this->getMock('Teeparty\Job');
        $context = array('foo' => 'bar');
        $job->expects($this->once())
            ->method('run')
            ->with($this->equalTo($context))
            ->will($this->returnValue(true));

        $t = new Task($job, $context);
        $result = $t->execute();
        $this->assertEquals($result->getStatus(), Result::STATUS_OK);
        $this->assertEquals($result->getResult(), true);
        $this->assertTrue($result->getExecutionTime() > -1);
    }

    public function testExecuteTaskException()
    {
        $job = $this->getMock('\Teeparty\Job');
        $context = array('foo' => 'bar');
        $exception = new Exception('exception');
        $job->expects($this->once())
            ->method('run')
            ->with($this->equalTo($context))
            ->will($this->throwException($exception));

        $t = new Task($job, $context);
        $result = $t->execute();
        $this->assertEquals($result->getStatus(), Result::STATUS_EXCEPTION);
        $this->assertEquals($result->getResult(), $exception);
        $this->assertTrue($result->getExecutionTime() > -1);
    }

    public function testStates() {
        $this->assertEquals(Result::states(), array(
            Result::STATUS_OK,
            Result::STATUS_FAILED,
            Result::STATUS_EXCEPTION,
            Result::STATUS_FATAL
        ));
    }
}
