<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

/**
 * TODO: description
 *
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 * @since 4.0
 */
class TypeHelper
{
    // TODO: refactor + tests + description
    public static function isConditionalType(string $type): bool
    {
        $typeString = trim(trim($type), '()');
        $balance = 0;
        $length = strlen($typeString);

        for ($i = 0; $i < $length; $i++) {
            $char = $typeString[$i];

            if ($char === '<' || $char === '(') {
                $balance++;
            } elseif ($char === '>' || $char === ')') {
                $balance--;
            } elseif ($balance === 0 && $char === '?') {
                $balanceInner = 0;
                for ($j = $i + 1; $j < $length; $j++) {
                    $charInner = $typeString[$j];

                    if ($charInner === '<' || $charInner === '(') {
                        $balanceInner++;
                    } elseif ($charInner === '>' || $charInner === ')') {
                        $balanceInner--;
                    } elseif ($balanceInner === 0 && $charInner === ':') {
                        return true;
                    }
                }

                return false;
            }
        }

        return false;
    }

    // TODO: refactor + tests + description
    /**
     * @return string[]
     */
    public static function extractGenericTypes(string $type): array
    {
        $typeString = trim($type);
        $length = strlen($typeString);
        $params = [];
        $balance = 0;
        $startPos = -1;

        $startBracket = strpos($typeString, '<');
        if ($startBracket === false) {
            return [];
        }

        for ($i = $startBracket + 1; $i < $length; $i++) {
            $char = $typeString[$i];

            if ($char === '<') {
                $balance++;
            } elseif ($char === '>') {
                $balance--;

                if ($balance < 0) {
                    if ($startPos !== -1) {
                        $param = substr($typeString, $startPos, $i - $startPos);
                        $params[] = trim($param);
                    }
                    break;
                }
            } elseif ($char === ',' && $balance === 0) {
                if ($startPos !== -1) {
                    $param = substr($typeString, $startPos, $i - $startPos);
                    $params[] = trim($param);
                }
                $startPos = -1;
                continue;
            }

            if ($startPos === -1) {
                if (trim($char) !== '') {
                    $startPos = $i;
                }
            }
        }

        return $params;
    }

    // TODO: refactor + tests + description
    /**
     * @return string[]
     */
    public static function getPossibleTypesFromConditionType(string $type): array
    {
        $possibleTypes = [];

        $processBranch = function (string $branch) use (&$possibleTypes, &$processBranch) {
            $branch = trim($branch);
            $length = strlen($branch);

            if ($length > 1 && $branch[0] === '(' && $branch[$length - 1] === ')') {
                $branch = trim(substr($branch, 1, -1));
            }

            if (self::isConditionalType($branch)) {
                $balance = 0;
                $questionMarkPos = -1;
                $colonPos = -1;

                for ($i = 0; $i < strlen($branch); $i++) {
                    $char = $branch[$i];
                    if ($char === '<' || $char === '(') {
                        $balance++;
                    } elseif ($char === '>' || $char === ')') {
                        $balance--;
                    } elseif ($balance === 0 && $char === '?') {
                        $questionMarkPos = $i;
                        break;
                    }
                }

                if ($questionMarkPos !== -1) {
                    $balance = 0;
                    for ($i = $questionMarkPos + 1; $i < strlen($branch); $i++) {
                        $char = $branch[$i];
                        if ($char === '<' || $char === '(') {
                            $balance++;
                        } elseif ($char === '>' || $char === ')') {
                            $balance--;
                        } elseif ($balance === 0 && $char === ':') {
                            $colonPos = $i;
                            break;
                        }
                    }
                }

                if ($questionMarkPos !== -1 && $colonPos !== -1) {
                    $trueBranch = trim(substr($branch, $questionMarkPos + 1, $colonPos - $questionMarkPos - 1));
                    $falseBranch = trim(substr($branch, $colonPos + 1));

                    $processBranch($trueBranch);
                    $processBranch($falseBranch);
                    return;
                }
            }

            if (!empty($branch)) {
                $possibleTypes[] = trim(preg_replace('/\s+/', ' ', $branch));
            }
        };

        $processBranch($type);

        return array_unique($possibleTypes);
    }
}
