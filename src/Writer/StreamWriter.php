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

use Psr\Log\LogLevel;
use Wedeto\Util\Hook;
use Wedeto\Util\RecursionException;

use Wedeto\Log\Logger;
use Wedeto\Log\Formatter\PatternFormatter;

/** 
 * Write log entry to a stream
 */
class StreamWriter extends AbstractWriter
{
    private $stream;

    /**
     * Create the file writer
     */
    public function __construct($stream, $min_level = LogLevel::DEBUG)
    {
        if ($stream === "STDOUT")
            $stream = PHP_SAPI === "cli" ? STDOUT : fopen('php://output', 'w');
        elseif ($stream === "STDERR")
            $stream = PHP_SAPI === "cli" ? STDERR : fopen('php://output', 'w');

        if (!is_resource($stream))
            throw new \InvalidArgumentException("Provide a stream to write to");

        $this->stream = $stream;
        $this->setLevel($min_level);
        $this->setFormatter(new PatternFormatter("[%DATE%][%MODULE%] %LEVEL%: %MESSAGE%"));
    }

    /**
     * Write a log message.
     *
     * @param string $level The LogLevel
     * @param string $message The message to write
     * @param array $context The context variables
     */
    public function write(string $level, string $message, array $context)
    {
        $lvl_num = Logger::getLevelNumeric($level);
        if ($lvl_num < $this->min_level)
            return;

        $fmt = $this->format($level, $message, $context);
        $this->writeLine($fmt);
    }

    /**
     * Write a line to the log stream
     *
     * @param string $str The line to write
     */
    private function writeLine(string $str)
    {
        fwrite($this->stream, $str . "\n");
    }
}
