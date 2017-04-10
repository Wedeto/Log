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
use Psr\Log\LoggerInterface;

/** 
 * Pass on log messages to another PSR-3 compatible logger
 * This class will not do any filtering, also not on log level - 
 * all messages are passed on as-is to the configured logger.
 */
class ExternalLogWriter extends AbstractWriter
{
    private $logger;

    /**
     * Create the logger
     * @param Psr\Log\LoggerInterface $logger The logger to delegate to
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Psr\Log\LoggerInterface The logger being delegated to
     */
    public function getLogger()
    {
        return $this->logger;
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
        $this->logger->log($level, $message, $context);
    }
}
