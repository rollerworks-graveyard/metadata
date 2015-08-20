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
 * The loader will only load the mapping of the current class.
 * Child classes or implemented traits/interfaces will not be loaded
 * automatically by an a driver.
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
}
