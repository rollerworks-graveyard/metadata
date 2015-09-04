<?php

/*
 * This file is part of the Rollerworks Metadata Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Metadata\Tests;

use DateTime;
use Prophecy\Argument;
use Prophecy\Argument\Token\ObjectStateToken;
use Prophecy\Prophecy\ObjectProphecy;
use Rollerworks\Component\Metadata\Cache\ArrayCache;
use Rollerworks\Component\Metadata\CacheableMetadataFactory;
use Rollerworks\Component\Metadata\ClassMetadata;
use Rollerworks\Component\Metadata\DefaultClassMetadata;

final class CacheableMetadataFactoryTest extends MetadataTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $driver;

    /**
     * @var ArrayCache
     */
    private $cache;

    /**
     * @var ObjectProphecy
     */
    private $freshnessValidator;

    const FIXTURES_NS = 'Rollerworks\Component\Metadata\Tests\Fixtures\\';
    const FIXTURES_COMPLEX_NS = 'Rollerworks\Component\Metadata\Tests\Fixtures\ComplexHierarchy\\';

    protected function setUp()
    {
        $this->driver = $this->prophesize('Rollerworks\Component\Metadata\Driver\MappingDriver');
        $this->freshnessValidator = $this->prophesize('Rollerworks\Component\Metadata\Cache\FreshnessValidator');
        $this->cache = new ArrayCache();
    }

    /**
     * @test
     */
    public function it_returns_NullMetadata_for_missing_metadata()
    {
        $factory = $this->create();

        $this->assertInstanceOf('Rollerworks\Component\Metadata\NullClassMetadata', $factory->getClassMetadata('stdClass'));
        $this->assertInstanceOf('Rollerworks\Component\Metadata\NullClassMetadata', $factory->getMergedClassMetadata('stdClass'));
    }

    /**
     * @test
     */
    public function it_returns_Metadata_of_a_class()
    {
        $classMetadata = new DefaultClassMetadata(
            self::FIXTURES_NS.'Administrator',
            ['bar' => new PropertyMetadataStub('bar', self::FIXTURES_NS.'Administrator')]
        );

        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_NS.'User'))->willReturn(null);
        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_NS.'Administrator'))->willReturn(
            $classMetadata
        );

        $factory = $this->create();
        $this->assertClassMetadataEquals($classMetadata, $factory->getClassMetadata(self::FIXTURES_NS.'Administrator'));
        $this->assertClassMetadataEquals($classMetadata, $factory->getMergedClassMetadata(self::FIXTURES_NS.'Administrator'));
    }

    /**
     * @test
     */
    public function it_stores_metadata_in_the_cache()
    {
        $classMetadata = new DefaultClassMetadata(
            self::FIXTURES_NS.'Administrator',
            ['bar' => new PropertyMetadataStub('bar', self::FIXTURES_NS.'Administrator')]
        );

        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_NS.'User'))->willReturn(null);
        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_NS.'Administrator'))->willReturn(
            $classMetadata
        );

        // Use closure to work around the createdAt time.
        $factory = $this->create(function () use ($classMetadata) { return $classMetadata; });

        $this->assertClassMetadataEquals($classMetadata, $factory->getClassMetadata(self::FIXTURES_NS.'Administrator'));
        $this->assertClassMetadataEquals($classMetadata, $factory->getMergedClassMetadata(self::FIXTURES_NS.'Administrator'));
    }

    /**
     * @test
     */
    public function it_loads_metadata_from_the_cache()
    {
        $classMetadata = new DefaultClassMetadata(
            self::FIXTURES_NS.'Administrator',
            ['bar' => new PropertyMetadataStub('bar', self::FIXTURES_NS.'Administrator')]
        );

        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_NS.'User'))->willReturn(null);

        // Expect one call for loading, and no further calls as it must use the cache.
        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_NS.'Administrator'))
            ->willReturn($classMetadata)
            ->shouldBeCalledTimes(1)
        ;

        $this->freshnessValidator->isFresh(Argument::type('Rollerworks\Component\Metadata\DefaultClassMetadata'))->willReturn(true);

        // Use closure to work around the createdAt time.
        $factory = $this->create(function () use ($classMetadata) { return $classMetadata; });

        $this->assertClassMetadataEquals($classMetadata, $factory->getClassMetadata(self::FIXTURES_NS.'Administrator'));
        $this->assertClassMetadataEquals($classMetadata, $factory->getMergedClassMetadata(self::FIXTURES_NS.'Administrator'));

        // Second time, should be cached.
        $this->assertClassMetadataEquals($classMetadata, $factory->getClassMetadata(self::FIXTURES_NS.'Administrator'));
        $this->assertClassMetadataEquals($classMetadata, $factory->getMergedClassMetadata(self::FIXTURES_NS.'Administrator'));
    }

    /**
     * @test
     */
    public function it_reloads_metadata_when_metadata_is_not_fresh()
    {
        $state = 0;

        $expiredClassMetadata = new DefaultClassMetadata(
            self::FIXTURES_NS.'Administrator',
            ['bar' => new PropertyMetadataStub('bar', self::FIXTURES_NS.'Administrator')],
            [],
            new DateTime('1990-05-05')
        );

        $freshClassMetadata = new DefaultClassMetadata(
            self::FIXTURES_NS.'Administrator',
            ['bar' => new PropertyMetadataStub('bar', self::FIXTURES_NS.'Administrator')],
            [],
            new DateTime('now + 80 seconds')
        );

        $this->freshnessValidator->isFresh($expiredClassMetadata)->willReturn(false);
        $this->freshnessValidator->isFresh($freshClassMetadata)->willReturn(true);

        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_NS.'User'))->willReturn(null);

        // Expect two calls. one for initial loading (expired data), then another one for getting the new (fresh data)
        // then no more calls as the cache is now fresh again.
        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_NS.'Administrator'))
            ->will(function () use (&$state, $expiredClassMetadata, $freshClassMetadata) {
                if (0 === $state) {
                    $state = 1;

                    return $expiredClassMetadata;
                }

                return $freshClassMetadata;
            })
            ->shouldBeCalledTimes(2)
        ;

        $factory = $this->create();

        $this->assertClassMetadataEquals($expiredClassMetadata, $factory->getClassMetadata(self::FIXTURES_NS.'Administrator'));

        // Second time, should be cached.
        $this->assertClassMetadataEquals($freshClassMetadata, $factory->getClassMetadata(self::FIXTURES_NS.'Administrator'));
    }

    /**
     * @test
     */
    public function it_returns_the_merged_hierarchy_Metadata_of_a_class()
    {
        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_COMPLEX_NS.'SubClassA'))->willReturn(
            new DefaultClassMetadata(
                self::FIXTURES_COMPLEX_NS.'SubClassA',
                ['bar' => new PropertyMetadataStub('bar', self::FIXTURES_COMPLEX_NS.'SubClassA')]
            )
        );

        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_COMPLEX_NS.'BaseClass'))->willReturn(
            new DefaultClassMetadata(
                self::FIXTURES_COMPLEX_NS.'BaseClass',
                ['foo' => new PropertyMetadataStub('foo', self::FIXTURES_COMPLEX_NS.'BaseClass')],
                ['getBar' => new MethodMetadataStub('getBaz', self::FIXTURES_COMPLEX_NS.'BaseClass')]
            )
        );

        $classMetadata = new DefaultClassMetadata(
            self::FIXTURES_COMPLEX_NS.'SubClassA',
            [
                'bar' => new PropertyMetadataStub('bar', self::FIXTURES_COMPLEX_NS.'SubClassA'),
                'foo' => new PropertyMetadataStub('foo', self::FIXTURES_COMPLEX_NS.'BaseClass'),
            ],
            [
                'getBaz' => new MethodMetadataStub('getBaz', self::FIXTURES_COMPLEX_NS.'BaseClass'),
            ]
        );

        $this->assertClassMetadataEquals(
            $classMetadata,
            $this->create()->getMergedClassMetadata(self::FIXTURES_COMPLEX_NS.'SubClassA')
        );
    }

    /**
     * @test
     */
    public function it_returns_the_merged_hierarchy_Metadata_of_a_class_with_interfaces()
    {
        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_COMPLEX_NS.'SubClassB'))->willReturn(
            new DefaultClassMetadata(
                self::FIXTURES_COMPLEX_NS.'SubClassB',
                ['baz' => new PropertyMetadataStub('baz', self::FIXTURES_COMPLEX_NS.'SubClassB')],

                // overwrites InterfaceA::getBaz()
                ['getBaz' => new MethodMetadataStub('getBaz', self::FIXTURES_COMPLEX_NS.'SubClassB')]
            )
        );

        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_COMPLEX_NS.'BaseClass'))->willReturn(
            new DefaultClassMetadata(
                self::FIXTURES_COMPLEX_NS.'BaseClass',
                ['foo' => new PropertyMetadataStub('foo', self::FIXTURES_COMPLEX_NS.'BaseClass')],
                ['getBar' => new MethodMetadataStub('getBaz', self::FIXTURES_COMPLEX_NS.'BaseClass')]
            )
        );

        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_COMPLEX_NS.'InterfaceA'))->willReturn(
            new DefaultClassMetadata(
                self::FIXTURES_COMPLEX_NS.'InterfaceA',
                [],
                [
                    'getBaz' => new MethodMetadataStub('getBaz', self::FIXTURES_COMPLEX_NS.'InterfaceA'),
                    'getBar' => new MethodMetadataStub('getBar', self::FIXTURES_COMPLEX_NS.'InterfaceA'),
                ]
            )
        );

        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_COMPLEX_NS.'InterfaceB'))->willReturn(
            new DefaultClassMetadata(
                self::FIXTURES_COMPLEX_NS.'InterfaceB',
                [],
                ['getWho' => new MethodMetadataStub('getWho', self::FIXTURES_COMPLEX_NS.'InterfaceB')]
            )
        );

        $classMetadata = new DefaultClassMetadata(
            self::FIXTURES_COMPLEX_NS.'SubClassB',
            [
                'foo' => new PropertyMetadataStub('foo', self::FIXTURES_COMPLEX_NS.'BaseClass'),
                'baz' => new PropertyMetadataStub('baz', self::FIXTURES_COMPLEX_NS.'SubClassB'),
            ],
            [
                'getBar' => new MethodMetadataStub('getBar', self::FIXTURES_COMPLEX_NS.'InterfaceA'),
                'getWho' => new MethodMetadataStub('getWho', self::FIXTURES_COMPLEX_NS.'InterfaceB'),
                'getBaz' => new MethodMetadataStub('getBaz', self::FIXTURES_COMPLEX_NS.'SubClassB'),
            ]
        );

        $this->assertClassMetadataEquals(
            $classMetadata,
            $this->create()->getMergedClassMetadata(
                self::FIXTURES_COMPLEX_NS.'SubClassB',
                CacheableMetadataFactory::INCLUDE_INTERFACES
            )
        );
    }

    /**
     * @test
     */
    public function it_returns_the_merged_hierarchy_Metadata_of_a_class_with_traits()
    {
        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_COMPLEX_NS.'SubClassC'))->willReturn(
            new DefaultClassMetadata(self::FIXTURES_COMPLEX_NS.'SubClassC')
        );

        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_COMPLEX_NS.'BaseClass'))->willReturn(
            new DefaultClassMetadata(
                self::FIXTURES_COMPLEX_NS.'BaseClass',
                ['foo' => new PropertyMetadataStub('foo', self::FIXTURES_COMPLEX_NS.'BaseClass')],
                ['getBar' => new MethodMetadataStub('getBar', self::FIXTURES_COMPLEX_NS.'BaseClass')]
            )
        );

        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_COMPLEX_NS.'TraitA'))->willReturn(
            new DefaultClassMetadata(
                self::FIXTURES_COMPLEX_NS.'TraitA',
                ['baz' => new PropertyMetadataStub('baz', self::FIXTURES_COMPLEX_NS.'TraitA')],
                ['getBaz' => new MethodMetadataStub('getBaz', self::FIXTURES_COMPLEX_NS.'TraitA')]
            )
        );

        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_COMPLEX_NS.'SubTraitB'))->willReturn(
            new DefaultClassMetadata(
                self::FIXTURES_COMPLEX_NS.'SubTraitB',
                [],
                ['getWho' => new MethodMetadataStub('getWho', self::FIXTURES_COMPLEX_NS.'SubTraitB')]
            )
        );

        $classMetadata = new DefaultClassMetadata(
            self::FIXTURES_COMPLEX_NS.'SubClassC',
            [
                'foo' => new PropertyMetadataStub('foo', self::FIXTURES_COMPLEX_NS.'BaseClass'),
                'baz' => new PropertyMetadataStub('baz', self::FIXTURES_COMPLEX_NS.'TraitA'),
            ],
            [
                'getBaz' => new MethodMetadataStub('getBaz', self::FIXTURES_COMPLEX_NS.'TraitA'),
                'getWho' => new MethodMetadataStub('getWho', self::FIXTURES_COMPLEX_NS.'SubTraitB'),
                'getBar' => new MethodMetadataStub('getBar', self::FIXTURES_COMPLEX_NS.'BaseClass'),
            ]
        );

        $this->assertClassMetadataEquals(
            $classMetadata,
            $this->create()->getMergedClassMetadata(
                self::FIXTURES_COMPLEX_NS.'SubClassC',
                CacheableMetadataFactory::INCLUDE_TRAITS
            )
        );
    }

    private function assertClassMetadataEquals(ClassMetadata $expected, ClassMetadata $actual)
    {
        $this->assertInstanceOf(get_class($expected), $actual);
        $this->assertEquals($expected->getClassName(), $actual->getClassName());
        $this->assertEquals($expected->getProperties(), $actual->getProperties());
        $this->assertEquals($expected->getMethods(), $actual->getMethods());
    }

    private function reflectionToken($classMame)
    {
        return new ObjectStateToken('getName', $classMame);
    }

    private function create($metadataClass = null)
    {
        return new CacheableMetadataFactory(
            $this->driver->reveal(),
            $this->cache,
            $this->freshnessValidator->reveal(),
            $metadataClass
        );
    }
}
