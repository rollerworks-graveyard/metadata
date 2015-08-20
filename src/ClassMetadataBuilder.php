<?php

/*
 * This file is part of the Rollerworks Metadata Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Metadata;

/**
 * Builds a ClassMetadata instance from one or more (sub)classes.
 */
final class ClassMetadataBuilder
{
    /**
     * @var string
     */
    private $classBuilder;
    private $rootClass;

    private $properties = [];
    private $methods = [];

    public function __construct($rootClass, callable $classBuilder = null)
    {
        if (null === $classBuilder) {
            $classBuilder = 'Rollerworks\Component\Metadata\CacheableMetadataFactory::newClassMetadata';
        }

        $this->classBuilder = $classBuilder;
        $this->rootClass = $rootClass;
    }

    public function addPropertyMetadata(PropertyMetadata $property)
    {
        $this->properties[$property->getName()] = $property;
    }

    public function addMethodMetadata(MethodMetadata $method)
    {
        $this->methods[$method->getName()] = $method;
    }

    public function mergeClassMetadata(ClassMetadata $classMetadata)
    {
        foreach ($classMetadata->getProperties() as $property) {
            $this->properties[$property->getName()] = $property;
        }

        foreach ($classMetadata->getMethods() as $method) {
            $this->methods[$method->getName()] = $method;
        }
    }

    /**
     * @return ClassMetadata
     */
    public function getClassMetadata()
    {
        if (!$this->properties && !$this->methods) {
            return new NullClassMetadata($this->rootClass);
        }

        $classMetadata = call_user_func(
            $this->classBuilder,
            $this->rootClass,
            $this->properties,
            $this->methods
        );

        if (!$classMetadata instanceof ClassMetadata) {
            throw new \InvalidArgumentException(
                sprintf(
                    '$classBuilder callback of ClassMetadataBuilder is expected to return an instance of '.
                    'Rollerworks\Component\Metadata\ClassMetadata got "%s" instead.',
                    is_object($classMetadata) ? get_class($classMetadata) : gettype($classMetadata)
                )
            );
        }

        return $classMetadata;
    }
}
