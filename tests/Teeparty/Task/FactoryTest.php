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

class FactoryTest extends \PHPUnit_Framework_TestCase {

    /**
     * Test that creating a task with a non-existant class throws an exception.
     *
     * @expectedException Teeparty\Task\Exception
     * @expectedExceptionMessage unknown class: \Foo
     */
    public function testCreateUnknownWorkerType()
    {
        Factory::create('\Foo');        
    }


    /**
     * Test that creating a task with an invalid class throws an exception.
     *
     * @expectedException Teeparty\Task\Exception
     * @expectedExceptionMessage \Teeparty\Task\Context must implement \Teep
     */
    public function testCreateInvalidWorkerType()
    {
        Factory::create('\Teeparty\Task\Context');        
    }


    public function testCreateWithId()
    {
        $worker = $this->getMock('Teeparty\Task\Worker');
        $t1 = Factory::create(get_class($worker), array(), 'foo');
        $t2 = Factory::create(get_class($worker), array(), 'foo');

        $this->assertEquals($t1->getId(), $t2->getId());
    }


    public function testCreateRandomId()
    {
        $worker = $this->getMock('Teeparty\Task\Worker');
        $t3 = Factory::create(get_class($worker), array());
        $t4 = Factory::create(get_class($worker), array());
    
        $this->assertNotEquals($t3->getId(), $t4->getId());
    }


    public function testCreateFromArray() {
        $worker = $this->getMock('Teeparty\Task\Worker');
        $t = Factory::createFromArray(array(
            'worker' => get_class($worker),
            'context' => 4,

        ));
        
        $this->assertEquals($t->getContext(), array());
    }
}
