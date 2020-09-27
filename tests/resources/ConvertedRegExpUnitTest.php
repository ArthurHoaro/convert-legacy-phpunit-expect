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

    public function testSomeCoreMethod(): void
    {
        $this->expectException(\ArthurHoaro\ConvertLegacyPHPUnitExpect\ConverterException::class);
        $this->expectExceptionCode(123);
        $this->expectExceptionMessage('This is unacceptable');
        $this->expectExceptionMessageRegExp('/You shouldn\'t mix message and regexp/');

        $this->assertTrue(true);
    }

    /**
     * Unrelated doc bloc
     */
    public function testExistingDocBlocIsPreserved(): void
    {
        $this->expectException(\ArthurHoaro\ConvertLegacyPHPUnitExpect\ConverterException::class);

        $this->assertTrue(true);
    }

    /**
     * Unrelated doc bloc
     *
     *
     * @see http://github.com
     */
    public function testMoreDoc(): void
    {
        $this->expectException(\ArthurHoaro\ConvertLegacyPHPUnitExpect\ConverterException::class);

        $this->assertTrue(true);
    }

    public function testThisIsAMess(): void
    {
        $this->expectException(\ArthurHoaro\ConvertLegacyPHPUnitExpect\ConverterException::class);
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
