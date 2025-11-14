<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use PhpParser\Node\Expr;
use PhpParser\NodeAbstract;
use PhpParser\PrettyPrinter\Standard as BasePrettyPrinter;

/**
 * Enhances the phpDocumentor PrettyPrinter:
 *
 * - Fix for single slash becoming double in values of properties and class constants.
 * - All comments in values are removed because inline comments are shifted to the next line (can be confusing).
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class PrettyPrinter extends BasePrettyPrinter
{
    /**
     * @link https://github.com/nikic/PHP-Parser/issues/447#issuecomment-348557940
     * @param string $string
     * @return string
     */
    protected function pSingleQuotedString(string $string): string
    {
        return '\'' . preg_replace("/'|\\\\(?=[\\\\']|$)/", '\\\\$0', $string) . '\'';
    }

    /**
     * @param NodeAbstract[] $nodes
     * @param bool $trailingComma
     * @return string
     */
    protected function pMaybeMultiline(array $nodes, bool $trailingComma = false): string
    {
        foreach ($nodes as $node) {
            $node->setAttribute('comments', []);
        }

        if (!$nodes) {
            return $this->pCommaSeparated($nodes);
        } else {
            return $this->pCommaSeparatedMultiline($nodes, $trailingComma) . $this->nl;
        }
    }

    /**
     * Returns a simple human readable output for a value.
     * @param Expr $value The value node as provided by PHP-Parser.
     * @return string
     * @deprecated Pretty print is handled in "phpdocumentor/reflection" library. This custom pretty printer is now
     * injected through strategies and not directly called within "apidoc" extension.
     */
    public static function getRepresentationOfValue(Expr $value)
    {
        if ($value === null) {
            return '';
        }

        $printer = new static();

        return $printer->prettyPrintExpr($value);
    }
}
