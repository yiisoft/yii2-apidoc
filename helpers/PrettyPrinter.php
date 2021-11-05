<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use PhpParser\Node\Expr;
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
     * @param Expr\Array_ $node
     * @return string
     */
    public function pExpr_Array(Expr\Array_ $node)
    {
        return '[' . $this->pCommaSeparated($node->items) . ']';
    }

    /**
     * Returns a simple human readable output for a value.
     *
     * @deprecated It's now handled in phpDocumentor library.
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
