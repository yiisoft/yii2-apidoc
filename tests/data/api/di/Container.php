<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\di;

/**
 * Container implements a [dependency injection](https://en.wikipedia.org/wiki/Dependency_injection) container.
 */
class Container
{
    /**
     * Returns an instance of the requested class.
     *
     * @template T of object
     * @param string|class-string<T> $class the class name, or an alias name (e.g. `foo`).
     * @param array<array-key, mixed> $params a list of constructor parameter values. Use one of two definitions:
     *  - Parameters as name-value pairs, for example: `['posts' => PostRepository::class]`.
     *  - Parameters in the order they appear in the constructor declaration. If you want to skip some parameters,
     *    you should index the remaining ones with the integers that represent their positions in the constructor
     *    parameter list.
     *    Dependencies indexed by name and by position in the same array are not allowed.
     * @param array<string, mixed> $config a list of name-value pairs that will be used to initialize the object properties.
     * @return ($class is class-string<T> ? T : object) an instance of the requested class.
     */
    public function get($class, $params = [], $config = []) {}
}
