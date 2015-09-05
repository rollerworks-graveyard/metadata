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

use ReflectionClass;

final class HierarchyResolver
{
    /**
     * @param ReflectionClass $refl
     * @param int             $flags
     *
     * @return string[]
     */
    public function getClassHierarchy(ReflectionClass $refl, $flags = 0)
    {
        /** @var ReflectionClass[] $classes */
        $classes = [];
        $hierarchy = [];

        do {
            $classes[] = $refl;
            $refl = $refl->getParentClass();
        } while (false !== $refl);

        $classes = array_reverse($classes);

        if ($flags & MetadataFactory::INCLUDE_INTERFACES) {
            $hierarchy = $this->loadInterfaces($hierarchy, $classes);
        }

        if ($flags & MetadataFactory::INCLUDE_TRAITS) {
            $classes = $this->loadClassTraits($classes);
        }

        return array_merge($hierarchy, $classes);
    }

    /**
     * @param array             $hierarchy
     * @param ReflectionClass[] $classes
     *
     * @return string[]
     */
    private function loadInterfaces(array $hierarchy, array $classes)
    {
        $interfaces = [];

        foreach ($classes as $class) {
            foreach ($class->getInterfaces() as $interface) {
                if (isset($interfaces[$interface->name])) {
                    continue;
                }

                $interfaces[$interface->name] = true;
                $hierarchy[] = $interface;
            }

            $hierarchy[] = $class;
        }

        return $hierarchy;
    }

    /**
     * @param ReflectionClass[] $classes
     *
     * @return string[]
     */
    private function loadClassTraits(array $classes)
    {
        $hierarchy = [];

        foreach ($classes as $class) {
            $traits = $this->loadTraits($class->getTraits());

            // Reverse the order of the traits list, (deepest becomes first as later traits overwrite).
            // And add them before the class (class overwrites traits).
            $hierarchy = array_merge($hierarchy, array_reverse($traits));
            $hierarchy[] = $class->name;
        }

        return $hierarchy;
    }

    /**
     * @param ReflectionClass[] $traits
     * @param array             $hierarchy
     *
     * @return string[]
     */
    private function loadTraits(array $traits, array $hierarchy = [])
    {
        foreach ($traits as $trait) {
            $hierarchy[] = $trait->name;

            // Load nested traits, can't use a loop here as
            // there can be more then one trait.
            // And that would blow my little head...
            $hierarchy = $this->loadTraits($trait->getTraits(), $hierarchy);
        }

        return $hierarchy;
    }
}
