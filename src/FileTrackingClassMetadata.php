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

class FileTrackingClassMetadata extends DefaultClassMetadata
{
    /**
     * @var string[]
     */
    private $fileResources = [];

    /**
     * Constructor.
     *
     * @param string             $className
     * @param PropertyMetadata[] $properties
     * @param MethodMetadata[]   $methods
     * @param DateTime  $createdAt
     * @param string[]           $fileResources
     */
    public function __construct(
        $className,
        array $properties = [],
        array $methods = [],
        DateTime $createdAt = null,
        array $fileResources = []
    ) {
        parent::__construct($className, $properties, $methods, $createdAt);

        $this->fileResources = $fileResources;
    }

    /**
     * Gets whether the cached metadata is fresh.
     *
     * @return bool
     */
    public function isFresh()
    {
        $timestamp = $this->createdAt->getTimestamp();

        foreach ($this->fileResources as $filepath) {
            if (!file_exists($filepath)) {
                return false;
            }

            if ($timestamp < filemtime($filepath)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all the associated metadata file resources.
     *
     * Resources consist of the root class class, parent class(es),
     * imported interfaces/traits and mapping files.
     *
     * @return string[] The complete file locations of resources.
     */
    public function getFileResources()
    {
        return $this->fileResources;
    }

    public function serialize()
    {
        return serialize(
            [
                $this->className,
                $this->properties,
                $this->methods,
                $this->createdAt,
                $this->fileResources,
            ]
        );
    }

    public function unserialize($serialized)
    {
        list(
            $this->className,
            $this->properties,
            $this->methods,
            $this->createdAt,
            $this->fileResources
        ) = unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ClassMetadata $object)
    {
        $properties = $this->properties;
        $methods = $this->methods;
        $resources = $this->fileResources;

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

        if ($object instanceof self) {
            $resources = array_unique(array_merge($resources, $object->getFileResources()));
        }

        return new static($this->className, $properties, $methods, $createdAt, $resources);
    }
}
