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

use DateTime;
use ReflectionClass;

class DefaultClassMetadata implements ClassMetadata
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var PropertyMetadata[]
     */
    protected $properties = [];

    /**
     * @var MethodMetadata[]
     */
    protected $methods = [];

    /**
     * @var ReflectionClass
     */
    protected $reflection;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * Constructor.
     *
     * @param string             $className
     * @param PropertyMetadata[] $properties
     * @param MethodMetadata[]   $methods
     * @param DateTime  $createdAt
     */
    public function __construct(
        $className,
        array $properties = [],
        array $methods = [],
        DateTime $createdAt = null
    ) {
        $this->className = $className;
        $this->properties = $properties;
        $this->methods = $methods;

        $this->createdAt = $createdAt ?: new DateTime();
    }

    /**
     * Returns when the ClassMetadata was created.
     *
     * This information can be used to check the freshness
     * of the current ClassMetadata.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
        return serialize(
            [
                $this->className,
                $this->properties,
                $this->methods,
                $this->createdAt,
            ]
        );
    }

    public function unserialize($serialized)
    {
        list(
            $this->className,
            $this->properties,
            $this->methods,
            $this->createdAt
        ) = unserialize($serialized);
    }

    /**
     * Merge the ClassMetadata of the object with the current ClassMetadata
     * into a new object.
     *
     * Note: instead of modifying the current ClassMetadata
     * you should instead return a new object.
     *
     * @param ClassMetadata $object Another MergeableClassMetadata object.
     *
     * @return self New ClassMetadata instance with
     *              the merged class metadata.
     */
    public function merge(ClassMetadata $object)
    {
        $properties = $this->properties;
        $methods = $this->methods;

        foreach ($object->getProperties() as $property) {
            $properties[$property->getName()] = $property;
        }

        foreach ($object->getMethods() as $method) {
            $methods[$method->getName()] = $method;
        }

        $createdAt = $this->createdAt;

        if (($otherCreatedAt = $object->getCreatedAt()) > $createdAt) {
            $createdAt = $otherCreatedAt;
        }

        return new static($this->className, $properties, $methods, $createdAt);
    }
}
