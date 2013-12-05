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
namespace Teeparty\Task;

use Teeparty\Task;
use Teeparty\Task\Result;
use Teeparty\Task\Exception;

class ResultTest extends \PHPUnit_Framework_TestCase {

    private $job;
    
    public function setUp()
    {
        $this->job = $this->getMock('Teeparty\Job');
    }


    public function testConstruct()
    {
        $rs = array('foo' => 'bar');
        $task = new Task($this->job, $rs);
        $result = new Result($task, Result::STATUS_OK, $rs);
        $this->assertEquals(Result::STATUS_OK, $result->getStatus());
        $this->assertEquals($rs, $result->getResult());

        $result = new Result($task, Result::STATUS_FAILED);
        $this->assertEquals(Result::STATUS_FAILED, $result->getStatus());
        $this->assertEmpty($result->getResult());
    }


    public function testExceptionResult()
    {
        $task = new Task($this->job);
        
        $e = new Exception('exception');
        $result = new Result($task, Result::STATUS_EXCEPTION, $e);
        $this->assertEquals(Result::STATUS_EXCEPTION, $result->getStatus());
        $this->assertEquals($result->getResult(), $e);

        $fromJSON = json_decode(json_encode($result), true);
        $this->assertEquals($fromJSON['status'], Result::STATUS_EXCEPTION);
        $this->assertEquals($fromJSON['task_id'], $task->getId());
        $this->assertEquals($fromJSON['returnValue']['message'], 'exception');
        $this->assertEquals($fromJSON['returnValue']['type'], 'Teeparty\Task\Exception');
        $this->assertTrue(isset($fromJSON['returnValue']['stack']));
    }
}
