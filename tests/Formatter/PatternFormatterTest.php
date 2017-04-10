<?php
/*
This is part of Wedeto, the WEb DEvelopment TOolkit.
It is published under the MIT Open Source License.

Copyright 2017, Egbert van der Wal

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace Wedeto\Log\Formatter;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * @covers Wedeto\Log\Formatter\PatternFormatter
 */
final class PatternFormatterTest extends TestCase
{
    public function testFormatter()
    {
        $fmt = new PatternFormatter("[%DATE%][%MODULE%][%LEVEL%] %MESSAGE%");

        $dt = date(\DateTime::ATOM);
        $actual = $fmt->format(LogLevel::ERROR, "Foo", ['_module' => 'PFTest']);
        $expected = "[$dt][PFTest][ERROR] Foo";
        $this->assertEquals($expected, $actual);
    }

    public function testFormatterWithOtherDateFormat()
    {
        $fmt = new PatternFormatter("[%DATE%][%MODULE%][%LEVEL%] %MESSAGE%", \DateTime::COOKIE);
        $dt = date(\DateTime::COOKIE);
        $actual = $fmt->format(LogLevel::ERROR, "Foo", ['_module' => 'PFTest']);
        $expected = "[$dt][PFTest][ERROR] Foo";
        $this->assertEquals($expected, $actual);
    }

    public function testFormatterWithPlaceholders()
    {
        $fmt = new PatternFormatter("[%DATE%][%MODULE%][%LEVEL%] %MESSAGE%");

        $dt = date(\DateTime::ATOM);
        $actual = $fmt->format(LogLevel::ERROR, "Foo {bar}", ['bar' => 'baz', '_module' => 'PFTest']);
        $expected = "[$dt][PFTest][ERROR] Foo baz";
        $this->assertEquals($expected, $actual);
    }
}
