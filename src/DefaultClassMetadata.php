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

use ReflectionClass;

class DefaultClassMetadata implements ClassMetadata
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var PropertyMetadata[]
     */
    private $properties = [];

    /**
     * @var MethodMetadata[]
     */
    private $methods = [];

    /**
     * @var ReflectionClass
     */
    private $reflection;

    /**
     * Constructor.
     *
     * @param string             $className
     * @param PropertyMetadata[] $properties
     * @param MethodMetadata[]   $methods
     */
    public function __construct($className, array $properties = [], array $methods = [])
    {
        $this->className = $className;
        $this->properties = $properties;
        $this->methods = $methods;
    }

    /**
     * Return the class name.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Return class reflection object.
     *
     * @return ReflectionClass
     */
    public function getReflection()
    {
        if (null === $this->reflection) {
            $this->reflection = new ReflectionClass($this->className);
        }

        return $this->reflection;
    }

    /**
     * Returns the properties metadata of a class.
     *
     * @return PropertyMetadata[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Returns the properties metadata of a class.
     *
     * @param string $name Name of the property.
     *
     * @return PropertyMetadata|null
     */
    public function getProperty($name)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : null;
    }

    /**
     * Returns the methods metadata of a class.
     *
     * @return MethodMetadata[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Returns the methods metadata of a class.
     *
     * @param string $name Name of the method.
     *
     * @return MethodMetadata|null
     */
    public function getMethod($name)
    {
        return isset($this->methods[$name]) ? $this->methods[$name] : null;
    }

    public function serialize()
    {
        return serialize([$this->className, $this->properties, $this->methods]);
    }

    public function unserialize($serialized)
    {
        list($this->className, $this->properties, $this->methods) = unserialize($serialized);
    }
}
