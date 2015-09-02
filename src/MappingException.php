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

final class MappingException extends \Exception
{
    /**
     * @param string $className
     * @param array  $namespaces
     *
     * @return self
     */
    public static function classNotFoundInNamespaces($className, $namespaces)
    {
        return new self(
            sprintf(
                'The class "%s" was not found in the chain configured namespaces: %s',
                $className,
                implode(', ', $namespaces)
            )
        );
    }

    /**
     * @param string|null $path
     *
     * @return self
     */
    public static function fileMappingDriversRequireConfiguredDirectoryPath($path = null)
    {
        if (!empty($path)) {
            $path = '['.$path.']';
        }

        return new self(
            sprintf(
                'File mapping drivers must have a valid directory path, '.
                'however the given path "%s" seems to be incorrect!',
                $path
            )
        );
    }

    /**
     * @param string $className
     * @param string $fileName
     *
     * @return self
     */
    public static function mappingFileNotFound($className, $fileName)
    {
        return new self(sprintf('No mapping file found named "%s" for class "%s".', $fileName, $className));
    }

    /**
     * @param string $className
     * @param string $fileName
     *
     * @return self
     */
    public static function invalidMappingFile($className, $fileName)
    {
        return new self('Invalid mapping file "%s" for class "%s".', $fileName, $className);
    }

    /**
     * @param string $className
     *
     * @return self
     */
    public static function nonExistingClass($className)
    {
        return new self(sprintf('Class "%s" does not exist.', $className));
    }
}
