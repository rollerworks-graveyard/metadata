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
 * Cache provider that allows to easily chain multiple cache providers.
 *
 * @author Michaël Gallego <mic.gallego@gmail.com>
 */
class ChainCache implements CacheProvider, ClearableCacheProvider
{
    /**
     * @var CacheProvider[]
     */
    private $cacheProviders = [];

    /**
     * Constructor.
     *
     * @param CacheProvider[] $cacheProviders
     */
    public function __construct($cacheProviders = [])
    {
        $this->cacheProviders = $cacheProviders;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($key)
    {
        foreach ($this->cacheProviders as $providerKey => $cacheProvider) {
            if ($cacheProvider->contains($key)) {
                /** @var ClassMetadata $metadata */
                $metadata = $cacheProvider->fetch($key);

                // Populate all the previous cache layers (that are assumed to be faster).
                for ($subKey = $providerKey - 1; $subKey >= 0; --$subKey) {
                    $this->cacheProviders[$subKey]->save($key, $metadata);
                }

                return $metadata;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function contains($key)
    {
        foreach ($this->cacheProviders as $cacheProvider) {
            if ($cacheProvider->contains($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function save($key, ClassMetadata $metadata)
    {
        foreach ($this->cacheProviders as $cacheProvider) {
            $cacheProvider->save($key, $metadata);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        foreach ($this->cacheProviders as $cacheProvider) {
            $cacheProvider->delete($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clearAll()
    {
        foreach ($this->cacheProviders as $cacheProvider) {
            if ($cacheProvider instanceof ClearableCacheProvider) {
                $cacheProvider->clearAll();
            }
        }
    }
}
