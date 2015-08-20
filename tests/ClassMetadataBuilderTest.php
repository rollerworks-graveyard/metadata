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

use Rollerworks\Component\Metadata\ClassMetadataBuilder;
use Rollerworks\Component\Metadata\DefaultClassMetadata;

final class ClassMetadataBuilderTest extends MetadataTestCase
{
    /**
     * @test
     */
    public function it_builds_an_empty_ClassMetadata_instance()
    {
        $builder = new ClassMetadataBuilder('stdClass');

        $classMetadata = $builder->getClassMetadata();
        $this->assertInstanceOf('Rollerworks\Component\Metadata\ClassMetadata', $classMetadata);
        $this->assertEquals('stdClass', $classMetadata->getClassName());
        $this->assertEquals([], $classMetadata->getProperties());
        $this->assertEquals([], $classMetadata->getMethods());
    }

    /**
     * @test
     */
    public function it_builds_a_ClassMetadata_instance_with_properties()
    {
        $builder = new ClassMetadataBuilder('stdClass');
        $builder->addPropertyMetadata($propertyIdMeta = $this->createPropertyMetadata('id', 'stdClass'));
        $builder->addPropertyMetadata($propertyNameMeta = $this->createPropertyMetadata('name', 'stdClass'));

        $classMetadata = $builder->getClassMetadata();

        $this->assertInstanceOf('Rollerworks\Component\Metadata\ClassMetadata', $classMetadata);
        $this->assertEquals('stdClass', $classMetadata->getClassName());
        $this->assertEquals(['id' => $propertyIdMeta, 'name' => $propertyNameMeta], $classMetadata->getProperties());
        $this->assertEquals([], $classMetadata->getMethods());
    }

    /**
     * @test
     */
    public function it_builds_a_ClassMetadata_instance_with_methods()
    {
        $builder = new ClassMetadataBuilder('stdClass');
        $builder->addMethodMetadata($methodIdMeta = $this->createMethodMetadata('getId', 'stdClass'));
        $builder->addMethodMetadata($methodNameMeta = $this->createMethodMetadata('getName', 'stdClass'));

        $classMetadata = $builder->getClassMetadata();

        $this->assertInstanceOf('Rollerworks\Component\Metadata\ClassMetadata', $classMetadata);
        $this->assertEquals('stdClass', $classMetadata->getClassName());
        $this->assertEquals([], $classMetadata->getProperties());
        $this->assertEquals(['getId' => $methodIdMeta, 'getName' => $methodNameMeta], $classMetadata->getMethods());
    }

    /**
     * @test
     */
    public function it_merges_ClassMetadata_into_the_root()
    {
        $classMetadata = new DefaultClassMetadata(
            'DateTime',
            ['name' => $datePropertyMeta = $this->createMethodMetadata('name', 'DateTime')],
            ['getDate' => $dateMethodMeta = $this->createMethodMetadata('getDate', 'DateTime')]
        );

        $builder = new ClassMetadataBuilder('stdClass');
        $builder->addMethodMetadata($methodIdMeta = $this->createMethodMetadata('getId', 'stdClass'));
        $builder->addMethodMetadata($methodNameMeta = $this->createMethodMetadata('getName', 'stdClass'));
        $builder->mergeClassMetadata($classMetadata);

        $classMetadata = $builder->getClassMetadata();

        $this->assertInstanceOf('Rollerworks\Component\Metadata\ClassMetadata', $classMetadata);
        $this->assertEquals('stdClass', $classMetadata->getClassName());
        $this->assertEquals(['name' => $datePropertyMeta], $classMetadata->getProperties());
        $this->assertEquals(
            ['getId' => $methodIdMeta, 'getName' => $methodNameMeta, 'getDate' => $dateMethodMeta],
            $classMetadata->getMethods()
        );
    }

    /**
     * @test
     */
    public function it_supports_creating_by_callback()
    {
        $builder = new ClassMetadataBuilder(
            'stdClass',
            function ($className, array $properties, array $methods) {
                return new DefaultClassMetadata($className, $properties, $methods);
            }
        );

        $builder->addPropertyMetadata($datePropertyMeta = $this->createPropertyMetadata('name', 'stdClass'));
        $builder->addMethodMetadata($methodIdMeta = $this->createMethodMetadata('getId', 'stdClass'));
        $builder->addMethodMetadata($methodNameMeta = $this->createMethodMetadata('getName', 'stdClass'));
        $classMetadata = $builder->getClassMetadata();

        $this->assertInstanceOf('Rollerworks\Component\Metadata\ClassMetadata', $classMetadata);
        $this->assertEquals('stdClass', $classMetadata->getClassName());
        $this->assertEquals(['name' => $datePropertyMeta], $classMetadata->getProperties());
        $this->assertEquals(['getId' => $methodIdMeta, 'getName' => $methodNameMeta], $classMetadata->getMethods());
    }

    /**
     * @test
     */
    public function it_merges_by_ClassMetadata_into_the_root()
    {
        $classMetadata = new DefaultClassMetadata(
            'DateTime',
            ['name' => $datePropertyMeta = $this->createMethodMetadata('name', 'DateTime')],
            ['getName' => $dateMethodMeta = $this->createMethodMetadata('getName', 'DateTime')]
        );

        $builder = new ClassMetadataBuilder('stdClass');
        $builder->addMethodMetadata($methodIdMeta = $this->createMethodMetadata('getId', 'stdClass'));
        $builder->addMethodMetadata($this->createMethodMetadata('getName', 'stdClass'));
        $builder->mergeClassMetadata($classMetadata);

        $classMetadata = $builder->getClassMetadata();

        $this->assertInstanceOf('Rollerworks\Component\Metadata\ClassMetadata', $classMetadata);
        $this->assertEquals('stdClass', $classMetadata->getClassName());
        $this->assertEquals(['name' => $datePropertyMeta], $classMetadata->getProperties());
        $this->assertEquals(['getId' => $methodIdMeta, 'getName' => $dateMethodMeta], $classMetadata->getMethods());
    }
}
