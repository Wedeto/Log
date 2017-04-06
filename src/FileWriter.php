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
use Wedeto\Util\Hook;
use Wedeto\Util\RecursionException;

/** 
 * Write log entry to a file
 */
class FileWriter extends AbstractWriter
{
    private $filename;
    private $file = null;
    private $reopen_interval = 30;

    /**
     * Create the file writer
     */
    public function __construct($filename, $min_level = LogLevel::DEBUG)
    {
        $this->filename = $filename;
        $this->setLevel($min_level);
        $this->setFormatter(new PatternFormatter("[%DATE%][%MODULE%] %LEVEL%: %MESSAGE%"));
    }

    /**
     * Set the file reopen interval. The file will be kept open for a maximum
     * of N seconds. When serving requests, this will not be reached, but
     * in long running CLI tasks, this may be necessary to cope with log rotation.
     *
     * @param int $seconds The amount of seconds before file reopening
     */
    public function setReopenInterval(int $seconds)
    {
        $this->reopen_interval = $seconds;
        return $this;
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
     * Write a line to the log file
     * @param string $str The line to write
     */
    private function writeLine(string $str)
    {
        $now = time();
        if ($this->file && $this->file_opened < $now - $this->reopen_interval)
        {
            // Close the file after a set interval
            fclose($this->file);
            $this->file = null;
        }

        if (!$this->file)
        {
            touch($this->filename);
            try
            {
                Hook::execute("Wedeto.IO.FileCreated", ['filename' => $this->filename]);
            }
            // @codeCoverageIgnoreStart
            // Ignore recursion: if and error occurs in a hook it may end up
            // calling the file writer again, resulting in a loop.
            catch (RecursionException $e)
            {} 
            // @codeCoverageIgnoreEnd


            $this->file = fopen($this->filename, 'a');
            $this->file_opened = time();
        }

        if ($this->file)
            fwrite($this->file, $str . "\n");
    }
}
