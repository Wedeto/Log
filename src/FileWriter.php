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

class FileWriter implements LogWriterInterface
{
    private $filename;
    private $min_level;
    private $file = null;

    public function __construct($filename, $min_level = LogLevel::DEBUG)
    {
        $this->filename = $filename;
        $this->min_level = Logger::getLevelNumeric($min_level);
    }

    public function write(string $level, $message, array $context)
    {
        $lvl_num = Logger::getLevelNumeric($level);
        if ($lvl_num < $this->min_level)
            return;

        $message = Logger::fillPlaceholders($message, $context);
        $module = isset($context['_module']) ? $context['_module'] : "";
        $fmt = "[" . date('Y-m-d H:i:s') . '][' . $module . ']';
        $fmt .= ' ' . strtoupper($level) . ': ' . $message;
        $this->writeLine($fmt);
    }

    private function writeLine(string $str)
    {
        $new_file = false;
        if (!$this->file)
        {
            touch($this->filename);
            try
            {
                Hook::execute("Wedeto.IO.FileCreated", ['filename' => $this->filename]);
            }
            catch (RecursionException $e)
            {} 
            // Ignore recursion: if and error occurs in a hook it may end up
            // calling the file writer again, resulting in a loop.

            $this->file = fopen($this->filename, 'a');
        }

        if ($this->file)
            fwrite($this->file, $str . "\n");
    }
}
