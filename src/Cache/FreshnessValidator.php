<?php

/*
 * This file is part of the Rollerworks Metadata Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Metadata\Cache;

use Rollerworks\Component\Metadata\ClassMetadata;

/**
 * A FreshnessValidator validates if the ClassMetadata is still fresh.
 *
 * One simple example is a time based validation:
 *
 * The ClassMetadata creation-timestamp is compared against the modification
 * timestamp of all associated metadata resources (until one of the metadata
 * timestamp values is higher the stored timestamp.
 *
 * If a higher modification timestamp is found the cache is no longer fresh.
 */
interface FreshnessValidator
{
    /**
     * Validates the ClassMetadata for freshness.
     *
     * @param ClassMetadata $metadata The ClassMetadata to be checked for freshness.
     *
     * @return bool True if the ClassMetadata has not changed since it was created,
     *              false otherwise.
     */
    public function isFresh(ClassMetadata $metadata);
}
