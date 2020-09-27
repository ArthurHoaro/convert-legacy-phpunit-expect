#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use ArthurHoaro\ConvertLegacyPHPUnitExpect\Converter;
use ArthurHoaro\ConvertLegacyPHPUnitExpect\ConverterException;
use Garden\Cli\Cli;
use PhpParser\Lexer\Emulative;
use PhpParser\ParserFactory;

$cli = new Cli();

$cli->description('Convert legacy PHPUnit @expectedException to $this->expectException and associated messages.')
    ->opt('path:p', 'PHPUnit tests folder (absolute or relative to this script.', true)
    ->opt('dry-run', 'Display changes without altering path files.')
    ->opt(
        'legacy-regexp',
        'Convert @expectedExceptionMessageRegExp to expectExceptionMessageRegExp instead of expectExceptionMessageMatches.'
    )
;

$args = $cli->parse($argv, true);

$testsPath = $args->getOpt('path');
if (!is_dir($testsPath)) {
    echo $cli->red('Unable to find provided tests directory: ' . $testsPath) . PHP_EOL;
    exit(1);
}

$legacyRegexp = $args->getOpt('legacy-regexp') !== null;
$dryRun = $args->getOpt('dry-run') !== null;

$lexer = new Emulative([
    'usedAttributes' => [
        'comments',
        'startLine', 'endLine',
        'startTokenPos', 'endTokenPos',
    ],
]);
$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);
$converter = new Converter($parser, $lexer, $dryRun, $legacyRegexp);

$total = $converted = 0;

$iterator = new RecursiveDirectoryIterator($testsPath);
/** @var SplFileInfo $file */
foreach (new RecursiveIteratorIterator($iterator) as $file) {
    if ($file->getExtension() === 'php' && substr($file->getBasename('.php'), -4) === 'Test') {
        try {
            if ($converter->convert($file->getRealPath())) {
                echo $cli->green('[✓] ' . $file) . PHP_EOL;
                ++$converted;
            } else {
                echo $cli->blue('[ ] ' . $file) . PHP_EOL;
            }
            ++$total;
        } catch (ConverterException $e) {
            echo $cli->red($e->getMessage()) . PHP_EOL;
            echo $cli->purple($e->getTraceAsString()) . PHP_EOL;
            exit(2);
        }
    }
}

echo $cli->bold('------------------------------------------------') . PHP_EOL;
echo $cli->green('Finished!') . PHP_EOL . PHP_EOL;

if ($dryRun) {
    echo $cli->purple('Dry Run mode - no change were actually apply.') . PHP_EOL . PHP_EOL;
}

echo $cli->green('Files processed: ' . $total) . PHP_EOL;
echo $cli->green('Files converted: ' . $converted) . PHP_EOL;
