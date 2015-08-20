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
 * A CacheStorage manages the caching of metadata.
 */
interface CacheStorage
{
    /**
     * Fetches a class metadata instance from the cache.
     *
     * @param string $key
     *
     * @return ClassMetadata|null
     */
    public function fetch($key);

    /**
     * Tests if a class metadata entry exists in the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function contains($key);

    /**
     * Puts the class metadata into the cache.
     *
     * @param ClassMetadata $metadata
     */
    public function save($key, ClassMetadata $metadata);

    /**
     * Deletes a cached class metadata entry.
     *
     * @param string $key
     */
    public function delete($key);
}
