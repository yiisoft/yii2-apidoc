<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\bootstrap;

use yii\apidoc\models\TypeDoc;

/**
 * Common methods for renderers
 */
trait RendererTrait
{
    /**
     * @var array official Yii extensions
     */
    public $extensions = [
        'apidoc',
        'authclient',
        'bootstrap',
        'codeception',
        'composer',
        'debug',
        'elasticsearch',
        'faker',
        'gii',
        'httpclient',
        'imagine',
        'jui',
        'mongodb',
        'redis',
        'shell',
        'smarty',
        'sphinx',
        'swiftmailer',
        'twig',
    ];


    /**
     * Returns nav TypeDocs
     * @param TypeDoc|null $type typedoc to take category from
     * @param TypeDoc[] $types TypeDocs to filter
     * @return array
     */
    public function getNavTypes($type, $types)
    {
        if ($type === null) {
            return $types;
        }

        return $this->filterTypes($types, $this->getTypeCategory($type));
    }

    /**
     * Returns category of TypeDoc
     * @param TypeDoc|null $type
     * @return string
     */
    protected function getTypeCategory($type)
    {
        $extensions = $this->extensions;
        $navClasses = 'app';
        if (isset($type)) {
            if ($type->name == 'Yii' || $type->name == 'YiiRequirementChecker') {
                $navClasses = 'yii';
            } elseif (str_starts_with((string) $type->name, 'yii\\')) {
                $navClasses = 'yii';
                $subName = substr((string) $type->name, 4);
                if (($pos = strpos($subName, '\\')) !== false) {
                    $subNamespace = substr($subName, 0, $pos);
                    if (in_array($subNamespace, $extensions)) {
                        $navClasses = $subNamespace;
                    }
                }
            }
        }

        return $navClasses;
    }

    /**
     * Returns types of a given class
     *
     * @param TypeDoc[] $types
     * @param string $navClasses
     * @return array
     */
    protected function filterTypes($types, $navClasses)
    {
        switch ($navClasses) {
            case 'app':
                $types = array_filter($types, fn($val) => !str_starts_with((string) $val->name, 'yii\\'));
                break;
            case 'yii':
                $self = $this;
                $types = array_filter($types, function ($val) use ($self) {
                    if ($val->name == 'Yii' || $val->name == 'YiiRequirementChecker') {
                        return true;
                    }
                    if (strlen((string) $val->name) < 5) {
                        return false;
                    }
                    $subName = substr((string) $val->name, 4, strpos((string) $val->name, '\\', 5) - 4);

                    return str_starts_with((string) $val->name, 'yii\\') && !in_array($subName, $self->extensions);
                });
                break;
            default:
                $types = array_filter($types, fn($val) => str_starts_with((string) $val->name, "yii\\$navClasses\\"));
        }

        return $types;
    }
}
