<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\Php\Property;
use yii\helpers\StringHelper;

/**
 * Represents API documentation information for a `property`.
 *
 * @property-read bool $isReadOnly If property is read only.
 * @property-read bool $isWriteOnly If property is write only.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class PropertyDoc extends BaseDoc
{
    public $visibility;
    public $isStatic;
    public $type;
    public $types;
    /**
     * @var string|null
     */
    public $defaultValue;
    // will be set by creating class
    public $getter;
    public $setter;
    // will be set by creating class
    public $definedBy;


    /**
     * @return bool if property is read only
     */
    public function getIsReadOnly()
    {
        return $this->getter !== null && $this->setter === null;
    }

    /**
     * @return bool if property is write only
     */
    public function getIsWriteOnly()
    {
        return $this->getter === null && $this->setter !== null;
    }

    /**
     * @param ClassDoc|TraitDoc $parent
     * @param Property $reflector
     * @param Context $context
     * @param array $config
     */
    public function __construct($parent, $reflector = null, $context = null, $config = [])
    {
        parent::__construct($parent, $reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        $this->visibility = (string) $reflector->getVisibility();
        $this->isStatic = $reflector->isStatic();

        if (PHP_VERSION_ID >= 80100) {
            $reflectorDefault = $reflector->getDefault(false);
            $this->defaultValue = $reflectorDefault !== null ? (string) $reflectorDefault : null;
        } else {
            $this->defaultValue = $reflector->getDefault();
        }

        $hasInheritdoc = false;
        foreach ($this->tags as $tag) {
            if ($tag instanceof Var_) {
                $this->type = (string) $tag->getType();
                $this->types = $this->splitTypes($tag->getType());

                $this->description = StringHelper::mb_ucfirst($tag->getDescription());
                $this->shortDescription = BaseDoc::extractFirstSentence($this->description);
            } elseif ($this->isInheritdocTag($tag)) {
                $hasInheritdoc = true;
            }
        }

        if (empty($this->shortDescription) && $context !== null && !$hasInheritdoc) {
            $context->warnings[] = [
                'line' => $this->startLine,
                'file' => $this->sourceFile,
                'message' => "No short description for element '{$this->name}'",
            ];
        }

        if (!$hasInheritdoc && $this->type === null) {
            $this->type = (string) $reflector->getType();
            $this->types = [$this->type];
        }
    }
}
