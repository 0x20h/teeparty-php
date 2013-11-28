<?php
/**
 * This file is part of the teeparty-schema package.
 *
 * Copyright (c) 2013 Jan Kohlhof <kohj@informatik.uni-marburg.de>
 *
 * Permission is hereby granted, free of charge, to any person 
 * obtaining a copy of this software and associated documentation 
 * files (the "Software"), to deal in the Software without 
 * restriction, including without limitation the rights to use, 
 * copy, modify, merge, publish, distribute, sublicense, and/or 
 * sell copies of the Software, and to permit persons to whom the 
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included 
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS 
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING 
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS 
 * IN THE SOFTWARE.
 */

namespace Teeparty\Schema;

Class ValidatorTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException Teeparty\Schema\Exception
     * @expectedExceptionMessage Invalid schema
     */
    public function testValidateEmptySchema()
    {
        $validator = new Validator;
        $validator->validate(false, array());

    }

    /**
     * @expectedException Teeparty\Schema\Exception
     * @expectedExceptionMessage Invalid schema
     */
    public function testValidateInvalidSchemaParam()
    {
        $validator = new Validator;
        $validator->validate(array(), array());
    }
 
    /**
     * @expectedException Teeparty\Schema\Exception
     * @expectedExceptionMessage Invalid schema
     */
    public function testValidateUnknownSchemaParam()
    {
        $validator = new Validator;
        $validator->validate('non-existant', array());
    }
   
    public function testSchemaTask()
    {
        $validator = new Validator;
        $data = $this->getValidData();
        $this->assertEquals(array(), $validator->getLastErrors());
        $this->assertTrue($validator->validate('task', $data));
        $this->assertEquals(array(), $validator->getLastErrors());
        $this->assertFalse($validator->validate('task', array()));
        $data->foo = 'bar';
        $this->assertTrue($validator->validate('task', $data));
        $data->context = new \stdClass;
        $this->assertTrue($validator->validate('task', $data));
    }

    public function testSchemaContext()
    {
        $validator = new Validator;
        $data = $this->getValidData();
        $this->assertEquals(array(), $validator->getLastErrors());
        $this->assertTrue($validator->validate('task', $data));
        $this->assertEquals(array(), $validator->getLastErrors());
        $this->assertFalse($validator->validate('task', array()));
        $this->assertEquals(array(
            array(
                'property' => '',
                'message' => 'array value found, but a object is required'
            )), 
            $validator->getLastErrors()
        );
    }
   
    public function getValidData() {
        return json_decode('{
            "id": "33abef",
            "job": "Foo"
        }');
    }
}
