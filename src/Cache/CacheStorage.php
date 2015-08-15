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

interface CacheStorage
{
    /**
     * Fetches a class metadata instance from the cache.
     *
     * @param string $className
     *
     * @return ClassMetadata|null
     */
    public function fetch($className);

    /**
     * Tests if a class metadata entry exists in the cache.
     *
     * @param string $className
     *
     * @return bool
     */
    public function contains($className);

    /**
     * Puts the class metadata into the cache.
     *
     * @param ClassMetadata $metadata
     */
    public function save(ClassMetadata $metadata);

    /**
     * Deletes a cached class metadata entry.
     *
     * @param string $className
     */
    public function delete($className);
}
