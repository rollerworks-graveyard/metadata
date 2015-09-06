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

use Rollerworks\Component\Metadata\FileTrackingClassMetadata;

final class FileTrackingClassMetadataTest extends MetadataTestCase
{
    /**
     * @var FileTrackingClassMetadata
     */
    private $metadata;
    private $idProperty;
    private $idMethod;
    private $fileResources;

    protected function setUp()
    {
        $this->fileResources = [
            __DIR__.'/Fixtures/ComplexHierarchy/BaseClass.php',
            __DIR__.'/Fixtures/ComplexHierarchy/SubClassA.php',
            __DIR__.'/Fixtures/ComplexHierarchy/InterfaceB.php',
        ];

        $this->metadata = new FileTrackingClassMetadata(
            'stdClass',
            ['id' => $this->idProperty = $this->createPropertyMetadata('id', 'stdClass')],
            ['getId' => $this->idMethod = $this->createMethodMetadata('getId', 'stdClass')],
            null,
            $this->fileResources
        );
    }

    /**
     * @test
     */
    public function it_supports_tracking_file_resources()
    {
        $metadata = new FileTrackingClassMetadata(
            'stdClass',
            ['id' => $idProperty = $this->createPropertyMetadata('id', 'stdClass')],
            ['getId' => $idMethod = $this->createMethodMetadata('getId', 'stdClass')],
            null,
            [
                __DIR__.'/Fixtures/ComplexHierarchy/InterfaceB.php',
                __DIR__.'/Fixtures/Administrator.php',
            ]
        );

        /** @var FileTrackingClassMetadata $classMetadata */
        $classMetadata = $this->metadata->merge($metadata);

        $expected = new FileTrackingClassMetadata(
            'stdClass',
            ['id' => $idProperty],
            ['getId' => $idMethod],
            $metadata->getCreatedAt()
        );

        $this->assertInstanceOf(get_class($this->metadata), $classMetadata);
        $this->assertEquals($expected->getClassName(), $classMetadata->getClassName());
        $this->assertEquals($expected->getProperties(), $classMetadata->getProperties());
        $this->assertEquals($expected->getMethods(), $classMetadata->getMethods());
        $this->assertEquals($expected->getCreatedAt(), $classMetadata->getCreatedAt());
        $this->assertEquals(
            [
                __DIR__.'/Fixtures/ComplexHierarchy/BaseClass.php',
                __DIR__.'/Fixtures/ComplexHierarchy/SubClassA.php',
                __DIR__.'/Fixtures/ComplexHierarchy/InterfaceB.php',
                __DIR__.'/Fixtures/Administrator.php',
            ],
            // Force a renumbering of the keys.
            array_merge([], $classMetadata->getFileResources())
        );
    }

    /**
     * @test
     */
    public function it_supports_merging()
    {
        $this->assertSame($this->fileResources, $this->metadata->getFileResources());
    }

    /**
     * @test
     */
    public function it_can_tell_if_metadata_is_fresh()
    {
        // Assert newly created is fresh.
        $this->assertTrue($this->metadata->isFresh());

        // Force a lower createdAt value, instead of caching actual files.
        $r = new \ReflectionObject($this->metadata);

        $reflectionProperty = $r->getProperty('createdAt');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->metadata, new \DateTime('1990-05-05'));

        $this->assertFalse($this->metadata->isFresh());
    }

    /**
     * @test
     */
    public function it_can_be_serialized()
    {
        $this->assertEquals($this->metadata, unserialize(serialize($this->metadata)));
    }
}
