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

use Rollerworks\Component\Metadata\Driver\DefaultFileLocator;

class DefaultFileLocatorTest extends MetadataTestCase
{
    public function testGetPaths()
    {
        $path = __DIR__.'/_files';

        $locator = new DefaultFileLocator([$path]);
        $this->assertEquals([$path], $locator->getPaths());

        $locator = new DefaultFileLocator($path);
        $this->assertEquals([$path], $locator->getPaths());
    }

    public function testGetFileExtension()
    {
        $locator = new DefaultFileLocator([], '.yml');
        $this->assertEquals('.yml', $locator->getFileExtension());
    }

    public function testUniquePaths()
    {
        $path = __DIR__.'/_files';

        $locator = new DefaultFileLocator([$path, $path]);
        $this->assertEquals([$path], $locator->getPaths());
    }

    public function testFindMappingFile()
    {
        $path = __DIR__.'/_files';

        $locator = new DefaultFileLocator([$path], '.yml');
        $this->assertEquals(__DIR__.'/_files'.DIRECTORY_SEPARATOR.'stdClass.yml', $locator->findMappingFile('stdClass'));
    }

    public function testFindMappingFileNotFound()
    {
        $path = __DIR__.'/_files';

        $locator = new DefaultFileLocator([$path], '.yml');

        $this->setExpectedException(
            'Rollerworks\Component\Metadata\MappingException',
            'No mapping file found named "stdClass2.yml" for class "stdClass2"'
        );

        $locator->findMappingFile('stdClass2');
    }

    public function testGetAllClassNames()
    {
        $path = __DIR__.'/_files';

        $locator = new DefaultFileLocator([$path], '.yml');
        $classes = $locator->getAllClassNames(null);
        sort($classes);

        $this->assertEquals(['global', 'stdClass'], $classes);
        $this->assertEquals(['stdClass'], $locator->getAllClassNames('global'));
    }

    public function testGetAllClassNamesNonMatchingFileExtension()
    {
        $path = __DIR__.'/_files';

        $locator = new DefaultFileLocator([$path], '.xml');
        $this->assertEquals([], $locator->getAllClassNames('global'));
    }

    public function testFileExists()
    {
        $path = __DIR__.'/_files';

        $locator = new DefaultFileLocator([$path], '.yml');

        $this->assertTrue($locator->fileExists('stdClass'));
        $this->assertFalse($locator->fileExists('stdClass2'));
        $this->assertTrue($locator->fileExists('global'));
        $this->assertFalse($locator->fileExists('global2'));
    }
}
