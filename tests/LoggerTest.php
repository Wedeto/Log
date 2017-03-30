<?php
/*
This is part of WASP, the Web Application Software Platform.
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

namespace WASP\Log;

use PHPUnit\Framework\TestCase;
use WASP\Log\LogWriterInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Psr\Log\NullLogger;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\AbstractLogger;

/**
 * @covers WASP\Log\Logger
 * @covers Psr\Log\LoggerInterface
 * @covers Psr\Log\LoggerTrait
 * @covers Psr\Log\AbstractLogger
 * @covers Psr\Log\NullLogger
 * @covers Psr\Log\LoggerAwareTrait
 */
class LoggerTest extends TestCase implements LogWriterInterface
{
    private $logs = array();

    public function getLogger()
    {
        $logger = Logger::getLogger('WASP.Log.Logger');
        $logger->removeLogHandlers();
        $logger->addLogHandler($this)->setLevel(LogLevel::DEBUG);
        return $logger;
    }

    public function write(string $level, $message, array $context)
    {
        $this->logs[] = array($level, $message, $context); 
    }

    /**
     * This must return the log messages in order.
     * The simple formatting of the messages is: "<LOG LEVEL> <MESSAGE>".
     * $log->error('Foo') would yield "error Foo".
     *
     * @return string[]
     */
    public function getLogs()
    {
        $lines = array();
        foreach ($this->logs as $l)
            $lines[] = $l[0] . " " . Logger::fillPlaceholders($l[1], $l[2]);
        $this->logs = array();
        return $lines;
    }

    public function testImplements()
    {
        $this->assertInstanceOf('Psr\Log\LoggerInterface', $this->getLogger());
    }

    public function testLogsAtAllLevels()
    {
        foreach ($this->provideLevelsAndMessages() as $msg)
        {
            $level = $msg[0];
            $message = $msg[1];
            $logger = $this->getLogger();
            $logger->{$level}($message, array('user' => 'Bob'));
            $logger->log($level, $message, array('user' => 'Bob'));

            $expected = array(
                $level.' message of level '.$level.' with context: Bob',
                $level.' message of level '.$level.' with context: Bob',
            );
            $logs = $this->getLogs();
            $this->assertEquals($expected, $logs);
        }
    }

    public function provideLevelsAndMessages()
    {
        return array(
            LogLevel::EMERGENCY => array(LogLevel::EMERGENCY, 'message of level emergency with context: {user}'),
            LogLevel::ALERT => array(LogLevel::ALERT, 'message of level alert with context: {user}'),
            LogLevel::CRITICAL => array(LogLevel::CRITICAL, 'message of level critical with context: {user}'),
            LogLevel::ERROR => array(LogLevel::ERROR, 'message of level error with context: {user}'),
            LogLevel::WARNING => array(LogLevel::WARNING, 'message of level warning with context: {user}'),
            LogLevel::NOTICE => array(LogLevel::NOTICE, 'message of level notice with context: {user}'),
            LogLevel::INFO => array(LogLevel::INFO, 'message of level info with context: {user}'),
            LogLevel::DEBUG => array(LogLevel::DEBUG, 'message of level debug with context: {user}'),
        );
    }

    public function testThrowsOnInvalidLevel()
    {
        $logger = $this->getLogger();
        $this->expectException(\Psr\Log\InvalidArgumentException::class);
        $logger->log('invalid level', 'Foo');
    }

    public function testContextReplacement()
    {
        $logger = $this->getLogger();
        $logger->info('{Message {nothing} {user} {foo.bar} a}', array('user' => 'Bob', 'foo.bar' => 'Bar'));

        $expected = array('info {Message {nothing} Bob Bar a}');
        $this->assertEquals($expected, $this->getLogs());
    }

    public function testObjectCastToString()
    {
        $dummy = new DummyTest();
        $this->getLogger()->warning($dummy);

        $expected = array('warning DUMMY');
        $this->assertEquals($expected, $this->getLogs());
    }

    public function testContextCanContainAnything()
    {
        $context = array(
            'bool' => true,
            'null' => null,
            'string' => 'Foo',
            'int' => 0,
            'float' => 0.5,
            'nested' => array('with object' => new DummyTest),
            'object' => new \DateTime,
            'resource' => fopen('php://memory', 'r'),
        );

        $this->getLogger()->warning('Crazy context data', $context);

        $expected = array('warning Crazy context data');
        $this->assertEquals($expected, $this->getLogs());
    }

    public function testContextExceptionKeyCanBeExceptionOrOtherValues()
    {
        $logger = $this->getLogger();
        $logger->warning('Random message', array('exception' => 'oops'));
        $logger->critical('Uncaught Exception!', array('exception' => new \LogicException('Fail')));

        $expected = array(
            'warning Random message',
            'critical Uncaught Exception!'
        );
        $this->assertEquals($expected, $this->getLogs());
    }

    public function testTrait()
    {
        $a = new DummyLogger(array($this, 'write'));
        
        foreach ($this->provideLevelsAndMessages() as $level => $msg)
        {
            $a->$level($msg[1], array('user' => 'Bob'));
            $logs = $this->getLogs();
            $this->assertEquals(
                $logs,
                array("${msg[0]} message of level ${msg[0]} with context: Bob")
            );
        }
    }

    public function testNullLogger()
    {
        $exception = null;
        $a = new NullLogger;
        foreach ($this->provideLevelsAndMessages() as $level => $msg)
        {
            try
            {
                $a->$level($msg[1], array('user' => 'Bob'));
            }
            catch (\Throwable $e)
            {
                $exception = $e;
                throw $e;
            }
        }
        $this->assertNull($exception);
    }

    public function testLoggerAware()
    {
        $a = new DummyLoggable();

        $logger = $this->getLogger();
        $a->test();

        $this->assertEquals(array(), $this->getLogs());

        $a->setLogger($logger);
        $a->test();

        $this->assertEquals(array('info ok'), $this->getLogs());
    }
}

class DummyTest
{
    public function __toString()
    {
        return "DUMMY";
    }
}

class DummyLogger implements LoggerInterface
{
    use LoggerTrait;

    private $cb;

    public function __construct($callback)
    {
        $this->cb = $callback;
    }

    public function log($level, $message, array $context = array())
    {
        call_user_func($this->cb, $level, $message, $context);
    }
}

class DummyLoggable implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function test()
    {
        if ($this->logger)
            $this->logger->info('ok');
    }
}
