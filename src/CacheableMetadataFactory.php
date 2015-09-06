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

use ReflectionClass;
use Rollerworks\Component\Metadata\Cache\CacheProvider;
use Rollerworks\Component\Metadata\Cache\FreshnessValidator;
use Rollerworks\Component\Metadata\Driver\MappingDriver;

final class CacheableMetadataFactory implements MetadataFactory
{
    /**
     * @var MappingDriver
     */
    private $mappingDriver;

    /**
     * @var CacheProvider
     */
    private $cache;

    /**
     * @var FreshnessValidator
     */
    private $freshnessValidator;

    /**
     * @var HierarchyResolver
     */
    private $hierarchyResolver;

    /**
     * @var callable
     */
    private $rootMetadataBuilder;

    /**
     * Constructor.
     *
     * @param MappingDriver      $mappingDriver       Mapping driver used for loading metadata.
     * @param CacheProvider      $cache               Cache provider for caching metadata. FreshnessValidator
     * @param FreshnessValidator $freshnessValidator  Freshness validator to check if the ClassMetadata
     *                                                is still fresh.
     * @param callable           $rootMetadataBuilder Callback for creating a new ClassMetadata instance,
     *                                                this only receives the className and should create the
     *                                                same class as returned by the drivers.
     */
    public function __construct(
        MappingDriver $mappingDriver,
        CacheProvider $cache,
        FreshnessValidator $freshnessValidator,
        callable $rootMetadataBuilder = null
    ) {
        $this->hierarchyResolver = new HierarchyResolver();

        $this->cache = $cache;
        $this->mappingDriver = $mappingDriver;
        $this->freshnessValidator = $freshnessValidator;
        $this->rootMetadataBuilder = $rootMetadataBuilder ?: [$this, 'createRootMetadata'];
    }

    /**
     * Create a new ClassMetadata instance for getMergedClassMetadata().
     *
     * @param string $className
     *
     * @return FileTrackingClassMetadata
     */
    public function createRootMetadata($className)
    {
        return new FileTrackingClassMetadata($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadata($className)
    {
        // Allow reflection as parameter for internal usage.
        if ($className instanceof ReflectionClass) {
            $className = $className->getName();
        }

        $cacheKey = str_replace('\\', '.', $className).'.single';

        if (null !== $classMetadata = $this->freshOrNull($this->cache->fetch($cacheKey))) {
            return $classMetadata;
        }

        $reflection = $className instanceof ReflectionClass ? $className : new ReflectionClass($className);

        return $this->filterAndStore(
            $cacheKey,
            $className,
            $this->mappingDriver->loadMetadataForClass($reflection)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMergedClassMetadata($className, $flags = 0)
    {
        $cacheKey = str_replace('\\', '.', $className).'.merged.'.$flags;

        if (null !== $classMetadata = $this->freshOrNull($this->cache->fetch($cacheKey))) {
            return $classMetadata;
        }

        return $this->filterAndStore(
            $cacheKey,
            $className,
            $this->loadClassMetadata($className, $flags)
        );
    }

    /**
     * Internal method for handling refreshing of metadata.
     *
     * @param ClassMetadata|null $classMetadata
     *
     * @return ClassMetadata|null
     */
    private function freshOrNull(ClassMetadata $classMetadata = null)
    {
        if (null === $classMetadata) {
            return;
        }

        return $this->freshnessValidator->isFresh($classMetadata) ? $classMetadata : null;
    }

    private function filterAndStore($cacheKey, $className, ClassMetadata $classMetadata = null)
    {
        if (null === $classMetadata) {
            return new NullClassMetadata($className);
        }

        if ($classMetadata instanceof NullClassMetadata) {
            return $classMetadata;
        }

        $this->cache->save($cacheKey, $classMetadata);

        return $classMetadata;
    }

    private function loadClassMetadata($className, $flags = 0)
    {
        $hierarchy = $this->hierarchyResolver->getClassHierarchy(new ReflectionClass($className), $flags);
        $classMetadata = call_user_func($this->rootMetadataBuilder, $className);

        foreach ($hierarchy as $class) {
            if (null !== $otherMetadata = $this->getClassMetadata($class)) {
                $classMetadata = $classMetadata->merge($otherMetadata);
            }
        }

        if (0 === count($classMetadata->getProperties()) && 0 === count($classMetadata->getMethods())) {
            $classMetadata = new NullClassMetadata($className);
        }

        return $classMetadata;
    }
}
