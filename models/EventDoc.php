<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Php\Class_;
use phpDocumentor\Reflection\Php\Constant;
use yii\helpers\StringHelper;

/**
 * Represents API documentation information for an `event`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class EventDoc extends ConstDoc
{
    /**
     * @var string|null
     */
    public $type;
    /**
     * @var string[]|null
     */
    public $types;


    /**
     * @param ClassDoc|TraitDoc $parent
     * @param Class_|Constant $reflector
     * @param Context $context
     * @param array $config
     * @param DocBlock $docBlock
     */
    public function __construct($parent, $reflector = null, $context = null, $config = [], $docBlock = null)
    {
        parent::__construct($parent, $reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        foreach ($this->tags as $i => $tag) {
            if ($tag->getName() != 'event') {
                continue;
            }

            $parts = explode(' ', trim($tag->getDescription()), 2);
            $className = $parts[0];
            $this->description = StringHelper::mb_ucfirst($parts[1]);

            if (strpos($className, '\\') !== false)  {
                $this->type = $className;
            } elseif (isset($docBlock->getContext()->getNamespaceAliases()[$className])) {
                $this->type = $docBlock->getContext()->getNamespaceAliases()[$className];
            } else {
                $this->type = $docBlock->getContext()->getNamespace() . '\\' . $className;
            }

            $this->types = [$this->type];
            $this->shortDescription = BaseDoc::extractFirstSentence($this->description);
            unset($this->tags[$i]);
        }
    }
}
