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

use Rollerworks\Component\Metadata\DefaultClassMetadata;

final class DefaultClassMetadataTest extends MetadataTestCase
{
    /**
     * @var DefaultClassMetadata
     */
    private $metadata;
    private $idProperty;
    private $idMethod;

    protected function setUp()
    {
        $this->metadata = new DefaultClassMetadata(
            'stdClass',
            ['id' => $this->idProperty = $this->createPropertyMetadata('id', 'stdClass')],
            ['getId' => $this->idMethod = $this->createMethodMetadata('getId', 'stdClass')]
        );
    }

    /**
     * @test
     */
    public function it_gets_the_className()
    {
        $this->assertSame('stdClass', $this->metadata->getClassName());
    }

    /**
     * @test
     */
    public function it_supports_properties_metadata()
    {
        $this->assertSame(['id' => $this->idProperty], $this->metadata->getProperties());
        $this->assertSame($this->idProperty, $this->metadata->getProperty('id'));
        $this->assertNull($this->metadata->getProperty('noop'));
    }

    /**
     * @test
     */
    public function it_supports_methods_metadata()
    {
        $this->assertSame(['getId' => $this->idMethod], $this->metadata->getMethods());
        $this->assertSame($this->idMethod, $this->metadata->getMethod('getId'));
        $this->assertNull($this->metadata->getMethod('noop'));
    }

    /**
     * @test
     */
    public function it_supports_reflection()
    {
        $this->assertInstanceOf('ReflectionClass', $reflection = $this->metadata->getReflection());
        $this->assertSame($reflection, $this->metadata->getReflection()); // test caching
        $this->assertEquals('stdClass', $reflection->getName());
    }

    /**
     * @test
     */
    public function it_can_be_serialized()
    {
        $this->assertEquals($this->metadata, unserialize(serialize($this->metadata)));
    }
}
