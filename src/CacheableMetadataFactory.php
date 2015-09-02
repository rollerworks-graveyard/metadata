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
use Rollerworks\Component\Metadata\Cache\CacheStorage;
use Rollerworks\Component\Metadata\Driver\MappingDriver;

final class CacheableMetadataFactory implements MetadataFactory
{
    /**
     * @var MappingDriver
     */
    private $mappingDriver;

    /**
     * @var CacheStorage
     */
    private $cache;

    /**
     * @var string
     */
    private $classBuilder;

    /**
     * Array of already loaded class metadata.
     *
     * @var array
     */
    private $loadedMetadata = [];

    /**
     * Constructor.
     *
     * @param MappingDriver $mappingDriver Mapping driver used for loading metadata.
     * @param CacheStorage  $cache         Cache storage driver for storing and loading
     *                                     cached metadata.
     * @param callable      $classBuilder  A callback to return a new 'ClassMetadata' instance.
     *                                     Arguments: string $rootClass, array $properties, array $methods
     */
    public function __construct(
        MappingDriver $mappingDriver,
        CacheStorage $cache,
        callable $classBuilder = null
    ) {
        if (null === $classBuilder) {
            $classBuilder = 'Rollerworks\Component\Metadata\CacheableMetadataFactory::newClassMetadata';
        }

        $this->cache = $cache;
        $this->mappingDriver = $mappingDriver;
        $this->classBuilder = $classBuilder;
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

        if (isset($this->loadedMetadata[$cacheKey])) {
            return $this->loadedMetadata[$cacheKey];
        }

        if ($this->cache->contains($cacheKey)) {
            return $this->cache->fetch($cacheKey);
        }

        $reflection = $className instanceof ReflectionClass ? $className : new ReflectionClass($className);

        return $this->filterAndStore(
            $this->mappingDriver->loadMetadataForClass($reflection),
            $cacheKey,
            $className
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMergedClassMetadata($className, $flags = 0)
    {
        $cacheKey = str_replace('\\', '.', $className).'.merged.'.$flags;

        if (isset($this->loadedMetadata[$cacheKey])) {
            return $this->loadedMetadata[$cacheKey];
        }

        if ($this->cache->contains($cacheKey)) {
            return $this->cache->fetch($cacheKey);
        }

        return $this->filterAndStore(
            $this->loadClassMetadata($className, $flags),
            $cacheKey,
            $className
        );
    }

    /**
     * @internal
     *
     * @param string $rootClass
     * @param array  $properties
     * @param array  $methods
     *
     * @return DefaultClassMetadata
     */
    public static function newClassMetadata($rootClass, $properties, $methods)
    {
        return new DefaultClassMetadata($rootClass, $properties, $methods);
    }

    private function filterAndStore(ClassMetadata $classMetadata = null, $cacheKey, $className)
    {
        if (null === $classMetadata) {
            return new NullClassMetadata($className);
        }

        if ($classMetadata instanceof NullClassMetadata) {
            return $classMetadata;
        }

        $this->loadedMetadata[$cacheKey] = $classMetadata;
        $this->cache->save($cacheKey, $classMetadata);

        return $classMetadata;
    }

    private function loadClassMetadata($className, $flags = 0)
    {
        $refl = new ReflectionClass($className);
        $builder = new ClassMetadataBuilder($className, $this->classBuilder);

        /** @var ReflectionClass[] $classes */
        $classes = [];
        $hierarchy = [];

        do {
            $classes[] = $refl;
            $refl = $refl->getParentClass();
        } while (false !== $refl);

        $classes = array_reverse($classes);

        if ($flags & self::INCLUDE_INTERFACES) {
            $hierarchy = $this->loadInterfaces($hierarchy, $classes);
        }

        if ($flags & self::INCLUDE_TRAITS) {
            $classes = $this->loadClassTraits($classes);
        }

        $hierarchy = array_merge($hierarchy, $classes);

        foreach ($hierarchy as $class) {
            $builder->mergeClassMetadata($this->getClassMetadata($class));
        }

        return $builder->getClassMetadata();
    }

    /**
     * @param array             $hierarchy
     * @param ReflectionClass[] $classes
     *
     * @return string[]
     */
    private function loadInterfaces(array $hierarchy, array $classes)
    {
        $interfaces = [];

        foreach ($classes as $class) {
            foreach ($class->getInterfaces() as $interface) {
                if (isset($interfaces[$interface->name])) {
                    continue;
                }

                $interfaces[$interface->name] = true;
                $hierarchy[] = $interface;
            }

            $hierarchy[] = $class;
        }

        return $hierarchy;
    }

    /**
     * @param ReflectionClass[] $classes
     *
     * @return string[]
     */
    private function loadClassTraits(array $classes)
    {
        $hierarchy = [];

        foreach ($classes as $class) {
            $traits = $this->loadTraits($class->getTraits());

            // Reverse the order of the traits list, (deepest becomes first as later traits overwrite).
            // And add them before the class (class overwrites traits).
            $hierarchy = array_merge($hierarchy, array_reverse($traits));
            $hierarchy[] = $class->name;
        }

        return $hierarchy;
    }

    /**
     * @param ReflectionClass[] $traits
     * @param array             $hierarchy
     *
     * @return string[]
     */
    private function loadTraits(array $traits, array $hierarchy = [])
    {
        foreach ($traits as $trait) {
            $hierarchy[] = $trait->name;

            // Load nested traits, can't use a loop here as
            // there can be more then one trait.
            // And that would blow my little head...
            $hierarchy = $this->loadTraits($trait->getTraits(), $hierarchy);
        }

        return $hierarchy;
    }
}
