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
use Wedeto\Log\Writer\WriterInterface;

/** 
 * Logger implementing the PSR-3 Logger Interface standard.
 * Wedeto\Log is a modular, hierarchical logger. It's inspired
 * by logging systems available on Java (Logger4J, SLF4J, ...),
 * and the native Python logger.
 *
 * Each class has its own logger, based on their class name and namespace.
 * Each logger can have a different log level, and log messages bubble up to
 * the root logger. You can attach log writers to each level to filter the
 * messages.
 */
class Logger extends AbstractLogger
{
    /** MODE_ACCEPT_MOST_SPECIFIC: the most specific logger can accept the message */
    const MODE_ACCEPT_MOST_SPECIFIC = 1;

    /** MODE_ACCEPT_MOST_GENERIC: the most generic logger can accept the message */
    const MODE_ACCEPT_MOST_GENERIC = 2;

    /** The registered loggers */
    private static $module_loggers = array();

    /** The acceptance mode */
    private static $accept_mode = Logger::MODE_ACCEPT_MOST_SPECIFIC;

    /** A mapping between LogLevel constants and their order of severity */
    private static $LEVEL_NUMERIC = array(
        LogLevel::DEBUG     => 0,
        "DEBUG"             => 0,
        LogLevel::INFO      => 1,
        "INFO"              => 1,
        LogLevel::NOTICE    => 2,
        "NOTICE"            => 2,
        LogLevel::WARNING   => 3,
        "WARNING"           => 3,
        LogLevel::ERROR     => 4,
        "ERROR"             => 4,
        LogLevel::CRITICAL  => 5,
        "CRITICAL"          => 5,
        LogLevel::ALERT     => 6,
        "ALERT"             => 6,
        LogLevel::EMERGENCY => 7,
        "EMERGENCY"         => 7
    );

    /** The module of the current instance */
    private $module;

    /** The log level of the current instance */
    private $level = null;

    /** The numeric log level of the current instance */
    private $level_num = 0;

    /** The writers attached to this instance */
    private $writers = array();

    /**
     * Get a logger for a specific module.
     * @param mixed $module A string indicating the module,
     *                      or a class name or object that will be
     *                      used to find the appropriate name.
     * @return Logger The instance for the specified module
     */
    public static function getLogger($module = "")
    {
        if (empty($module) || strtoupper($module) === "ROOT")
            $module = "";
        elseif (is_object($module))
            $module = get_class($module);
        $module = trim(str_replace('\\', '.', $module), ". \\");

        if (!isset(self::$module_loggers[$module]))
            self::$module_loggers[$module] = new Logger($module);

        return self::$module_loggers[$module];
    }

    /**
     * This method will reset all global state in the Logger object.
     * It will remove all writers from all loggers and then remove all
     * loggers. Note that this will not remove existing logger instances
     * from other objects - this is why the writers are removed.
     */
    public static function resetGlobalState()
    {
        foreach (self::$module_loggers as $logger)
            $logger->removeLogWriters();
        self::$module_loggers = [];
        self::$accept_mode = self::MODE_ACCEPT_MOST_SPECIFIC;
    }

    /** 
     * Set the accept mode of the logger structure. This will determine how messages are
     * accepted and bubbled. There are two modes:
     *y
     * Logger::MODE_ACCEPT_MOST_SPECIFIC - This will honour the decision made by the most specific
     *                                     logger for a message. If a logger accepts the message because
     *                                     its level is equal to or higher than the loggers level,
     *                                     parents will also accept and bubble up the message.
     * Logger::MODE_ACCEPT_MOST_GENERIC  - This will leave the decision to the most specific logger
     *                                     that has a verdict. If any logger decides to reject it,
     *                                     none of its ancestors will receive the message.
     *
     * In both cases, whether the message is actually written depends on the log level of the writer.
     */
    public static function setAcceptMode(int $mode)
    {
        if ($mode !== self::MODE_ACCEPT_MOST_SPECIFIC && $mode !== self::MODE_ACCEPT_MOST_GENERIC)
            throw new \InvalidArgumentException("Invalid accept mode: " . $mode);

        self::$accept_mode = $mode;
    }

    /**
     * @return int the current accept mode of the logger:
     * Logger::MODE_ACCEPT_MOST_SPECIFIC or Logger::MODE_ACCEPT_MOST_GENERIC
     */
    public static function getAcceptMode()
    {
        return self::$accept_mode;
    }

    /**
     * Create the module. Private, because it should only be called
     * using getLogger which makes sure each module gets exactly one logger.
     *
     * @param string $module The name of the module
     */
    private function __construct(string $module)
    {
        $this->module = $module;
    }

    /**
     * @return string The module of this logger
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return bool True if this is the root logger, false if not
     */
    public function isRoot()
    {
        return empty($this->module);
    }

    /**
     * @return Logger The logger of the parent module. Null for the root
     * logger.
     */
    public function getParentLogger()
    {
        if ($this->module === "")
            return null;

        $tree = explode(".", $this->module);
        array_pop($tree);
        $parent_module = implode(".", $tree);
        return self::getLogger($parent_module);
    }

    /**
     * Set the log level for this module. Any log messages with a severity lower
     * than this threshold will not bubble up.
     * @param string $level The minimum log level of messages to handle
     */
    public function setLevel(string $level)
    {
        if (!defined(LogLevel::class . '::' . strtoupper($level)))
            throw new \DomainException("Invalid log level: $level");

        $this->level = $level;
        $this->level_num = self::$LEVEL_NUMERIC[$level];
        return $this;
    }
    
    /**
     * @return string the level this logger is set to
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return bool True if the level would be accepted somewhere, false if it
     *              would be dropped
     */
    public function isLevelEnabled(string $level, int $level_num = null)
    {
        if ($level_num === null)
            $level_num = self::$LEVEL_NUMERIC[$level];

        if (
            self::$accept_mode === self::MODE_ACCEPT_MOST_GENERIC && 
            $this->level !== null && $level_num < $this->level_num
        )
        {
            // This logger rejects it, so it will not be handlded anyway in GENERIC mode
            return false;
        }

        if ($this->level !== null && $level_num >= $this->level_num)
        {
            // This logger would not reject the message, check if any writer
            // would accept it.
            foreach ($this->writers as $writer)
                if ($writer->isLevelEnabled($level, $level_num))
                    return true;
        }

        // No writer accepts it directly, delegate to parents
        $parent = $this->getParentLogger();
        if ($parent !== null)
            return $parent->isLevelEnabled($level, $level_num);

        // Negative
        return false;
    }

    /**
     * Add a log handler to this module. The log handler will receive all messages
     * passing through this logger.
     *
     * @param WriterInterface The log writer
     * @return Logger Provides fluent interface
     */
    public function addLogWriter(WriterInterface $writer)
    {
        $this->writers[] = $writer;
        return $this;
    }

    /**
     * @return array The list of log writers attached to this instance
     */
    public function getLogWriters()
    {
        return $this->writers;
    }

    /**
     * Removes all log writers attached to this instance
     * @return Logger Provides fluent interface
     */
    public function removeLogWriters()
    {
        $this->writers = array();
        return $this;
    }

    /**
     * The log() call logs a message for this module.
     *
     * @param string $level The LogLevel for this message
     * @param string $mesage The message to log
     * @param array $context The context - can be used to format the message
     */
    public function log($level, $message, array $context = array())
    {
        if (!defined(LogLevel::class . '::' . strtoupper($level)))
            throw new \Psr\Log\InvalidArgumentException("Invalid log level: $level");

        if ($this->level_num === null && $this->level !== null)
            $this->level_num = self::LEVEL_NUMERIC[$this->level];

        $level_num = self::$LEVEL_NUMERIC[$level];

        if (
            $this->level !== null &&
            $level_num < $this->level_num
        )
        {
            // This logger is configured to reject this message. In mode GENERIC,
            // the message will be dropped. In mode SPECIFIC, it depends on wether
            // a more specific logger has already accepted the message.
            if (self::$accept_mode === self::MODE_ACCEPT_MOST_GENERIC || !isset($context['_accept']))
                return;
        }

        if ($this->level !== null && $level_num >= $this->level_num && !isset($context['_accept']))
        {
            // This logger is configured to accept this message. Store the logger that made
            // this decision to inform parent loggers that it has been accepted. In accept mode
            // SPECIFIC, this will instruct parents not to reject it.
            $context['_accept'] = $this->module;
        }

        if (!isset($context['_module']))
            $context['_module'] = $this->module;

        if (!isset($context['_level']))
            $context['_level'] = $level;

        foreach ($this->writers as $writer)
            $writer->write($level, $message, $context);

        // Bubble up to parent loggers
        $parent = $this->getParentLogger();
        if ($parent)
            $parent->log($level, $message, $context);
    }

    /**
     * Fill the place holders in the message with values from the context array
     * @param string $message The message to format. Any {var} placeholders will be replaced
     *                        with a value from the context array.
     * @param array $context Contains the values to replace the placeholders with.
     * @return string The message with placeholders replaced
     */
    public static function fillPlaceholders(string $message, array $context)
    {
        $message = (string)$message;
        foreach ($context as $key => $value)
        {
            $placeholder = '{' . $key . '}';
            $strval = null;
            $pos = 0;
            while (($pos = strpos($message, $placeholder, $pos)) !== false)
            {
                $strval = $strval ?: WF::str($value);
                $message = 
                    substr($message, 0, $pos) 
                    . $strval 
                    . substr($message, $pos + strlen($placeholder));
                $pos = $pos + strlen($strval);
            }
        }
        return $message;
    }

    /**
     * Get the severity number for a specific LogLevel.
     * @param string $level The LogLevel to convert to a number
     * @return int The severity - 0 is less important, 7 is most important.
     */
    public static function getLevelNumeric(string $level)
    {
        return isset(self::$LEVEL_NUMERIC[$level]) ? self::$LEVEL_NUMERIC[$level] : 0;
    }
}
