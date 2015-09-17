<?php

/*
 * This file is part of the Rollerworks Metadata Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Metadata\Driver;

use Rollerworks\Component\Metadata\ClassMetadata;

final class MappingDriverChain implements MappingDriver
{
    /**
     * @var MappingDriver[]
     */
    private $drivers;

    /**
     * @param MappingDriver[] $drivers
     */
    public function __construct(array $drivers)
    {
        $this->drivers = $drivers;
    }

    /**
     * Gets class metadata for the given class name.
     *
     * @param \ReflectionClass $class
     *
     * @return ClassMetadata|null Returns null when no metadata is found.
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        foreach ($this->drivers as $driver) {
            if (null !== $metadata = $driver->loadMetadataForClass($class)) {
                return $metadata;
            }
        }
    }

    /**
     * Gets the names of all mapped classes known to this driver.
     *
     * @return array The names of all mapped classes known to this driver.
     */
    public function getAllClassNames()
    {
        $classNames = [];

        foreach ($this->drivers as $driver) {
            $classNames = array_merge($driver->getAllClassNames());
        }

        return $classNames;
    }

    /**
     * Returns whether the class with the specified name should have
     * its metadata loaded.
     *
     * This can be used for cache warming, where all the class metadata
     * gets loaded during the deployment process.
     *
     * @param string $className
     *
     * @return bool
     */
    public function isTransient($className)
    {
        foreach ($this->drivers as $driver) {
            if (false !== $driver->isTransient($className)) {
                return true;
            }
        }

        return false;
    }
}
