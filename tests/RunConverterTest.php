<?php

declare(strict_types=1);

namespace ArthurHoaro\ConvertLegacyPHPUnitExpect;

use PHPUnit\Framework\TestCase;

class RunConverterTest extends TestCase
{
    protected function setUp(): void
    {
        $this->tearDown();
        mkdir(__DIR__ . '/workdir');
        copy(__DIR__ . '/resources/LegacyUnitTest.php', __DIR__ . '/workdir/LegacyUnitTest.php');
    }

    protected function tearDown(): void
    {
        @unlink(__DIR__ . '/workdir/LegacyUnitTest.php');
        @rmdir(__DIR__ . '/workdir');
    }

    public function testRunConverter(): void
    {
        static::assertFileExists(__DIR__ . '/workdir/LegacyUnitTest.php');

        shell_exec('php run.php --path="tests/workdir/"');

        static::assertFileEquals(
            __DIR__ . '/resources/ConvertedUnitTest.php',
            __DIR__ . '/workdir/LegacyUnitTest.php',
        );
    }

    public function testRunConverterInDryMode(): void
    {
        static::assertFileExists(__DIR__ . '/workdir/LegacyUnitTest.php');

        shell_exec('php run.php --path="tests/workdir/" --dry-mode');

        static::assertFileEquals(
            __DIR__ . '/resources/LegacyUnitTest.php',
            __DIR__ . '/workdir/LegacyUnitTest.php',
        );
    }

    public function testRunConverterWithLegacyRegexp(): void
    {
        static::assertFileExists(__DIR__ . '/workdir/LegacyUnitTest.php');

        shell_exec('php run.php --path="tests/workdir/" --legacy-regexp');

        static::assertFileEquals(
            __DIR__ . '/resources/ConvertedRegExpUnitTest.php',
            __DIR__ . '/workdir/LegacyUnitTest.php',
        );
    }
}
