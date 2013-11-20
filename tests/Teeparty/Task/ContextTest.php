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

class ContextTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {
    }

    public function testReadAccess()
    {
        $x = array(
            'foo' => 'bar',
            'bar' => array(
                'baz' => 'bam'
            ),
        );

        $c = new Context($x);
        $this->assertEquals($c['bar']['baz'], 'bam');
        $this->assertEquals($c['non_existant'], null);
    }


    /**
     * @expectedException Teeparty\Task\Exception
     * @expectedExceptionMessage write access denied
     */
    public function testWritesCauseException()
    {
        $c = new Context(array('foo'=>'bar'));
        $c['foo'] = 'bar';
    }

    /**
     * @expectedException Teeparty\Task\Exception
     * @expectedExceptionMessage write access denied
     */
    public function testUnsetCauseException()
    {
        $c = new Context(array('foo' => 'bar'));
        unset($c['foo']);
    }


    /**
     * @expectedException Teeparty\Task\Exception
     * @expectedExceptionMessage cannot store object of type 
     */
    public function testObjectsMustImplementSerializable()
    {
        $c = new Context(array('foo' => array('bar' => new \stdClass())));
    }


    /**
     * @covers validate
     */
    public function testSerializableObjects()
    {
        $c = new Context(array('foo' => array(
            'bar' => new Context(array('foo' => 'bar'))
        )));

        $this->assertTrue($c['foo']['bar'] instanceof Context);
        $c2 = unserialize(serialize($c));
        $this->assertTrue($c2['foo']['bar'] instanceof Context);
        $this->assertEquals($c2['foo']['bar']['foo'], 'bar');
    }


    public function testSerialization()
    {
        $c = new Context(array('foo' => 'bar'));
        $this->assertEquals($c, unserialize(serialize($c)));
    }

}
