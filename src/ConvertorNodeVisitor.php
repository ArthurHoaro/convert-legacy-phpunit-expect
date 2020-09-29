<?php

declare(strict_types=1);

namespace ArthurHoaro\ConvertLegacyPHPUnitExpect;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;

class ConvertorNodeVisitor extends NodeVisitorAbstract
{
    /** @var Node\Stmt[] */
    protected array $statementsToAdd;

    protected bool $changeMade;

    protected bool $legacyRegexp;

    public function __construct(bool $legacyRegexp, bool &$changeMade)
    {
        $this->changeMade = &$changeMade;
        $this->legacyRegexp = $legacyRegexp;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof ClassMethod) {
            $this->statementsToAdd = [];

            $docComment = $node->getDocComment();
            if ($docComment !== null) {
                $commentLines = explode(PHP_EOL, $docComment->getText());
                foreach ($commentLines as $i => $commentLine) {
                    if ($this->processCommentLine($commentLine)) {
                        unset($commentLines[$i]);
                    }
                }
            }


            if (!empty($this->statementsToAdd)) {
                $this->changeMade = true;
                if (isset($commentLines)) {
                    $commentStr = $this->cleanUpComments($commentLines);
                    $node->setDocComment(new Doc($commentStr));
                }
                $node->stmts = array_merge($this->statementsToAdd, $node->stmts);
            }
        }
    }

    protected function processCommentLine(string $commentLine): bool
    {
        return $this->convertExpectException($commentLine)
            || $this->convertExpectExceptionMessage($commentLine)
            || $this->convertExpectExceptionMessageRegex($commentLine)
            || $this->convertExpectExceptionCode($commentLine);
    }

    protected function convertExpectException(string $commentLine): bool
    {
        if (!preg_match('/@expectedException\s+(.+)/', $commentLine, $matches)) {
            return false;
        }

        $className = '\\' . ltrim($matches[1], '\\');
        $this->statementsToAdd[] = new MethodCall(
            new Variable('this'),
            new Node\Identifier('expectException'),
            [new Node\Arg(new Node\Expr\ClassConstFetch(
                new Node\Name($className), new Node\Identifier('class')
            ))]
        );

        return true;
    }

    protected function convertExpectExceptionMessage(string $commentLine)
    {
        if (!preg_match('/@expectedExceptionMessage\s+(.+)/', $commentLine, $matches)) {
            return false;
        }

        $this->statementsToAdd[] = new MethodCall(
            new Variable('this'),
            new Node\Identifier('expectExceptionMessage'),
            [new Node\Arg(new Node\Scalar\String_($matches[1]))]
        );

        return true;
    }

    protected function convertExpectExceptionMessageRegex(string $commentLine)
    {
        if (!preg_match('/@expectedExceptionMessageRegExp\s+(.+)/', $commentLine, $matches)) {
            return false;
        }

        $method = $this->legacyRegexp
            ? 'expectExceptionMessageRegExp'
            : 'expectExceptionMessageMatches';

        $this->statementsToAdd[] = new MethodCall(
            new Variable('this'),
            new Node\Identifier($method),
            [new Node\Arg(new Node\Scalar\String_($matches[1]))]
        );

        return true;
    }

    protected function convertExpectExceptionCode(string $commentLine)
    {
        if (!preg_match('/@expectedExceptionCode\s+(\d+)/', $commentLine, $matches)) {
            return false;
        }

        $this->statementsToAdd[] = new MethodCall(
            new Variable('this'),
            new Node\Identifier('expectExceptionCode'),
            [new Node\Arg(new Node\Scalar\LNumber((int) $matches[1]))]
        );

        return true;
    }

    protected function cleanUpComments(array $commentLines): string
    {
        $commentLines = array_values($commentLines);
        for ($i = count($commentLines) - 1; $i >= 0; --$i) {
            if (trim($commentLines[$i]) === '*') {
                unset($commentLines[$i]);
            } elseif ($i !== count($commentLines) - 1) {
                if (trim($commentLines[$i]) === '/**') {
                    return '';
                }
                break;
            }
        }

        return implode(PHP_EOL, $commentLines);
    }
}
