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

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @covers Wedeto\Log\Writer\FileWriter
 * @covers Wedeto\Log\Writer\AbstractWriter
 */
final class FileWriterTest extends TestCase
{
    private $dir;

    public function setUp()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('logdir'));
        $this->dir = vfsStream::url('logdir');
    }

    public function testFileWriter()
    {
        $fw = new FileWriter($this->dir . '/test.log', LogLevel::DEBUG);
        $fw->setReopenInterval(-1);
        
        $fw->write(LogLevel::INFO, "Foo", ['_module' => 'FWTest']);
        $log = file_get_contents($this->dir . '/test.log');
        $this->assertContains('[FWTest] INFO: Foo', $log);

        $fw->write(LogLevel::INFO, "Bar", ['_module' => 'FWTest2']);
        $log = file_get_contents($this->dir . '/test.log');
        $this->assertContains('[FWTest] INFO: Foo', $log);
        $this->assertContains('[FWTest2] INFO: Bar', $log);

        $fw->setLevel(LogLevel::ERROR);
        $fw->write(LogLevel::INFO, "Baz", ['_module' => 'FWTest2']);
        $log = file_get_contents($this->dir . '/test.log');
        $this->assertContains('[FWTest] INFO: Foo', $log);
        $this->assertContains('[FWTest2] INFO: Bar', $log);
        $this->assertNotContains('[FWTest2] INFO: Baz', $log);
    }
}
