<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use PhpParser\Node\Expr;
use PhpParser\NodeAbstract;
use PhpParser\PrettyPrinter\Standard as BasePrettyPrinter;

/**
 * Enhances the phpDocumentor PrettyPrinter with short array syntax
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
    protected function pSingleQuotedString(string $string) {
        return '\'' . preg_replace("/'|\\\\(?=[\\\\']|$)/", '\\\\$0', $string) . '\'';
    }

    /**
     * @param NodeAbstract[] $nodes
     * @param bool $trailingComma
     * @return string
     */
    protected function pMaybeMultiline(array $nodes, bool $trailingComma = false)
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
