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
    const INCLUDE_TRAITS = 1;

    /**
     * Load mapping information from imported interfaces.
     */
    const INCLUDE_INTERFACES = 2;

    /**
     * Returns (only) the metadata of the given class name.
     *
     * If no metadata is available, null is returned.
     * Child classes will not be included in the final result.
     *
     * @param string $className Name of the class to load the metadata of.
     *
     * @return ClassMetadata
     */
    public function getClassMetadata($className);

    /**
     * Returns the merged metadata of the given class name and its children.
     *
     * Optionally the metadata of implemented interfaces and/or traits
     * can be included also.
     *
     * If no metadata is available, null is returned.
     * Child classes will be included in the final result.
     *
     * @param string $className Name of the class to load the metadata of.
     * @param int    $flags     Bitwise flag for options.
     *                          For example `MetadataFactory::INCLUDE_TRAITS & MetadataFactory::INCLUDE_INTERFACES`
     *                          will load mapping data from the class, any traits
     *                          and/or interfaces that were imported by the class.
     *
     * @return ClassMetadata
     */
    public function getMergedClassMetadata($className, $flags = 0);
}
