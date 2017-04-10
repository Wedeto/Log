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

namespace Wedeto\Log\Writer;

use PHPUnit\Framework\TestCase;

use Psr\Log\LogLevel;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

use Wedeto\Log\Logger;
use Wedeto\Log\Formatter\FormatterInterface;

/**
 * @covers Wedeto\Log\Writer\AbstractWriter
 */
final class AbstractWriterTest extends TestCase
{
    public function testAbstractWriter()
    {
        $writer_mock = $this->getMockForAbstractClass(AbstractWriter::class);

        $this->assertEquals($writer_mock, $writer_mock->setLevel(LogLevel::DEBUG));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::DEBUG));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::INFO));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::NOTICE));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::EMERGENCY));

        $this->assertEquals($writer_mock, $writer_mock->setLevel(LogLevel::INFO));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::DEBUG));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::INFO));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::NOTICE));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::EMERGENCY));

        $this->assertEquals($writer_mock, $writer_mock->setLevel(LogLevel::NOTICE));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::INFO));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::NOTICE));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::EMERGENCY));

        $this->assertEquals($writer_mock, $writer_mock->setLevel(LogLevel::WARNING));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::NOTICE));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::EMERGENCY));

        $this->assertEquals($writer_mock, $writer_mock->setLevel(LogLevel::ERROR));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::NOTICE));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::EMERGENCY));

        $this->assertEquals($writer_mock, $writer_mock->setLevel(LogLevel::CRITICAL));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::NOTICE));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::WARNING));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::EMERGENCY));

        $this->assertEquals($writer_mock, $writer_mock->setLevel(LogLevel::ALERT));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::NOTICE));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::WARNING));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::ERROR));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::EMERGENCY));

        $this->assertEquals($writer_mock, $writer_mock->setLevel(LogLevel::EMERGENCY));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::NOTICE));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::WARNING));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::ERROR));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertFalse($writer_mock->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($writer_mock->isLevelEnabled(LogLevel::EMERGENCY));

        $actual = $writer_mock->format(LogLevel::EMERGENCY, "Test {fmt} {bar}", ['fmt' => 3]);
        $expected = "Test 3 {bar}";
        $this->assertEquals($expected, $actual);

        $fmt_mock = $this->prophesize(FormatterInterface::class);
        $fmt = $fmt_mock->reveal();
        $this->assertEquals($writer_mock, $writer_mock->setFormatter($fmt));
    }
}

