<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\models;

use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PropertyDoc;
use yii\apidoc\models\TypeDoc;
use yiiunit\apidoc\TestCase;

class TypeDocTest extends TestCase
{
    private function makeType(): TypeDoc
    {
        $type = new TypeDoc(null, null, ['name' => '\\app\\Post']);

        $save = new MethodDoc($type, null, null, ['name' => 'save', 'visibility' => 'public', 'definedBy' => '\\app\\Post']);
        $load = new MethodDoc($type, null, null, ['name' => 'load', 'visibility' => 'protected', 'definedBy' => '\\app\\Base']);
        $attributes = new MethodDoc($type, null, null, ['name' => 'attributes', 'visibility' => 'public', 'definedBy' => '\\app\\Post']);

        $id = new PropertyDoc($type, null, null, ['name' => '$id', 'visibility' => 'public', 'definedBy' => '\\app\\Post']);
        $count = new PropertyDoc($type, null, null, ['name' => '$count', 'visibility' => 'protected', 'definedBy' => '\\app\\Base']);
        $attributesProp = new PropertyDoc($type, null, null, ['name' => '$attributes', 'visibility' => 'public', 'definedBy' => '\\app\\Post']);

        $type->methods = ['save' => $save, 'load' => $load, 'attributes' => $attributes];
        $type->properties = ['$id' => $id, '$count' => $count, '$attributes' => $attributesProp];

        return $type;
    }

    public function testFindSubjectFindsMethodByName(): void
    {
        $type = $this->makeType();
        $this->assertSame($type->methods['save'], $type->findSubject('save'));
    }

    public function testFindSubjectStripsOwnNamespaceFromSubject(): void
    {
        $type = $this->makeType();
        $this->assertSame('app', $type->namespace);
        $this->assertSame($type->methods['save'], $type->findSubject('app\\save'));
    }

    public function testFindSubjectFindsMethodWithParentheses(): void
    {
        $type = $this->makeType();
        $this->assertSame($type->methods['save'], $type->findSubject('save()'));
    }

    public function testFindSubjectFindsPropertyByDollarName(): void
    {
        $type = $this->makeType();
        $this->assertSame($type->properties['$id'], $type->findSubject('$id'));
    }

    public function testFindSubjectPrefersMethodOverProperty(): void
    {
        $type = $this->makeType();
        $this->assertSame($type->methods['attributes'], $type->findSubject('attributes'));
    }

    public function testFindSubjectWithDollarPrefixForcesProperty(): void
    {
        $type = $this->makeType();
        $this->assertSame($type->properties['$attributes'], $type->findSubject('$attributes'));
    }

    public function testFindSubjectWithParenthesesNeverReturnsProperty(): void
    {
        $type = $this->makeType();
        $this->assertNull($type->findSubject('id()'));
    }

    public function testFindSubjectReturnsNullForEmptyName(): void
    {
        $type = $this->makeType();
        $this->assertNull($type->findSubject(''));
    }

    public function testFindSubjectReturnsNullForUnknownName(): void
    {
        $type = $this->makeType();
        $this->assertNull($type->findSubject('unknown'));
    }

    public function testGetPublicMethods(): void
    {
        $type = $this->makeType();
        $this->assertSame(
            ['save' => $type->methods['save'], 'attributes' => $type->methods['attributes']],
            $type->getPublicMethods(),
        );
    }

    public function testGetProtectedMethods(): void
    {
        $type = $this->makeType();
        $this->assertSame(['load' => $type->methods['load']], $type->getProtectedMethods());
    }

    public function testGetNativeMethodsExcludesInheritedOnes(): void
    {
        $type = $this->makeType();
        $this->assertSame(
            ['save' => $type->methods['save'], 'attributes' => $type->methods['attributes']],
            $type->getNativeMethods(),
        );
    }

    public function testGetPublicProperties(): void
    {
        $type = $this->makeType();
        $this->assertSame(
            ['$id' => $type->properties['$id'], '$attributes' => $type->properties['$attributes']],
            $type->getPublicProperties(),
        );
    }

    public function testGetProtectedProperties(): void
    {
        $type = $this->makeType();
        $this->assertSame(['$count' => $type->properties['$count']], $type->getProtectedProperties());
    }

    public function testGetNativePropertiesExcludesInheritedOnes(): void
    {
        $type = $this->makeType();
        $this->assertSame(
            ['$id' => $type->properties['$id'], '$attributes' => $type->properties['$attributes']],
            $type->getNativeProperties(),
        );
    }
}
