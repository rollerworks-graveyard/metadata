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

/**
 * A ClearableCacheProvider manages the caching of metadata
 * and allows to delete (clear) all the cached metadata.
 */
interface ClearableCacheProvider extends CacheProvider
{
    /**
     * Delete (clear) all the currently cached metadata.
     */
    public function clearAll();
}
