<?php

/*
 * This file is part of the Rollerworks Metadata Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Metadata\Tests\Driver;

use Rollerworks\Component\Metadata\Driver\PathByPrefixFileLocator;
use Rollerworks\Component\Metadata\Tests\MetadataTestCase;

class PathByPrefixFileLocatorTest extends MetadataTestCase
{
    /**
     * @test
     */
    public function it_has_a_file_extension()
    {
        $locator = new PathByPrefixFileLocator([], '.yml');
        $this->assertEquals('.yml', $locator->getFileExtension());
    }

    /**
     * @test
     */
    public function it_finds_mapping_files_without_prefix()
    {
        $path = dirname(__DIR__).'/_files';

        $locator = new PathByPrefixFileLocator(['\\' => $path], '.yml');

        $this->assertNull($locator->findMappingFile('Acme\Sub\stdClass'));
        $this->assertEquals(
            $path.DIRECTORY_SEPARATOR.'stdClass.yml',
            $locator->findMappingFile('stdClass')
        );
    }

    /**
     * @test
     */
    public function it_finds_mapping_files_with_prefix()
    {
        $path = dirname(__DIR__).'/_files'.DIRECTORY_SEPARATOR.'Acme';

        $locator = new PathByPrefixFileLocator(['Acme\\' => $path], '.yml');

        $this->assertNull($locator->findMappingFile('stdClass'));
        $this->assertNull($locator->findMappingFile('Acme\stdClass'));
        $this->assertEquals($path.DIRECTORY_SEPARATOR.'Model.stdClass.yml', $locator->findMappingFile('Acme\Model\stdClass'));
        $this->assertEquals($path.DIRECTORY_SEPARATOR.'Sub.stdClass.yml', $locator->findMappingFile('Acme\Sub\stdClass'));
    }

    /**
     * @test
     */
    public function it_gives_null_when_no_mapping_file_is_found()
    {
        $path = dirname(__DIR__).'/_files';

        $locator = new PathByPrefixFileLocator(['\\' => $path], '.yml');
        $this->assertNull($locator->findMappingFile('stdClass2'));
    }

    /**
     * @test
     */
    public function it_can_get_all_mapped_classes_without_prefix()
    {
        $path = dirname(__DIR__).'/_files';

        $locator = new PathByPrefixFileLocator(['\\' => $path], '.yml');
        $classes = $locator->getAllClassNames();
        sort($classes);

        $this->assertEquals(['global', 'stdClass'], $classes);
    }

    /**
     * @test
     */
    public function it_can_get_all_mapped_classes_with_prefix()
    {
        $path = dirname(__DIR__).'/_files/Acme';

        $locator = new PathByPrefixFileLocator(['Acme' => $path], '.yml');
        $classes = $locator->getAllClassNames();
        sort($classes);

        $this->assertEquals(['Acme\Model\stdClass', 'Acme\Sub\stdClass'], $classes);
    }

    /**
     * @test
     */
    public function it_only_finds_all_mapped_classes_with_file_extension()
    {
        $path = dirname(__DIR__).'/_files';

        $locator = new PathByPrefixFileLocator(['\\' => $path], '.xml');
        $this->assertEquals([], $locator->getAllClassNames());
    }

    /**
     * @test
     */
    public function it_can_tell_if_files_exist()
    {
        $path = dirname(__DIR__).'/_files';

        $locator = new PathByPrefixFileLocator(['\\' => $path], '.yml');

        $this->assertTrue($locator->fileExists('stdClass'));
        $this->assertFalse($locator->fileExists('stdClass2'));
        $this->assertTrue($locator->fileExists('global'));
        $this->assertFalse($locator->fileExists('global2'));
    }
}
