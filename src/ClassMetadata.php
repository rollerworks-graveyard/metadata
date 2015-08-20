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

interface ClassMetadata extends \Serializable
{
    /**
     * Return the class name.
     *
     * @return string
     */
    public function getClassName();

    /**
     * Return class reflection object.
     *
     * @return \ReflectionClass
     */
    public function getReflection();

    /**
     * Returns the properties metadata of a class.
     *
     * @return PropertyMetadata[]
     */
    public function getProperties();

    /**
     * Returns the properties metadata of a class.
     *
     * @param string $name Name of the property.
     *
     * @return PropertyMetadata|null
     */
    public function getProperty($name);

    /**
     * Returns the methods metadata of a class.
     *
     * @return MethodMetadata[]
     */
    public function getMethods();

    /**
     * Returns the methods metadata of a class.
     *
     * @param string $name Name of the method.
     *
     * @return MethodMetadata|null
     */
    public function getMethod($name);
}
