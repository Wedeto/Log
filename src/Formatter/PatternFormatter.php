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

namespace Wedeto\Log\Formatter;

use Wedeto\Log\Logger;

class PatternFormatter implements FormatterInterface
{
    protected $format;
    protected $date_format;

    /**
     * Create the formatter - $format indicates the format. You use placeholders to 
     * fill in the variables:
     * %MODULE% The module where the error occured
     * %LEVEL% The level of the message
     * %DATE% The current date
     * %MESSAGE% The log message
     *
     * @param string $format The format used to create the final log entry
     * @param string $dateformat Used to format the date in the log entry
     */
    public function __construct(string $format, string $dateformat = \DateTime::ATOM)
    {
        $this->format = $format;
        $this->date_format = $dateformat;
    }

    /**
     * Format the message
     * 
     * @param string $level The LogLevel
     * @param string $message The message to log
     * @param array $context Additional data
     * @return string The formatted log message
     */
    public function format(string $level, string $message, array $context)
    {
        $search = [
            '%MODULE%' => $context['_module'] ?? "",
            '%LEVEL%' => strtoupper($level),
            '%MESSAGE%' => Logger::fillPlaceholders($message, $context),
            '%DATE%' => date($this->date_format)
        ];

        return str_replace(array_keys($search), array_values($search), $this->format);
    }
}
