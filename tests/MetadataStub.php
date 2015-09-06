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

abstract class MetadataStub
{
    private $name;
    private $className;

    public function __construct($name, $className)
    {
        $this->name = $name;
        $this->className = $className;
    }

    public function serialize()
    {
        return serialize(
            [
                $this->name,
                $this->className,
            ]
        );
    }

    public function unserialize($serialized)
    {
        list($this->name, $this->className) = unserialize($serialized);
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getReflection()
    {
    }
}
