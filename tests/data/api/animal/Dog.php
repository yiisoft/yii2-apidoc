<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\animal;

/**
 * Dog represents a dog animal.
 *
 * @phpstan-type MyArray array{foo: int, bar: string}
 * @psalm-type TypeWithoutEqualsSign array<string, mixed>
 *
 * @phpstan-type InvalidType invalid-type
 *
 * @phpstan-type InvalidTag1
 * @psalm-type InvalidTag2
 * @phpstan-import-type InvalidTag3
 * @psalm-import-type InvalidTag4
 *
 * @psalm-import-type AnimalData from Animal
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.1
 */
class Dog extends Animal
{
    /**
     * @inheritdoc
     */
    public function render()
    {
        // this method has `inheritdoc` tag without brackets
        return 'This is a dog';
    }

    /**
     * @return array{string, string, string}
     */
    public function getThreeStringsArray(): array
    {
        return ['one', 'two', 'three'];
    }

    /**
     * @return MyArray['bar']
     */
    public function testOffsetAccess(): string
    {
        return '';
    }

    /**
     * @return non-empty-list<array>
     */
    public function getNonEmptyList(): array
    {
        return [[]];
    }

    /**
     * @return list
     */
    public function getListWithoutGenerics(): array
    {
        return [];
    }

    /**
     * @return non-empty-list
     */
    public function getNonEmptyListWithoutGenerics(): array
    {
        return [];
    }

    /**
     * @return Dog<Cat, Animal>
     */
    public function getClassWithTwoGenerics()
    {
    }

    /**
     * @return invalid-type
     */
    public function methodWithInvalidReturnTag()
    {
    }

    /**
     * @return static[]
     */
    public function getArrayOfStatic()
    {
    }

    /**
     * @return array<string, static>
     */
    public function getArrayWithStaticGeneric()
    {
    }

    /**
     * @return iterable<string, static>
     */
    public function getIterableWithStaticGeneric()
    {
    }

    /**
     * @return array{someObject: static}
     */
    public function getArrayShapeWithStaticGeneric()
    {
    }

    /**
     * @return array{someObject: static}
     */
    public function getObjectShapeWithStaticGeneric()
    {
    }

    /**
     * @return static|null
     */
    public function getStaticOrNull()
    {
    }

    /**
     * Some description
     */
    public function getNullableString(): ?string
    {
        return null;
    }

    /**
     * @return AnimalData
     */
    public function asArray()
    {
        return parent::asArray();
    }

    /**
     * Some description. See {@see Animal::asArray() parent method}.
     *
     * See {@see Animal::COLOR_GREY}.
     *
     * See {@see Animal::$name}.
     *
     * See {@see Animal}.
     *
     * See {@see \yiiunit\apidoc\data\api\db\ActiveQuery}.
     *
     * See {@see https://www.php.net/manual/intro.filter.php documentation}.
     *
     * See {@see Dog::getNullableString() }.
     *
     * See {@see \ArrayAccess}.
     *
     * See {@see in_array()}.
     *
     * See {@see \in_array()}.
     *
     * See {@see array_merge() documentation}.
     *
     * See {@see array_merge_recursive()}.
     */
    public function testInlineSee(): void
    {
    }

    /**
     * Some description. See {@link https://docs.phpdoc.org/guide/references/phpdoc/tags/link.html documentation}.
     *
     * See invalid link {@link Animal::COLOR_GREY}.
     *
     * See invalid link with {@link array_merge() description}.
     */
    public function testInlineLink(): void
    {
    }
}
