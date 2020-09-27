<?php

declare(strict_types=1);

namespace ArthurHoaro\UnitTest\RandomNamespace;

use PHPUnit\Framework\TestCase;

class LegacyUnitTest extends TestCase
{
    /**
     * Ignore me
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @expectedException \ArthurHoaro\ConvertLegacyPHPUnitExpect\ConverterException
     * @expectedExceptionCode 123
     * @expectedExceptionMessage This is unacceptable
     * @expectedExceptionMessageRegExp /You shouldn't mix message and regexp/
     */
    public function testSomeCoreMethod(): void
    {
        $this->assertTrue(true);
    }

    /**
     * Unrelated doc bloc
     *
     * @expectedException \ArthurHoaro\ConvertLegacyPHPUnitExpect\ConverterException
     */
    public function testExistingDocBlocIsPreserved(): void
    {
        $this->assertTrue(true);
    }

    /**
     * Unrelated doc bloc
     *
     * @expectedException \ArthurHoaro\ConvertLegacyPHPUnitExpect\ConverterException
     *
     * @see http://github.com
     */
    public function testMoreDoc(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @expectedException \ArthurHoaro\ConvertLegacyPHPUnitExpect\ConverterException
     */
    public function testThisIsAMess(): void
    {
        $this->expectExceptionMessage('Hi!');

        $this->assertTrue(true);
    }

    public function testLeaveMeAlone(): void
    {
        $this->expectException(\ArthurHoaro\ConvertLegacyPHPUnitExpect\ConverterException::class);
        $this->expectExceptionMessage('Hi!');
        $this->expectExceptionMessageMatches('/Another one bites the dust/');
        $this->expectExceptionCode(456);
    }
}
