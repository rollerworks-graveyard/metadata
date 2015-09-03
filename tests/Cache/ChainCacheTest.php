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
use Rollerworks\Component\Metadata\Cache\ChainCache;
use Rollerworks\Component\Metadata\DefaultClassMetadata;

final class ChainCacheTest extends CacheProviderTestCase
{
    protected function createCacheProvider()
    {
        return new ChainCache([new ArrayCache()]);
    }

    /**
     * @test
     */
    public function it_only_fetch_the_first_one()
    {
        $classMetadata = new DefaultClassMetadata('stdClass');

        $cache1 = new ArrayCache();
        $cache2 = $this->getMockForAbstractClass('Rollerworks\Component\Metadata\Cache\CacheProvider');

        $cache2->expects($this->never())->method('fetch');

        $chainCache = new ChainCache([$cache1, $cache2]);
        $chainCache->save('id', $classMetadata);

        $this->assertEquals($classMetadata, $chainCache->fetch('id'));
    }

    /**
     * @test
     */
    public function its_fetch_calls_are_propagate_to_the_fastest_cache()
    {
        $classMetadata = new DefaultClassMetadata('stdClass');

        $cache1 = new ArrayCache();
        $cache2 = new ArrayCache();

        $cache2->save('bar', $classMetadata);

        $chainCache = new ChainCache([$cache1, $cache2]);

        $this->assertFalse($cache1->contains('bar'));

        $result = $chainCache->fetch('bar');

        $this->assertEquals($classMetadata, $result);
        $this->assertTrue($cache2->contains('bar'));
    }

    /**
     * @test
     */
    public function its_delete_calls_are_propagate_to_all_providers()
    {
        $cache1 = $this->getMock('Rollerworks\Component\Metadata\Cache\CacheProvider');
        $cache2 = $this->getMock('Rollerworks\Component\Metadata\Cache\CacheProvider');

        $cache1->expects($this->once())->method('delete');
        $cache2->expects($this->once())->method('delete');

        $chainCache = new ChainCache([$cache1, $cache2]);
        $chainCache->delete('bar');
    }

    /**
     * @test
     */
    public function its_clearAll_calls_are_propagate_to_all_supported_providers()
    {
        $cache1 = $this->getMock('Rollerworks\Component\Metadata\Cache\ClearableCacheProvider');
        $cache2 = $this->getMock('Rollerworks\Component\Metadata\Cache\ClearableCacheProvider');
        $cache3 = $this->getMock('Rollerworks\Component\Metadata\Cache\CacheProvider');

        $cache1->expects($this->once())->method('clearAll');
        $cache2->expects($this->once())->method('clearAll');
        // clearAll() on CacheProvider is not expected, calling it will trigger and error.

        $chainCache = new ChainCache([$cache1, $cache2, $cache3]);
        $chainCache->clearAll();
    }
}
