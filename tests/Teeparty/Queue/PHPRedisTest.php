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
namespace Teeparty\Queue;

use Teeparty\Job;
use Teeparty\Task;

Class PHPRedisTest extends \PHPUnit_Framework_TestCase {

    private $client;

    public function setUp()
    {
        $this->client = $this->getMock('\Redis');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testClientNotConnected()
    {
        $queue = new \Teeparty\Queue\PHPRedis($this->client, '');
    }


    public function testPop()
    {
        $job = $this->getMock('Teeparty\Job');
        $this->assumeClientConnected();
        $queue = new \Teeparty\Queue\PHPRedis($this->client, 'a3d3');
        $msg = json_encode(new Task($job, array()));
        
        $this->client->expects($this->once())
            ->method('evalSHA')
            ->with(
                $this->equalTo('pop'), 
                $this->equalTo(array('chanA','chanB', 'worker.a3d3')), 
                $this->equalTo(3)
            )
            ->will($this->returnValue($msg));
        
        $task = $queue->pop(array('chanA', 'chanB'), 3);
        $this->assertEquals($msg, json_encode($task));
    }


    public function testPopWithInvalidChannels()
    {
        $this->assumeClientConnected();
        $queue = new \Teeparty\Queue\PHPRedis($this->client, '223s');
    }


    public function testPushTaskSuccess()
    {
        $this->assumeClientConnected();
        $queue = new \Teeparty\Queue\PHPRedis($this->client, '23ss');

        $task = new Task($this->getMock('Teeparty\Job'), array());
 
        $this->client->expects($this->once())
            ->method('evalSHA')
            ->with(
                $this->equalTo('push'), 
                $this->equalTo(array(
                    'foo', 
                    'task.' . $task->getId(), 
                    json_encode($task->jsonSerialize())
                )), 
                $this->equalTo(2)
            )
            ->will($this->returnValue(true));

        $queue->push($task, 'foo');
    }

    
    /**
     * @expectedException Teeparty\Queue\Exception foo
     */
    public function testPushTaskException()
    {
        $this->assumeClientConnected();
        $queue = new \Teeparty\Queue\PHPRedis($this->client, '23ss');

        $task = new Task($this->getMock('Teeparty\Job'), array());

        $this->client->expects($this->once())
            ->method('evalSHA')
            ->with(
                $this->equalTo('push'), 
                $this->equalTo(array(
                    'foo', 
                    'task.' . $task->getId(), 
                    json_encode($task->jsonSerialize())
                )), 
                $this->equalTo(2)
            )
            ->will($this->throwException(new \RedisException('foo')));

        $queue->push($task, 'foo');
    }

    /**
     * @covers registerScripts
     */
    protected function assumeClientConnected()
    {
        $this->client->expects($this->once())
            ->method('isConnected')
            ->will($this->returnValue(true));

        // ctor setup, register scripts
        $this->client->expects($this->at(1))
            ->method('multi')
            ->will($this->returnValue($this->client));

        $this->client->expects($this->any())
            ->method('script')
            ->with($this->equalTo('load'));

        $this->client->expects($this->once())
            ->method('exec')
            ->will($this->returnValue(array('ack', 'pop','push')));
    }
}
