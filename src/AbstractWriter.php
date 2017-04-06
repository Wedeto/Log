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

use Psr\Log\LogLevel;

/**
 * Implement the basic facilities for a writer
 */
abstract class AbstractWriter implements WriterInterface 
{
    /** The formatter in use */
    protected $formatter = null;

    /** The minimum log level */
    protected $min_level = 0;

    /**
     * Set the formatter used to format a message
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * Set the minimum level
     * @param string $level The minimum level for messages to be written
     * @return AbstractWriter Provides fluent interface
     */
    public function setLevel(string $level)
    {
        $this->min_level = Logger::getLevelNumeric($level); 
        return $this;
    }

    /**
     * Return if a message of a specific level would be accepted
     * @param string $level The LogLevel
     * @param int $level_num The level as int, with 0 being DEBUG and 8 being EMERGENCY. When omitted,
     *                       it is obtained from Logger.
     * @return bool True if a message with this level would be accepted, false it it would be dropped.
     */
    public function isLevelEnabled(string $level, int $level_num = null)
    {
        if ($level_num === null)
            $level_num = Logger::getLevelNumeric($level);
        return $level_num >= $this->min_level;
    }

    /**
     * Format the message
     * @param string $level The LogLevel of the message
     * @param string $messsage The message to be formatted
     * @param array $context The values for the placeholders
     * @return string The formatted message.
     */
    public function format(string $level, string $message, array $context)
    {
        if ($this->formatter !== null)
            return $this->formatter->format($level, $message, $context);

        // Fall back to the logger placeholder filler
        return Logger::fillPlaceholders($message, $context);
    }
}
