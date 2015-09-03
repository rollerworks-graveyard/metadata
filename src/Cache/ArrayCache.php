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
 * Array cache driver.
 */
final class ArrayCache implements ClearableCacheProvider
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * {@inheritdoc}
     */
    public function fetch($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function save($key, ClassMetadata $metadata)
    {
        $this->data[$key] = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        unset($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function clearAll()
    {
        $this->data = [];
    }
}
