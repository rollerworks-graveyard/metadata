<?php

namespace Rollerworks\Component\Metadata\Cache;

use Rollerworks\Component\Metadata\ClassMetadata;

interface CacheStorage
{
    /**
     * Fetches a class metadata instance from the cache
     *
     * @param string $className
     *
     * @return ClassMetadata|null
     */
    function fetch($className);

    /**
     * Tests if a class metadata entry exists in the cache.
     *
     * @param string $className
     *
     * @return bool
     */
    function contains($className);

    /**
     * Puts the class metadata into the cache.
     *
     * @param ClassMetadata $metadata
     */
    function save(ClassMetadata $metadata);

    /**
     * Deletes a cached class metadata entry.
     *
     * @param string $className
     */
    function delete($className);
}
