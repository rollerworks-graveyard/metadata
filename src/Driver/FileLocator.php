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

/**
 * Locates the file that contains the metadata information for a given class name.
 *
 * This behavior is independent of the actual content of the file. It just detects
 * the file which is responsible for the given class name.
 *
 * @author Senastiaan Stok <s.stok@rollerscapes.net>
 */
interface FileLocator
{
    /**
     * Locates mapping file for the given class name.
     *
     * @param string $className
     *
     * @return string|null Returns NULL when no mapping file is found.
     */
    public function findMappingFile($className);

    /**
     * Gets all class names that are found with this file locator.
     *
     * @return string[] An array of fully qualified classes names.
     */
    public function getAllClassNames();

    /**
     * Checks if a file can be found for this class name.
     *
     * @param string $className
     *
     * @return bool True when a mapping file exists for the class,
     *              false otherwise.
     */
    public function fileExists($className);
}
