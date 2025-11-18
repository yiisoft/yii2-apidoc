<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\animal;

/**
 * Cat represents a cat animal.
 *
 * @method $first is true ? string : string[] methodWithInvalidReturnType2(bool $first) Will be ignored
 *
 * @psalm-type SomePsalmType = (string|array<string, mixed>)
 * @phpstan-type SomePhpStanType (string|array<string, mixed>|object)
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.1
 */
class Cat extends Animal
{
    /**
     * {@inheritdoc}
     */
    public function render()
    {
        // this method has `inheritdoc` tag in brackets
        return 'This is a cat';
    }

    public function methodWithoutDocAndTypeHints($param)
    {
        return $param;
    }

    /**
     * @return $first is true ? string : string[] Incorrect conditional type (without parentheses)
     */
    public function methodWithInvalidReturnType(bool $first)
    {
        return $first ? '' : [''];
    }

    /**
     * @return SomePsalmType
     */
    public function getSomePsalmType(): array
    {
        return [];
    }

    /**
     * @return SomePhpStanType
     */
    public function getSomePhpStanType(): array
    {
        return [];
    }
}
