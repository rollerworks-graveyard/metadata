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

/**
 * Loads the mapping information for a given class name.
 *
 * The loader will only load the mapping of the requested class.
 * Inherited classes or implemented traits/interfaces will not be
 * loaded automatically by the driver.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
interface MappingDriver
{
    /**
     * Gets class metadata for the given class name.
     *
     * @param \ReflectionClass $class
     *
     * @return ClassMetadata|null Returns null when no metadata is found.
     */
    public function loadMetadataForClass(\ReflectionClass $class);

    /**
     * Gets the names of all mapped classes known to this driver.
     *
     * @return array The names of all mapped classes known to this driver.
     */
    public function getAllClassNames();

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
    public function isTransient($className);
}
