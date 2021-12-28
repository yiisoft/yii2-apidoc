<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\File\LocalFile;
use phpDocumentor\Reflection\Php\Factory\ClassConstant as ClassConstantFactory;
use phpDocumentor\Reflection\Php\Factory\Property as PropertyFactory;
use phpDocumentor\Reflection\Php\Project;
use phpDocumentor\Reflection\Php\ProjectFactory;
use yii\apidoc\helpers\PrettyPrinter;
use yii\base\Component;

/**
 * @author Carsten Brandt <mail@cebe.cc>
 */
class Context extends Component
{
    /**
     * @var array list of php files that have been added to this context.
     */
    public $files = [];
    /**
     * @var ClassDoc[]
     */
    public $classes = [];
    /**
     * @var InterfaceDoc[]
     */
    public $interfaces = [];
    /**
     * @var TraitDoc[]
     */
    public $traits = [];
    /**
     * @var array
     */
    public $errors = [];
    /**
     * @var array
     */
    public $warnings = [];
    /**
     * @var Project
     */
    private $reflectionProject;


    /**
     * Returning TypeDoc for a type given
     * @param string $type
     * @return null|ClassDoc|InterfaceDoc|TraitDoc
     */
    public function getType($type)
    {
        $type = ltrim($type, '\\');

        if (isset($this->classes[$type])) {
            return $this->classes[$type];
        }
        if (isset($this->interfaces[$type])) {
            return $this->interfaces[$type];
        }
        if (isset($this->traits[$type])) {
            return $this->traits[$type];
        }

        return null;
    }

    public function processFiles()
    {
        $projectFiles = $this->getReflectionProject()->getFiles();
        foreach ($this->files as $fileName => $hash) {
            $reflection = $projectFiles[$fileName];

            $this->parseFile($reflection, $fileName);
        }
    }

    /**
     * Adds file to context
     * @param string $fileName
     */
    public function addFile($fileName)
    {
        $this->files[$fileName] = sha1_file($fileName);
    }

    private function parseFile($reflection, $fileName)
    {
        foreach ($reflection->getClasses() as $class) {
            $class = new ClassDoc($class, $this, ['sourceFile' => $fileName]);
            $this->classes[$class->name] = $class;
        }
        foreach ($reflection->getInterfaces() as $interface) {
            $interface = new InterfaceDoc($interface, $this, ['sourceFile' => $fileName]);
            $this->interfaces[$interface->name] = $interface;
        }
        foreach ($reflection->getTraits() as $trait) {
            $trait = new TraitDoc($trait, $this, ['sourceFile' => $fileName]);
            $this->traits[$trait->name] = $trait;
        }
    }

    public function updateReferences()
    {
        // update all subclass references
        foreach ($this->classes as $class) {
            $className = $class->name;
            while (isset($this->classes[$class->parentClass])) {
                $class = $this->classes[$class->parentClass];
                $class->subclasses[] = $className;
            }
        }
        // update interfaces of subclasses
        foreach ($this->classes as $class) {
            $this->updateSubclassInterfacesTraits($class);
        }
        // update implementedBy and usedBy for traits
        foreach ($this->classes as $class) {
            foreach ($class->traits as $trait) {
                if (isset($this->traits[$trait])) {
                    $trait = $this->traits[$trait];
                    $trait->usedBy[] = $class->name;
                    $class->properties = array_merge($trait->properties, $class->properties);
                    $class->methods = array_merge($trait->methods, $class->methods);
                }
            }
        }
        foreach ($this->interfaces as $interface) {
            foreach ($interface->parentInterfaces as $pInterface) {
                if (isset($this->interfaces[$pInterface])) {
                    $this->interfaces[$pInterface]->implementedBy[] = $interface->name;
                }
            }
        }
        // inherit docs
        foreach ($this->classes as $class) {
            $this->inheritDocs($class);
        }
        // inherit properties, methods, constants and events from subclasses
        foreach ($this->classes as $class) {
            $this->handleClassInheritance($class);
        }
        // update implementedBy and usedBy for interfaces
        foreach ($this->classes as $class) {
            foreach ($class->interfaces as $interface) {
                if (isset($this->interfaces[$interface])) {
                    $this->interfaces[$interface]->implementedBy[] = $class->name;
                    if ($class->isAbstract) {
                        // add not implemented interface methods
                        foreach ($this->interfaces[$interface]->methods as $method) {
                            if (!isset($class->methods[$method->name])) {
                                $class->methods[$method->name] = $method;
                            }
                        }
                    }
                }
            }
        }
        foreach ($this->interfaces as $interface) {
            $this->updateSubInterfaceInheritance($interface);
        }
        // add properties from getters and setters
        foreach ($this->classes as $class) {
            $this->handlePropertyFeature($class);
        }

        // TODO reference exceptions to methods where they are thrown
    }

    /**
     * Add implemented interfaces and used traits to subclasses
     * @param ClassDoc $class
     */
    protected function updateSubclassInterfacesTraits($class)
    {
        foreach ($class->subclasses as $subclass) {
            $subclass = $this->classes[$subclass];
            $subclass->interfaces = array_unique(array_merge($subclass->interfaces, $class->interfaces));
            $subclass->traits = array_unique(array_merge($subclass->traits, $class->traits));
            $this->updateSubclassInterfacesTraits($subclass);
        }
    }

    /**
     * Add implemented interfaces and used traits to subclasses
     * @param ClassDoc $class
     * @deprecated Use handleClassInheritance() instead
     */
    protected function updateSubclassInheritance($class)
    {
        foreach ($class->subclasses as $subclass) {
            $subclass = $this->classes[$subclass];
            $subclass->events = array_merge($class->events, $subclass->events);
            $subclass->constants = array_merge($class->constants, $subclass->constants);
            $subclass->properties = array_merge($class->properties, $subclass->properties);
            $subclass->methods = array_merge($class->methods, $subclass->methods);
            $this->updateSubclassInheritance($subclass);
        }
    }

    /**
     * @param ClassDoc $class
     */
    protected function handleClassInheritance($class)
    {
        $parents = $this->getParents($class);
        if (!$parents) {
            return;
        }

        $attrNames = ['events', 'constants', 'properties', 'methods'];

        foreach ($parents as $parent) {
            $parent = $this->classes[$parent->name];

            foreach ($attrNames as $attrName) {
                foreach ($parent->$attrName as $item) {
                    if (isset($class->$attrName[$item->name])) {
                        continue;
                    }

                    $class->$attrName += [$item->name => $item];
                }
            }
        }
    }

    /**
     * Add methods to subinterfaces
     * @param InterfaceDoc $interface
     */
    protected function updateSubInterfaceInheritance($interface)
    {
        foreach ($interface->implementedBy as $name) {
            if (!isset($this->interfaces[$name])) {
                continue;
            }

            $subInterface = $this->interfaces[$name];
            $subInterface->methods = array_merge($interface->methods, $subInterface->methods);
            $this->updateSubInterfaceInheritance($subInterface);
        }
    }

    /**
     * Inherit docsblocks using `@inheritDoc` tag.
     * @param ClassDoc $class
     * @see http://phpdoc.org/docs/latest/guides/inheritance.html
     */
    protected function inheritDocs($class)
    {
        // inherit for properties
        foreach ($class->properties as $p) {
            if ($p->hasTag('inheritdoc') && ($inheritTag = $p->getFirstTag('inheritdoc')) !== null) {
                $inheritedProperty = $this->inheritPropertyRecursive($p, $class);
                if (!$inheritedProperty) {
                    $this->errors[] = [
                        'line' => $p->startLine,
                        'file' => $class->sourceFile,
                        'message' => "Property {$p->name} has no parent to inherit from in {$class->name}.",
                    ];
                    continue;
                }

                // set all properties that are empty.
                foreach (['shortDescription', 'type', 'types', 'since'] as $property) {
                    if (empty($p->$property) || is_string($p->$property) && trim($p->$property) === '') {
                        // only copy @since if the package names are equal (or missing)
                        if ($property === 'since' && $p->getPackageName() !== $inheritedProperty->getPackageName()) {
                            continue;
                        }
                        $p->$property = $inheritedProperty->$property;
                    }
                }
                // descriptions will be concatenated.
                $p->description = implode("\n\n", [
                    trim($p->description),
                    trim($inheritedProperty->description),
                    $inheritTag->getDescription(),
                ]);

                $p->removeTag('inheritdoc');
            }
        }

        // inherit for methods
        foreach ($class->methods as $m) {
            if ($m->hasTag('inheritdoc') && ($inheritTag = $m->getFirstTag('inheritdoc')) !== null) {
                $inheritedMethod = $this->inheritMethodRecursive($m, $class);
                if (!$inheritedMethod) {
                    $this->errors[] = [
                        'line' => $m->startLine,
                        'file' => $class->sourceFile,
                        'message' => "Method {$m->name} has no parent to inherit from in {$class->name}.",
                    ];
                    continue;
                }
                // set all properties that are empty.
                foreach (['shortDescription', 'return', 'returnType', 'returnTypes', 'exceptions', 'since'] as $property) {
                    if (empty($m->$property) || is_string($m->$property) && trim($m->$property) === '') {
                        // only copy @since if the package names are equal (or missing)
                        if ($property === 'since' && $m->getPackageName() !== $inheritedMethod->getPackageName()) {
                            continue;
                        }
                        $m->$property = $inheritedMethod->$property;
                    }
                }
                // descriptions will be concatenated.
                $m->description = implode("\n\n", [
                    trim($m->description),
                    trim($inheritedMethod->description),
                    $inheritTag->getDescription(),
                ]);

                foreach ($m->params as $i => $param) {
                    if (!isset($inheritedMethod->params[$i])) {
                        $this->errors[] = [
                            'line' => $m->startLine,
                            'file' => $class->sourceFile,
                            'message' => "Method param $i does not exist in parent method, @inheritdoc not possible in {$m->name} in {$class->name}.",
                        ];
                        continue;
                    }
                    if (empty($param->description) || trim($param->description) === '') {
                        $param->description = $inheritedMethod->params[$i]->description;
                    }
                    if (empty($param->type) || trim($param->type) === '') {
                        $param->type = $inheritedMethod->params[$i]->type;
                    }
                    if (empty($param->types)) {
                        $param->types = $inheritedMethod->params[$i]->types;
                    }
                }
                $m->removeTag('inheritdoc');
            }
        }
    }

    /**
     * @param MethodDoc $method
     * @param ClassDoc $class
     * @return mixed
     */
    private function inheritMethodRecursive($method, $class)
    {
        $inheritanceCandidates = array_merge(
            $this->getParents($class),
            $this->getInterfaces($class)
        );

        $methods = [];
        foreach($inheritanceCandidates as $candidate) {
            if (isset($candidate->methods[$method->name])) {
                $cmethod = $candidate->methods[$method->name];
                if (!$candidate instanceof InterfaceDoc && $cmethod->hasTag('inheritdoc')) {
                    $this->inheritDocs($candidate);
                }
                $methods[] = $cmethod;
            }
        }

        return reset($methods);
    }

    /**
     * @param PropertyDoc $method
     * @param ClassDoc $class
     * @return mixed
     */
    private function inheritPropertyRecursive($method, $class)
    {
        $inheritanceCandidates = array_merge(
            $this->getParents($class),
            $this->getInterfaces($class)
        );

        $properties = [];
        foreach($inheritanceCandidates as $candidate) {
            if (isset($candidate->properties[$method->name])) {
                $cproperty = $candidate->properties[$method->name];
                if ($cproperty->hasTag('inheritdoc')) {
                    $this->inheritDocs($candidate);
                }
                $properties[] = $cproperty;
            }
        }

        return reset($properties);
    }

    /**
     * @param ClassDoc $class
     * @return array
     */
    private function getParents($class)
    {
        if ($class->parentClass === null || !isset($this->classes[$class->parentClass])) {
            return [];
        }
        return array_merge([$this->classes[$class->parentClass]], $this->getParents($this->classes[$class->parentClass]));
    }

    /**
     * @param ClassDoc $class
     * @return array
     */
    private function getInterfaces($class)
    {
        $interfaces = [];
        foreach($class->interfaces as $interface) {
            if (isset($this->interfaces[$interface])) {
                $interfaces[] = $this->interfaces[$interface];
            }
        }
        return $interfaces;
    }

    /**
     * Add properties for getters and setters if class is subclass of [[\yii\base\BaseObject]].
     * @param ClassDoc $class
     */
    protected function handlePropertyFeature($class)
    {
        if (!$this->isSubclassOf($class, 'yii\base\BaseObject')) {
            return;
        }
        foreach ($class->getPublicMethods() as $name => $method) {
            if ($method->isStatic) {
                continue;
            }
            if (!strncmp($name, 'get', 3) && strlen($name) > 3 && $this->hasNonOptionalParams($method)) {
                $propertyName = '$' . lcfirst(substr($method->name, 3));
                $property = isset($class->properties[$propertyName]) ? $class->properties[$propertyName] : null;
                if ($property && $property->getter === null && $property->setter === null) {
                    $this->errors[] = [
                        'line' => $property->startLine,
                        'file' => $class->sourceFile,
                        'message' => "Property $propertyName conflicts with a defined getter {$method->name} in {$class->name}.",
                    ];
                } else {
                    // Override the setter-defined property if it exists already
                    $class->properties[$propertyName] = new PropertyDoc(null, $this, [
                        'name' => $propertyName,
                        'definedBy' => $method->definedBy,
                        'sourceFile' => $class->sourceFile,
                        'visibility' => 'public',
                        'isStatic' => false,
                        'type' => $method->returnType,
                        'types' => $method->returnTypes,
                        'shortDescription' => BaseDoc::extractFirstSentence($method->return),
                        'description' => $method->return,
                        'since' => $method->since,
                        'getter' => $method,
                        'setter' => isset($property->setter) ? $property->setter : null,
                        // TODO set default value
                    ]);
                }
            }
            if (!strncmp($name, 'set', 3) && strlen($name) > 3 && $this->hasNonOptionalParams($method, 1)) {
                $propertyName = '$' . lcfirst(substr($method->name, 3));
                $property = isset($class->properties[$propertyName]) ? $class->properties[$propertyName] : null;
                if ($property) {
                    if ($property->getter === null && $property->setter === null) {
                        $this->errors[] = [
                            'line' => $property->startLine,
                            'file' => $class->sourceFile,
                            'message' => "Property $propertyName conflicts with a defined setter {$method->name} in {$class->name}.",
                        ];
                    } else {
                        // Just set the setter
                        $property->setter = $method;
                    }
                } else {
                    $param = $this->getFirstNotOptionalParameter($method);
                    $class->properties[$propertyName] = new PropertyDoc(null, $this, [
                        'name' => $propertyName,
                        'definedBy' => $method->definedBy,
                        'sourceFile' => $class->sourceFile,
                        'visibility' => 'public',
                        'isStatic' => false,
                        'type' => $param->type,
                        'types' => $param->types,
                        'shortDescription' => BaseDoc::extractFirstSentence($param->description),
                        'description' => $param->description,
                        'since' => $method->since,
                        'setter' => $method,
                    ]);
                }
            }
        }
    }

    /**
     * Check whether a method has `$number` non-optional parameters.
     * @param MethodDoc $method
     * @param int $number number of not optional parameters
     * @return bool
     */
    private function hasNonOptionalParams($method, $number = 0)
    {
        $count = 0;
        foreach ($method->params as $param) {
            if (!$param->isOptional) {
                $count++;
            }
        }
        return $count == $number;
    }

    /**
     * @param MethodDoc $method
     * @return ParamDoc
     */
    private function getFirstNotOptionalParameter($method)
    {
        foreach ($method->params as $param) {
            if (!$param->isOptional) {
                return $param;
            }
        }
        return null;
    }

    /**
     * @param ClassDoc $classA
     * @param ClassDoc|string $classB
     * @return bool
     */
    protected function isSubclassOf($classA, $classB)
    {
        if (is_object($classB)) {
            $classB = $classB->name;
        }
        if ($classA->name == $classB) {
            return true;
        }
        while ($classA->parentClass !== null && isset($this->classes[$classA->parentClass])) {
            $classA = $this->classes[$classA->parentClass];
            if ($classA->name == $classB) {
                return true;
            }
        }
        return false;
    }

    public function getReflectionProject()
    {
        if ($this->reflectionProject !== null) {
            return $this->reflectionProject;
        }

        $files = [];
        foreach ($this->files as $fileName => $hash) {
            $files[] = new LocalFile($fileName);
        }

        $projectFactory = ProjectFactory::createInstance();
        $docBlockFactory = DocBlockFactory::createInstance();
        $priority = 1200;

        $projectFactory->addStrategy(new ClassConstantFactory($docBlockFactory, new PrettyPrinter()), $priority);
        $projectFactory->addStrategy(new PropertyFactory($docBlockFactory, new PrettyPrinter()), $priority);

        $this->reflectionProject = $projectFactory->create('ApiDoc', $files);

        return $this->reflectionProject;
    }
}
