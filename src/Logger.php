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

use Throwable;

use Psr\Log\LogLevel;
use Psr\Log\AbstractLogger;

use Wedeto\Util\Functions as WF;

class Logger extends AbstractLogger
{
    private static $module_loggers = array();

    private $module;
    private $level = LogLevel::DEBUG;
    private $handlers = array();

    private static $LEVEL_NAMES = array(
        LogLevel::DEBUG => array(0, 'DEBUG'),
        LogLevel::INFO => array(1, 'INFO'),
        LogLevel::NOTICE => array(2, 'NOTICE'),
        LogLevel::WARNING => array(3, 'WARNING'),
        LogLevel::ERROR => array(4, 'ERROR'),
        LogLevel::CRITICAL => array(5, 'CRITICAL'),
        LogLevel::ALERT => array(6, 'ALERT'),
        LogLevel::EMERGENCY => array(7, 'EMERGENCY')
    );

    public static function getLogger($module = "")
    {
        if (is_object($module))
            $module = get_class($module);
        $module = trim(str_replace('\\', '.', $module), ". \\");

        if (!isset(self::$module_loggers[$module]))
            self::$module_loggers[$module] = new Logger($module);

        return self::$module_loggers[$module];
    }

    private function __construct(string $module)
    {
        $this->module = $module;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function isRoot()
    {
        return empty($this->module);
    }

    public function getParentLogger()
    {
        if ($this->module === "")
            return null;

        $tree = explode(".", $this->module);
        array_pop($tree);
        $parent_module = implode(".", $tree);
        return self::getLogger($parent_module);
    }

    public function setLevel(string $lvl)
    {
        if (!isset(self::$LEVEL_NAMES[$lvl]))
            throw new \DomainException("Invalid log level: $lvl");

        $this->level = $lvl;
        return $this;
    }

    public function addLogHandler($handler)
    {
        if (!($handler instanceof LogWriterInterface) && !is_callable($handler))
            throw new \RuntimeException("Please provide a valid callback or object as LogHandler");

        $this->handlers[] = $handler;
        return $this;
    }

    public function getLogHandlers()
    {
        return $this->handlers;
    }

    public function removeLogHandlers()
    {
        $this->handlers = array();
        return $this;
    }

    public function log($level, $message, array $context = array())
    {
        if (!isset(self::$LEVEL_NAMES[$level]))
            throw new \Psr\Log\InvalidArgumentException("Invalid log level: $level");

        if (self::$LEVEL_NAMES[$level][0] < self::$LEVEL_NAMES[$this->level][0])
            return;

        if (!isset($context['_module']))
            $context['_module'] = $this->module;

        if (!isset($context['_level']))
            $context['_level'] = $level;

        foreach ($this->handlers as $handler)
        {
            if ($handler instanceof LogWriterInterface)
            {
                $handler->write($level, $message, $context);
            }
            else
            {
                call_user_func($handler, $level, $message, $context);
            }
        }

        $parent = $this->getParentLogger();
        if ($parent)
            $parent->log($level, $message, $context);
    }

    public static function fillPlaceholders(string $message, $context)
    {
        $message = (string)$message;
        foreach ($context as $key => $value)
        {
            $placeholder = '{' . $key . '}';
            $strval = null;
            while (($pos = strpos($message, $placeholder)) !== false)
            {
                $strval = $strval ?: WF::str($value);
                $message = substr($message, 0, $pos) . $strval . substr($message, $pos + strlen($placeholder));
            }
        }
        return $message;
    }

    public static function printIndent($buf, string $text, int $indent = 4)
    {
        $parts = explode("\n", $text);
        $indent = str_repeat(' ', $indent);
        foreach ($parts as $p)
            fprintf($buf, "%s%s\n", $indent, $p);
    }

    public static function logModule(string $level, $module, $message, array $context = array())
    {
        $log = self::getLogger($module);
        return $log->log($level, $message, $context);
    }

    public static function getLevelNumeric(string $level)
    {
        return isset(self::$LEVEL_NAMES[$level]) ? self::$LEVEL_NAMES[$level][0] : 0;
    }
}

function debug(string $module, string $message, array $context = array())
{
    Logger::logModule(LogLevel::DEBUG, $module, $message, $context);
}

function info(string $module, string $message, array $context = array())
{
    Logger::logModule(LogLevel::INFO, $module, $message, $context);
}

function notice(string $module, string $message, array $context = array())
{
    Logger::logModule(LogLevel::NOTICE, $module, $message, $context);
}

function warn(string $module, string $message, array $context = array())
{
    Logger::logModule(LogLevel::WARN, $module, $message, $context);
}

function warning(string $module, string $message, array $context = array())
{
    Logger::logModule(LogLevel::WARN, $module, $message, $context);
}

function error(string $module, string $message, array $context = array())
{
    Logger::logModule(LogLevel::ERROR, $module, $message, $context);
}

function critical(string $module, string $message, array $context = array())
{
    Logger::logModule(LogLevel::CRITICAL, $module, $message, $context);
}

function alert(string $module, string $message, array $context = array())
{
    Logger::logModule(LogLevel::ALERT, $module, $message, $context);
}

function emergency(string $module, string $message, array $context = array())
{
    Logger::logModule(LogLevel::EMERGENCY, $module, $message, $context);
}
