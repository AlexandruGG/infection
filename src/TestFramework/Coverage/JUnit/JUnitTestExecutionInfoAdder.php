<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\TestFramework\Coverage\JUnit;

use function explode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\TestFramework\Coverage\ProxyTrace;

/**
 * Adds test execution info to selected covered file data object.
 *
 * @internal
 * @final
 */
class JUnitTestExecutionInfoAdder
{
    private $testFileDataProvider;

    private $adapter;

    public function __construct(
        TestFrameworkAdapter $adapter,
        TestFileDataProvider $testFileDataProvider
    ) {
        $this->adapter = $adapter;

        $this->testFileDataProvider = $testFileDataProvider;
    }

    /**
     * @param iterable<ProxyTrace> $traces
     *
     * @return iterable<ProxyTrace>
     */
    public function addTestExecutionInfo(iterable $traces): iterable
    {
        if (!$this->adapter->hasJUnitReport()) {
            return $traces;
        }

        return $this->testExecutionInfoAdder($traces);
    }

    /**
     * @param iterable<ProxyTrace> $traces
     *
     * @return iterable<ProxyTrace>
     */
    private function testExecutionInfoAdder(iterable $traces): iterable
    {
        foreach ($traces as $trace) {
            foreach ($trace->retrieveTestLocations()->byLine as &$testsLocations) {
                foreach ($testsLocations as $line => $test) {
                    $testsLocations[$line] = self::createCompleteTestLocation($test);
                }
            }

            yield $trace;
        }
    }

    private function createCompleteTestLocation(TestLocation $test): TestLocation
    {
        $class = explode(':', $test->getMethod(), 2)[0];

        $testFileData = $this->testFileDataProvider->getTestFileInfo($class);

        return new TestLocation(
            $test->getMethod(),
            $testFileData->path,
            $testFileData->time
        );
    }
}