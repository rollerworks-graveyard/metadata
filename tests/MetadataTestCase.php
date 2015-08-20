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

abstract class MetadataTestCase extends \PHPUnit_Framework_TestCase
{
    protected function createPropertyMetadata($name, $class)
    {
        return new PropertyMetadataStub($name, $class);
    }

    protected function createMethodMetadata($name, $class)
    {
        return new MethodMetadataStub($name, $class);
    }
}
