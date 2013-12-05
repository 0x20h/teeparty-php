<?php
/**
 * This file is part of the teeparty package.
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

namespace Teeparty\Job;

use Teeparty\Job;

/**
 * This job simply returns its arguments.
 */
class Pong implements Job {

    public function __construct() {}

    public function run(array $context)
    {
        if (isset($context['sleep'])) {
            sleep((int) $context['sleep']);
        }

        if (isset($context['exception'])) {
            if (rand() / getRandMax() < $context['exception']) {
                throw new Exception();
            }
        }

        if (isset($context['fatal'])) {
            if (rand() / getRandMax() < $context['fatal']) {
                $context->unknownMethod();
            }
        }
        
        return $context;
    }


    public function getName()
    {
        return __CLASS__;
    }


    public function getDescription()
    {
        return 'Job for testing functionality like job durations, error '.
            'conditions, etc.';
    }
}

