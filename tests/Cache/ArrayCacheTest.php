<?php

/*
 * This file is part of the Rollerworks Metadata Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Metadata\Tests\Cache;

use Rollerworks\Component\Metadata\Cache\ArrayCache;

final class ArrayCacheTest extends CacheProviderTestCase
{
    protected function createCacheProvider()
    {
        return new ArrayCache();
    }
}
