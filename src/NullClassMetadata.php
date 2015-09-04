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

/**
 * Represents the metadata for a class with no metadata.
 */
class NullClassMetadata implements ClassMetadata
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var ReflectionClass
     */
    private $reflection;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * Constructor.
     *
     * @param string            $className
     * @param DateTime $createdAt
     */
    public function __construct($className, DateTime $createdAt = null)
    {
        $this->className = $className;
        $this->createdAt = $createdAt ?: new DateTime();
    }

    /**
     * Returns when the ClassMetadata was created.
     *
     * This information can be used to check the freshness
     * of the current ClassMetadata.
     *
     * @return DateTime
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
     * @return PropertyMetadata[] Empty array.
     */
    public function getProperties()
    {
        return [];
    }

    /**
     * Returns the properties metadata of a class.
     *
     * @param string $name Name of the property.
     *
     * @return null
     */
    public function getProperty($name)
    {
    }

    /**
     * Returns the methods metadata of a class.
     *
     * @return MethodMetadata[] Empty array.
     */
    public function getMethods()
    {
        return [];
    }

    /**
     * Returns the methods metadata of a class.
     *
     * @param string $name Name of the method.
     *
     * @return null
     */
    public function getMethod($name)
    {
    }

    public function serialize()
    {
        return serialize([$this->className, $this->createdAt]);
    }

    public function unserialize($serialized)
    {
        list($this->className, $this->createdAt) = unserialize($serialized);
    }

    /**
     * Returns a new NullClassMetadata the highest createdAt.
     *
     * No actual merging is performed as NullClassMetadata can't hold
     * any data and it's not possible to determine which ClassMetadata
     * implementation must be used.
     *
     * Only the createdAt of the object is used if it's higher then
     * the current createdAt value.
     *
     * @param ClassMetadata $object Another MergeableClassMetadata object.
     *
     * @return NullClassMetadata
     */
    public function merge(ClassMetadata $object)
    {
        $createdAt = $this->createdAt;

        if (($otherCreatedAt = $object->getCreatedAt()) > $createdAt) {
            $createdAt = $otherCreatedAt;
        }

        return new self($this->className, $createdAt);
    }
}
