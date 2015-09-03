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

use Rollerworks\Component\Metadata\Cache\CacheProvider;
use Rollerworks\Component\Metadata\Cache\ClearableCacheProvider;
use Rollerworks\Component\Metadata\DefaultClassMetadata;

abstract class CacheProviderTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @return CacheProvider|ClearableCacheProvider
     */
    abstract protected function createCacheProvider();

    /**
     * @test
     */
    public function it_returns_null_for_missing_metadata()
    {
        $provider = $this->createCacheProvider();

        $this->assertNull($provider->fetch('foo'));
        $this->assertNull($provider->fetch('bar'));
        $this->assertFalse($provider->contains('bar'));
    }

    /**
     * @test
     */
    public function it_can_save_metadata()
    {
        $classMetadata = new DefaultClassMetadata('stdClass');

        $provider = $this->createCacheProvider();
        $provider->save('stdClass', $classMetadata);

        $this->assertEquals($classMetadata, $provider->fetch('stdClass'));
        $this->assertEquals($classMetadata, $provider->fetch('stdClass'));
        $this->assertTrue($provider->contains('stdClass'));

        $this->assertNull($provider->fetch('bar'));
        $this->assertFalse($provider->contains('bar'));
    }

    /**
     * @test
     */
    public function it_can_delete_stored_metadata()
    {
        $classMetadata = new DefaultClassMetadata('stdClass');

        $provider = $this->createCacheProvider();
        $provider->save('stdClass', $classMetadata);
        $provider->save('stdClass2', $classMetadata);

        $this->assertTrue($provider->contains('stdClass'));
        $this->assertTrue($provider->contains('stdClass2'));

        $provider->delete('stdClass');

        $this->assertNull($provider->fetch('stdClass'));
        $this->assertFalse($provider->contains('stdClass'));

        $this->assertTrue($provider->contains('stdClass2'));
        $this->assertEquals($classMetadata, $provider->fetch('stdClass2'));
    }

    /**
     * @test
     */
    public function it_can_clear_all_stored_metadata()
    {
        $provider = $this->createCacheProvider();

        // Ignore when the provider doesn't support clearing.
        // We don't skip as this functionality is not required.
        if (!$provider instanceof ClearableCacheProvider) {
            $this->assertTrue(true);
        }

        $classMetadata = new DefaultClassMetadata('stdClass');

        $provider->save('stdClass', $classMetadata);
        $provider->save('stdClass2', $classMetadata);

        $this->assertTrue($provider->contains('stdClass'));
        $this->assertTrue($provider->contains('stdClass2'));

        $provider->clearAll();

        $this->assertFalse($provider->contains('stdClass'));
        $this->assertFalse($provider->contains('stdClass2'));

        $this->assertNull($provider->fetch('stdClass'));
        $this->assertNull($provider->fetch('stdClass2'));
    }
}
