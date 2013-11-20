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

use Teeparty\Task\Worker;
use Teeparty\Task\Context;

Class TaskTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {
    }

    public function testSerialization()
    {
        $worker = $this->getMock('\Teeparty\Task\Worker');
        $t = new Task($worker, new Context(array('foo' => 'bar')));
        $msg = json_decode(json_encode($t), true);
        $t2 = Task::create($msg['worker'], $msg['context']);
        $this->assertEquals($t, $t2);
    }

    /**
     * Test that creating a task with a non-existant class throws an exception.
     *
     * @expectedException Teeparty\Task\Exception
     * @expectedExceptionMessage unknown class: \Foo
     */
    public function testCreateUnknownWorkerType()
    {
        Task::create('\Foo');        
    }


    /**
     * Test that creating a task with an invalid class throws an exception.
     *
     * @expectedException Teeparty\Task\Exception
     * @expectedExceptionMessage \Teeparty\Task\Context must implement \Teep
     */
    public function testCreateInvalidWorkerType()
    {
        Task::create('\Teeparty\Task\Context');        
    }
}