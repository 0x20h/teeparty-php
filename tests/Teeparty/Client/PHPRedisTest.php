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
namespace Teeparty\Client;

use Teeparty\Job;
use Teeparty\Task;
use Teeparty\Redis\Lua;

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
        $queue = new \Teeparty\Client\PHPRedis($this->client, '');
    }


    public function testGet()
    {
        $lua = new Lua;
        $job = $this->getMock('Teeparty\Job');
        $this->assumeClientConnected();
        $queue = new \Teeparty\Client\PHPRedis($this->client, 'a3d3');
        $task = new Task($job, array());
        $msg = json_encode($task->jsonSerialize());

        $this->client->expects($this->once())
            ->method('evalSHA')
            ->with(
                $this->equalTo($lua->getSha1('task/get')),
                $this->equalTo(array(
                    'chanA',
                    'pending',
                    'processing',
                    'worker.a3d3'
                )),
                $this->equalTo(4)
            )
            ->will($this->returnValue($msg));

        $task = $queue->get('chanA', 3);
        $this->assertEquals($msg, json_encode($task->jsonSerialize()));
    }


    public function testGetWithInvalidChannels()
    {
        $this->assumeClientConnected();
        $queue = new \Teeparty\Client\PHPRedis($this->client, '223s');
    }


    public function testPutTaskSuccess()
    {
        $lua = new Lua;
        $this->assumeClientConnected();
        $queue = new \Teeparty\Client\PHPRedis($this->client, '23ss');

        $task = new Task($this->getMock('Teeparty\Job'), array());

        $this->client->expects($this->once())
            ->method('evalSHA')
            ->with(
                $this->equalTo($lua->getSha1('task/put')),
                $this->equalTo(array(
                    'foo',
                    'task.' . $task->getId(),
                    'pending',
                    json_encode($task->jsonSerialize())
                )),
                $this->equalTo(3)
            )
            ->will($this->returnValue(true));

        $queue->put($task, 'foo');
    }


        /**
         * @expectedException Teeparty\Client\Exception foo
         */
        public function testPutTaskException()
        {
            $lua = new Lua;
            $this->assumeClientConnected();
            $queue = new \Teeparty\Client\PHPRedis($this->client, '23ss');

            $task = new Task($this->getMock('Teeparty\Job'), array());

            $this->client->expects($this->once())
                ->method('evalSHA')
                ->with(
                    $this->equalTo($lua->getSha1('task/put')),
                    $this->equalTo(array(
                        'foo',
                        'task.' . $task->getId(),
                        'pending',
                        json_encode($task->jsonSerialize())
                    )),
                    $this->equalTo(3)
                )
                ->will($this->throwException(new \RedisException('foo')));

            $queue->put($task, 'foo');
        }

    /**
     * @covers registerScripts
     */
    protected function assumeClientConnected()
    {
        $this->client->expects($this->once())
            ->method('isConnected')
            ->will($this->returnValue(true));

        $this->client->expects($this->once())
            ->method('multi')
            ->will($this->returnValue($this->client));

        $this->client->expects($this->any())
            ->method('script')
            ->with($this->equalTo('load'))
            ->will($this->returnValue('foo'));

        $this->client->expects($this->once())
            ->method('exec')
            ->will($this->returnValue(array('foo')));
    }
}
