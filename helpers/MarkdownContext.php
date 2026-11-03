<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use SplObjectStorage;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\Context;
use yii\apidoc\models\InterfaceDoc;

/**
 * Builds the API context needed for Markdown links without full documentation or source code.
 * @since 4.0.2
 */
final class MarkdownContext
{
    /**
     * Returns an independent context for link resolution, preserving shared inherited members.
     * The supplied context must have its cross-references resolved before copying.
     */
    public static function create(Context $context): Context
    {
        $result = new Context();
        $subjects = new SplObjectStorage();

        foreach (['classes', 'interfaces', 'traits'] as $category) {
            foreach ($context->$category as $name => $type) {
                $class = $type::class;
                $copy = new $class();
                $copy->name = $type->name;
                $copy->namespace = $type->namespace;
                $copy->phpDocContext = $type->phpDocContext;
                $copy->sourceFile = $type->sourceFile;

                if ($type instanceof ClassDoc) {
                    $copy->parentClass = $type->parentClass;
                    $copy->interfaces = $type->interfaces;
                    $copy->traits = $type->traits;
                } elseif ($type instanceof InterfaceDoc) {
                    $copy->parentInterfaces = $type->parentInterfaces;
                }

                $result->{$category}[$name] = $copy;
            }
        }

        foreach (['classes', 'interfaces', 'traits'] as $category) {
            foreach ($context->$category as $name => $type) {
                $copy = $result->{$category}[$name];

                foreach (['methods', 'properties', 'constants', 'events'] as $members) {
                    if (!property_exists($type, $members)) {
                        continue;
                    }
                    foreach ($type->$members as $memberName => $member) {
                        // Inherited members are shared by many types. Keep that sharing in the cache.
                        if (!isset($subjects[$member])) {
                            $memberClass = $member::class;
                            $parent = $result->getType($member->definedBy);
                            $subject = new $memberClass($parent ?? $copy);
                            $subject->name = $member->name;
                            $subject->definedBy = $member->definedBy;
                            $subjects[$member] = $subject;
                        }
                        $copy->{$members}[$memberName] = $subjects[$member];
                    }
                }
            }
        }

        return $result;
    }
}
