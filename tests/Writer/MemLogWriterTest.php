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

use Wedeto\Log\Logger;
use Wedeto\Log\Formatter\PatternFormatter;

/**
 * @covers Wedeto\Log\Writer\MemLogWriter
 * @covers Wedeto\Log\Writer\AbstractWriter
 */
class MemLogWriterTest extends TestCase
{
    public function testMemLog()
    {
        $log = Logger::getLogger('wedeto.test');
        $log->removeLogWriters();

        $memlog = new MemLogWriter(LogLevel::DEBUG);
        $this->assertEquals($memlog, MemLogWriter::getInstance());

        $fmt = new PatternFormatter("%MESSAGE%");
        $this->assertInstanceOf(MemLogWriter::class, $memlog->setFormatter($fmt));

        $log->addLogWriter($memlog);
        $log->info("Foobar");

        $actual = $memlog->getLog();
        $expected = ['Foobar'];
        $this->assertEquals($expected, $actual);

        $memlog->setLevel(LogLevel::ERROR);
        $log->info("Foobar2");

        $actual = $memlog->getLog();
        $expected = ['Foobar'];
        $this->assertEquals($expected, $actual);
    }

    public function testSetLogLevels()
    {
        $log = new MemLogWriter(LogLevel::DEBUG);

        $log->setLevel(LogLevel::DEBUG);
        $this->assertTrue($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertTrue($log->isLevelEnabled(LogLevel::INFO));
        $this->assertTrue($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertTrue($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));
        
        $log->setLevel(LogLevel::INFO);
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertTrue($log->isLevelEnabled(LogLevel::INFO));
        $this->assertTrue($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertTrue($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));

        $log->setLevel(LogLevel::NOTICE);
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($log->isLevelEnabled(LogLevel::INFO));
        $this->assertTrue($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertTrue($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));

        $log->setLevel(LogLevel::WARNING);
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($log->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertTrue($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));

        $log->setLevel(LogLevel::ERROR);
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($log->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertFalse($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));

        $log->setLevel(LogLevel::CRITICAL);
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($log->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertFalse($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertFalse($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));

        $log->setLevel(LogLevel::ALERT);
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($log->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertFalse($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertFalse($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertFalse($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));

        $log->setLevel(LogLevel::EMERGENCY);
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($log->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertFalse($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertFalse($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertFalse($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertFalse($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));
    }
    
    public function testFormat()
    {
        $log = new MemLogWriter(LogLevel::DEBUG);

        $actual = $log->format(LogLevel::INFO, "Foo", ['user' => 'john']);
        $expected = "INFO: Foo";
        $this->assertEquals($expected, $actual);

        $fmt = new PatternFormatter("--%MESSAGE%--");
        $log->setFormatter($fmt);
        $expected = "--Foo--";
        $actual = $log->format(LogLevel::INFO, "Foo", ['user' => 'john']);
        $this->assertEquals($expected, $actual);
    }
}
