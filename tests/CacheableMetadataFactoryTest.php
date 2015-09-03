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

use Prophecy\Argument\Token\ObjectStateToken;
use Prophecy\Prophecy\ObjectProphecy;
use Rollerworks\Component\Metadata\CacheableMetadataFactory;
use Rollerworks\Component\Metadata\DefaultClassMetadata;

final class CacheableMetadataFactoryTest extends MetadataTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $driver;

    /**
     * @var ObjectProphecy
     */
    private $cache;

    const FIXTURES_NS = 'Rollerworks\Component\Metadata\Tests\Fixtures\\';
    const FIXTURES_COMPLEX_NS = 'Rollerworks\Component\Metadata\Tests\Fixtures\ComplexHierarchy\\';

    protected function setUp()
    {
        $this->driver = $this->prophesize('Rollerworks\Component\Metadata\Driver\MappingDriver');
        $this->cache = $this->prophesize('Rollerworks\Component\Metadata\Cache\CacheProvider');
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
        $this->assertEquals($classMetadata, $factory->getClassMetadata(self::FIXTURES_NS.'Administrator'));
        $this->assertEquals($classMetadata, $factory->getMergedClassMetadata(self::FIXTURES_NS.'Administrator'));
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

        $namespace = str_replace('\\', '.', self::FIXTURES_NS);
        $this->cache->contains($namespace.'Administrator.single')->willReturn(false);
        $this->cache->contains($namespace.'User.single')->willReturn(false);
        $this->cache->contains($namespace.'Administrator.merged.0')->willReturn(false);
        $this->cache->save($namespace.'Administrator.single', $classMetadata)->shouldBeCalled();
        $this->cache->save($namespace.'Administrator.merged.0', $classMetadata)->shouldBeCalled();

        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_NS.'User'))->willReturn(null);
        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_NS.'Administrator'))->willReturn(
            $classMetadata
        );

        $factory = $this->create();
        $this->assertEquals($classMetadata, $factory->getClassMetadata(self::FIXTURES_NS.'Administrator'));
        $this->assertEquals($classMetadata, $factory->getMergedClassMetadata(self::FIXTURES_NS.'Administrator'));
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

        $namespace = str_replace('\\', '.', self::FIXTURES_NS);
        $this->cache->contains($namespace.'Administrator.single')->willReturn(true);
        $this->cache->contains($namespace.'Administrator.merged.0')->willReturn(true);
        $this->cache->fetch($namespace.'Administrator.single')->willReturn($classMetadata);
        $this->cache->fetch($namespace.'Administrator.merged.0')->willReturn($classMetadata);

        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_NS.'User'))->shouldNotBeCalled();
        $this->driver->loadMetadataForClass($this->reflectionToken(self::FIXTURES_NS.'Administrator'))->shouldNotBeCalled();

        $factory = $this->create();
        $this->assertEquals($classMetadata, $factory->getClassMetadata(self::FIXTURES_NS.'Administrator'));
        $this->assertEquals($classMetadata, $factory->getMergedClassMetadata(self::FIXTURES_NS.'Administrator'));
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

        $this->assertEquals(
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

        $this->assertEquals(
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

        $this->assertEquals(
            $classMetadata,
            $this->create()->getMergedClassMetadata(
                self::FIXTURES_COMPLEX_NS.'SubClassC',
                CacheableMetadataFactory::INCLUDE_TRAITS
            )
        );
    }

    private function reflectionToken($classMame)
    {
        return new ObjectStateToken('getName', $classMame);
    }

    private function create($metadataClass = null)
    {
        return new CacheableMetadataFactory(
            $this->driver->reveal(),
            $this->cache->reveal(),
            $metadataClass
        );
    }
}
