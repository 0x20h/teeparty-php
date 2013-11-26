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

use Teeparty\Queue\PHPRedis;

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
        $queue = new \Teeparty\Queue\PHPRedis($this->client);
    }


    public function testPop()
    {
        $this->assumeClientConnected();
        $queue = new \Teeparty\Queue\PHPRedis($this->client);
        $msg = json_encode(array('foo' => 'bar'));

        $this->client->expects($this->once())
            ->method('brpop')
            ->with($this->equalTo(array('chanA','chanB')), $this->equalTo(3))
            ->will($this->returnValue(array('chanB', $msg)));
        
        list($channel, $rcvdMsg) = $queue->pop(array('chanA', 'chanB'), 3);
        $this->assertEquals('chanB', $channel);
        $this->assertEquals($msg, $rcvdMsg);
    }


    public function testPopWithInvalidChannels()
    {
        $this->assumeClientConnected();
        $queue = new \Teeparty\Queue\PHPRedis($this->client);
    }


    public function testPushTask()
    {
        $this->assumeClientConnected();
        $queue = new \Teeparty\Queue\PHPRedis($this->client);
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
            ->with($this->equalTo('load'))
            ->will($this->returnValue(true));
    }
}
