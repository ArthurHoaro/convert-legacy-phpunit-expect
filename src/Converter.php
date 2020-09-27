<?php

declare(strict_types=1);

namespace ArthurHoaro\ConvertLegacyPHPUnitExpect;

use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;

class Converter
{
    protected Parser $parser;

    protected Lexer $lexer;

    protected bool $dryRun;

    protected bool $legacyRegexp;

    public function __construct(Parser $parser, Lexer $lexer, bool $dryRun, bool $legacyRegexp)
    {
        $this->parser = $parser;
        $this->lexer = $lexer;
        $this->dryRun = $dryRun;
        $this->legacyRegexp = $legacyRegexp;
    }

    public function convert(string $filepath): bool
    {
        $changeMade = false;

        $this->checkPermissions($filepath);
        $content = $this->parserTraverse($filepath, $changeMade);

        if ($changeMade) {
            $content = $this->addBlankLines($content);

            if (!$this->dryRun) {
                file_put_contents($filepath, $content);
            }
        }

        return $changeMade;
    }

    protected function checkPermissions(string $filepath): void
    {
        if (!is_file($filepath) || !is_writable($filepath)) {
            throw new ConverterException('I do not have the permission to read or write into ' . $filepath);
        }
    }

    protected function parserTraverse(string $filepath, bool &$changeMade): string
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloningVisitor());

        try {
            $existingStatements = $this->parser->parse(file_get_contents($filepath));
            $existingTokens = $this->lexer->getTokens();
        } catch (Error $e) {
            throw new ConverterException('Unable to parse file: ' . $filepath, 0, $e);
        }

        $updatedStatements = $traverser->traverse($existingStatements);

        // Reset - required otherwise it'll throw an exception
        $traverser = new NodeTraverser();

        try {
            // Run our custom node visitor which will create the new expect statements
            $traverser->addVisitor(new ConvertorNodeVisitor($this->legacyRegexp, $changeMade));
            $updatedStatements = $traverser->traverse($updatedStatements);
        } catch (Error $e) {
            throw new ConverterException('An error occurred while replacing expect statements: ' . $filepath, 0, $e);
        }

        $printer = new Standard();

        return $printer->printFormatPreserving($updatedStatements, $existingStatements, $existingTokens);
    }

    /** Adds an empty line after the new $this->expect statements bloc */
    protected function addBlankLines(string $content): string
    {
        $fileContent = explode(PHP_EOL, $content);
        $out = [];
        $currentLineIsNew = false;
        foreach ($fileContent as $index => $line) {
            $previousLineWasNew = $currentLineIsNew;
            $currentLineIsNew = preg_match('/\s+\$this->expect/', $line) === 1;

            // Entirely removed doc blocks leave an empty line with indentation
            if ($line === '    ') {
                continue;
            }

            // For some reason the printer does not add the ";" after new statements
            if ($currentLineIsNew && substr($line, -1) !== ';') {
                $line .= ';';
            }

            if (!$currentLineIsNew && $previousLineWasNew && !empty(trim($line)) && trim($line) !== '}') {
                $out[] = '';
            }

            $out[] = $line;
            unset($fileContent[$index]);
        }

        return implode(PHP_EOL, $out);
    }
}
