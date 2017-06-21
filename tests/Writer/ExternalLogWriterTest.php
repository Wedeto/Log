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

/**
 * @covers Wedeto\Log\Writer\ExternalLogWriter
 * @covers Wedeto\Log\Writer\AbstractWriter
 */
final class ExternalLogWriterTest extends TestCase
{
    public function testExternalLogger()
    {
        $elog = new TestLogger();

        $logger = Logger::getLogger('foo.bar');
        $logger->setLevel(LogLevel::DEBUG);

        $elogwriter = new ExternalLogWriter($elog);
        $this->assertEquals($elog, $elogwriter->getLogger());
        $logger->addLogWriter($elogwriter);

        $logger->debug("Foo {bar}", ['bar' => 'baz']);
        $logger->info("Foo {bar}", ['bar' => 'baz']);
        $logger->notice("Foo {bar}", ['bar' => 'baz']);
        $logger->warning("Foo {bar}", ['bar' => 'baz']);
        $logger->error("Foo {bar}", ['bar' => 'baz']);
        $logger->critical("Foo {bar}", ['bar' => 'baz']);
        $logger->alert("Foo {bar}", ['bar' => 'baz']);
        $logger->emergency("Foo {bar}", ['bar' => 'baz']);

        $logs = $elog->getLog();

        $this->assertEquals(8, count($logs));

        $expected_msg = "Foo {bar}";
        $expected_context = [
            ['bar' => 'baz', '_module' => 'foo.bar', '_accept' => 'foo.bar', '_level' => 'debug'],
            ['bar' => 'baz', '_module' => 'foo.bar', '_accept' => 'foo.bar', '_level' => 'info'],
            ['bar' => 'baz', '_module' => 'foo.bar', '_accept' => 'foo.bar', '_level' => 'notice'],
            ['bar' => 'baz', '_module' => 'foo.bar', '_accept' => 'foo.bar', '_level' => 'warning'],
            ['bar' => 'baz', '_module' => 'foo.bar', '_accept' => 'foo.bar', '_level' => 'error'],
            ['bar' => 'baz', '_module' => 'foo.bar', '_accept' => 'foo.bar', '_level' => 'critical'],
            ['bar' => 'baz', '_module' => 'foo.bar', '_accept' => 'foo.bar', '_level' => 'alert'],
            ['bar' => 'baz', '_module' => 'foo.bar', '_accept' => 'foo.bar', '_level' => 'emergency']
        ];
        
        $msg = array_shift($logs);
        $this->assertEquals(LogLevel::DEBUG, $msg[0]);
        $this->assertEquals($expected_msg, $msg[1]);
        $this->assertEquals($expected_context[0], $msg[2]);

        $msg = array_shift($logs);
        $this->assertEquals(LogLevel::INFO, $msg[0]);
        $this->assertEquals($expected_msg, $msg[1]);
        $this->assertEquals($expected_context[1], $msg[2]);

        $msg = array_shift($logs);
        $this->assertEquals(LogLevel::NOTICE, $msg[0]);
        $this->assertEquals($expected_msg, $msg[1]);
        $this->assertEquals($expected_context[2], $msg[2]);

        $msg = array_shift($logs);
        $this->assertEquals(LogLevel::WARNING, $msg[0]);
        $this->assertEquals($expected_msg, $msg[1]);
        $this->assertEquals($expected_context[3], $msg[2]);

        $msg = array_shift($logs);
        $this->assertEquals(LogLevel::ERROR, $msg[0]);
        $this->assertEquals($expected_msg, $msg[1]);
        $this->assertEquals($expected_context[4], $msg[2]);

        $msg = array_shift($logs);
        $this->assertEquals(LogLevel::CRITICAL, $msg[0]);
        $this->assertEquals($expected_msg, $msg[1]);
        $this->assertEquals($expected_context[5], $msg[2]);

        $msg = array_shift($logs);
        $this->assertEquals(LogLevel::ALERT, $msg[0]);
        $this->assertEquals($expected_msg, $msg[1]);
        $this->assertEquals($expected_context[6], $msg[2]);

        $msg = array_shift($logs);
        $this->assertEquals(LogLevel::EMERGENCY, $msg[0]);
        $this->assertEquals($expected_msg, $msg[1]);
        $this->assertEquals($expected_context[7], $msg[2]);
    }
}

class TestLogger extends AbstractLogger
{
    private $log = array();

    public function log($level, $message, array $context = [])
    {
        $this->log[] = [$level, $message, $context];
    }

    public function getLog()
    {
        return $this->log;
    }
}
