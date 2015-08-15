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

interface MetadataFactory
{
    /**
     * Load mapping information from imported traits.
     */
    const LOAD_TRAITS = 1;

    /**
     * Load mapping information from imported interfaces.
     */
    const LOAD_INTERFACES = 2;

    /**
     * Returns (only) the metadata of the given class name.
     *
     * If no metadata is available, null is returned.
     * Child classes will not be included in the final result.
     *
     * @param string  $className Name of the class to load the metadata of.
     * @param integer $flags     Bitwise flag for options.
     *                           For example `LOAD_TRAITS & LOAD_INTERFACES` will load
     *                           mapping data from the class and any traits and/or interfaces
     *                           that were imported by the class.
     *
     * @return ClassMetadata|null
     */
    public function getClassMetadata($className, $flags = 0);

    /**
     * Returns the merged metadata of the given class name.
     *
     * If no metadata is available, null is returned.
     * Child classes will be included in the final result.
     *
     * @param string  $className Name of the class to load the metadata of.
     * @param integer $flags     Bitwise flag for options.
     *                           For example `LOAD_TRAITS & LOAD_INTERFACES` will load
     *                           mapping data from the class and any traits and/or interfaces
     *                           that were imported by the class.
     *
     * @return ClassMetadata|null
     */
    public function getMergedClassMetadata($className, $flags = 0);
}
