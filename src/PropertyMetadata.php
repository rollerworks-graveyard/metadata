<?php

/*
 * This file is part of the Rollerworks Metadata Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Metadata;

interface PropertyMetadata extends \Serializable
{
    /**
     * Return the class name of the property.
     *
     * @return string
     */
    public function getClassName();

    /**
     * Return the name of the property.
     *
     * @return string
     */
    public function getName();

    /**
     * Return property reflection object.
     *
     * @return \ReflectionProperty
     */
    public function getReflection();
}
