<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\apidoc\data\api\classes;

use yiiunit\apidoc\data\api\traits\BaseTrait;
use Exception;

/**
 * Description.
 *
 * Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when
 * an unknown printer took a galley of type and scrambled it to make a type specimen book.
 *
 * @since 2.0.0
 * @see AbstractClass description.
 */
class BaseClass extends AbstractClass
{
    use BaseTrait;

    /**
     * @deprecated description
     */
    public const DEPRECATED_CONST = 'DEPRECATED_CONST';

    /**
     * @deprecated 2.2.0 description
     */
    public const DEPRECATED_SINCE_CONST = 'DEPRECATED_SINCE_CONST';

    /**
     * @event Event Description.
     */
    public const EVENT = 'event';

    /**
     * @event Event Description.
     * @deprecated description.
     */
    public const DEPRECATED_EVENT = 'deprecatedEvent';

    /**
     * @event Event Description.
     * @deprecated 2.2.0 description.
     */
    public const DEPRECATED_SINCE_EVENT = 'deprecatedSinceEvent';

    /**
     * Description.
     * @deprecated description.
     */
    public $deprecatedBaseClassProperty;

    /**
     * Description.
     * @deprecated 2.2.0 description.
     */
    public $deprecatedSinceBaseClassProperty;

    /**
     * Description.
     *
     * Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.
     */
    public array $baseClassProperty;

    /**
     * Description
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function abstractMethod()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function baseMethod(int $firstParam, string $secondParam): void
    {
    }

    /** */
    public function methodWithoutDocBlock(): void
    {
    }

    /** */
    public function methodWithoutDescriptions(): void
    {
    }

    /**
     * Lorem Ipsum is simply dummy text of the printing and typesetting industry.
     *
     * Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when
     * an unknown printer took a galley of type and scrambled it to make a type specimen book.
     */
    public function methodWithShortAndDetailedDescriptions(): void
    {
    }

    /**
     * Description.
     *
     * @todo Some description for todo tag.
     */
    public function methodWithTodoTag(): void
    {
    }

    /**
     * Description.
     *
     * @deprecated deprecated method.
     */
    public function deprecatedMethod(): void
    {
    }

    /**
     * Description.
     *
     * @throws Exception
     */
    public function methodWithThrows()
    {
    }
}
