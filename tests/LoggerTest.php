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

namespace Wedeto\Log;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Psr\Log\NullLogger;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\AbstractLogger;

use Wedeto\Log\Formatter\FormatterInterface;
use Wedeto\Log\Writer\WriterInterface;
use Wedeto\Log\Writer\MemLogWriter;

/**
 * @covers Wedeto\Log\Logger
 */
class LoggerTest extends TestCase implements WriterInterface
{
    private $logs = array();

    public function getLogger()
    {
        $logger = Logger::getLogger($this);
        $logger->removeLogWriters();
        $logger->addLogWriter($this)->setLevel(LogLevel::DEBUG);
        return $logger;
    }

    public function write(string $level, string $message, array $context)
    {
        $this->logs[] = array($level, $message, $context); 
    }

    public function setFormatter(FormatterInterface $formatter)
    {}

    public function setLevel(string $level)
    {}

    public function isLevelEnabled(string $level, int $level_num = null)
    {
        return true;
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
        $log = $this->getLogger();
        $this->assertInstanceOf('Psr\Log\LoggerInterface', $log);
        $this->assertEquals(str_replace('\\', '.', static::class), $log->getModule());

        $log = Logger::getLogger("");
        $this->assertTrue($log->isRoot());
    }

    public function testSetLogLevels()
    {
        $log = $this->GetLogger();

        $log->setLevel(LogLevel::DEBUG);
        $this->assertEquals(LogLevel::DEBUG, $log->getLevel());
        $this->assertTrue($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertTrue($log->isLevelEnabled(LogLevel::INFO));
        $this->assertTrue($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertTrue($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));
        
        $log->setLevel(LogLevel::INFO);
        $this->assertEquals(LogLevel::INFO, $log->getLevel());
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertTrue($log->isLevelEnabled(LogLevel::INFO));
        $this->assertTrue($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertTrue($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));

        $log->setLevel(LogLevel::NOTICE);
        $this->assertEquals(LogLevel::NOTICE, $log->getLevel());
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($log->isLevelEnabled(LogLevel::INFO));
        $this->assertTrue($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertTrue($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));

        $log->setLevel(LogLevel::WARNING);
        $this->assertEquals(LogLevel::WARNING, $log->getLevel());
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($log->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertTrue($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));

        $log->setLevel(LogLevel::ERROR);
        $this->assertEquals(LogLevel::ERROR, $log->getLevel());
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($log->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertFalse($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));

        $log->setLevel(LogLevel::CRITICAL);
        $this->assertEquals(LogLevel::CRITICAL, $log->getLevel());
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($log->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertFalse($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertFalse($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertTrue($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));

        $log->setLevel(LogLevel::ALERT);
        $this->assertEquals(LogLevel::ALERT, $log->getLevel());
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($log->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertFalse($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertFalse($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertFalse($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertTrue($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));

        $log->setLevel(LogLevel::EMERGENCY);
        $this->assertEquals(LogLevel::EMERGENCY, $log->getLevel());
        $this->assertFalse($log->isLevelEnabled(LogLevel::DEBUG));
        $this->assertFalse($log->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($log->isLevelEnabled(LogLevel::NOTICE));
        $this->assertFalse($log->isLevelEnabled(LogLevel::WARNING));
        $this->assertFalse($log->isLevelEnabled(LogLevel::ERROR));
        $this->assertFalse($log->isLevelEnabled(LogLevel::CRITICAL));
        $this->assertFalse($log->isLevelEnabled(LogLevel::ALERT));
        $this->assertTrue($log->isLevelEnabled(LogLevel::EMERGENCY));

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("Invalid log level: TRACE");
        $log->setLevel("TRACE");
    }

    public function testLevelEnabled()
    {
        $log1 = Logger::getLogger("wedeto.test");
        $log2 = Logger::getLogger("wedeto.test.sub");
        
        $log2->setLevel(LogLevel::INFO);
        $log1->setLevel(LogLevel::ERROR);

        $memlog = new MemLogWriter(LogLevel::INFO);
        $log2->addLogWriter($memlog);
        $this->assertEquals([$memlog], $log2->getLogWriters());
        $this->assertEquals([], $log1->getLogWriters());

        $this->assertTrue($log2->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($log1->isLevelEnabled(LogLevel::INFO));

        $log2->removeLogWriters();
        $this->assertEquals([], $log2->getLogWriters());
        $this->assertFalse($log2->isLevelEnabled(LogLevel::INFO));

        $log1->addLogWriter($memlog);
        $this->assertEquals([$memlog], $log1->getLogWriters());
        $this->assertFalse($log2->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($log1->isLevelEnabled(LogLevel::INFO));

        $log1->setLevel(LogLevel::INFO);
        $this->assertTrue($log2->isLevelEnabled(LogLevel::INFO));
        $this->assertTrue($log1->isLevelEnabled(LogLevel::INFO));
        $log1->removeLogWriters();
        $this->assertEquals([], $log1->getLogWriters());

        // Should bubble up to root now
        $this->assertFalse($log2->isLevelEnabled(LogLevel::INFO));
        $this->assertFalse($log1->isLevelEnabled(LogLevel::INFO));
    }

    public function testLogsAtAllLevels()
    {
        foreach ($this->provideLevelsAndMessages() as $level => $message)
        {
            $logger = $this->getLogger();
            $logger->{$level}($message, array('user' => 'Bob'));
            $logger->log($level, $message, array('user' => 'Bob'));

            $expected = array(
                $level.' message of level ' . $level . ' with context: Bob',
                $level.' message of level ' . $level . ' with context: Bob',
            );
            $logs = $this->getLogs();
            $this->assertEquals($expected, $logs);
        }
    }

    public function provideLevelsAndMessages()
    {
        return array(
            LogLevel::EMERGENCY => 'message of level emergency with context: {user}',
            LogLevel::ALERT => 'message of level alert with context: {user}',
            LogLevel::CRITICAL => 'message of level critical with context: {user}',
            LogLevel::ERROR => 'message of level error with context: {user}',
            LogLevel::WARNING => 'message of level warning with context: {user}',
            LogLevel::NOTICE => 'message of level notice with context: {user}',
            LogLevel::INFO => 'message of level info with context: {user}',
            LogLevel::DEBUG => 'message of level debug with context: {user}',
        );
    }

    public function testLogAtTooLowLevel()
    {
        $logger = $this->getLogger();
        $logger->setLevel(LogLevel::ALERT);
        
        $this->assertFalse($logger->isLevelEnabled(LogLevel::CRITICAL));
        $logger->log(LogLevel::CRITICAL, "Critical error");
        $this->assertEquals([], $this->getLogs());

        $logger->setLevel(LogLevel::CRITICAL);
        $this->assertTrue($logger->isLevelEnabled(LogLevel::CRITICAL));
        $logger->log(LogLevel::CRITICAL, "Critical error");
        $this->assertEquals(['critical Critical error'], $this->getLogs());
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
}

class DummyTest
{
    public function __toString()
    {
        return "DUMMY";
    }
}
