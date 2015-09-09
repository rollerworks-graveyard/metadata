<?php

/*
 * This file is part of the Rollerworks Metadata Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Metadata\Driver;

use Rollerworks\Component\Metadata\MappingException;

/**
 * Locates the file that contains the metadata information for a given class name.
 *
 * This behavior is independent of the actual content of the file. It just detects
 * the file which is responsible for the given class name.
 */
final class PathByPrefixFileLocator implements FileLocator
{
    /**
     * The paths where to look for mapping files.
     *
     * @var array
     */
    private $paths = [];

    /**
     * @var string
     */
    private $fileExtension;

    /**
     * Initializes a new FileDriver that looks in the given path(s) for mapping
     * documents and operates in the specified operating mode.
     *
     * @param array  $paths         Paths where mapping documents can be found.
     * @param string $fileExtension File extension which is suffixed on the filename.
     *
     * @throws MappingException When one of the paths does not exist.
     */
    public function __construct(array $paths, $fileExtension)
    {
        $newPaths = [];

        foreach ($paths as $prefix => $path) {
            if (!is_dir($path)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
            }

            $newPaths['\\'.trim($prefix, '\\')] = $path;
        }

        $this->paths = $newPaths;
        $this->fileExtension = $fileExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function findMappingFile($className)
    {
        $className = ltrim($className, '\\');
        $fqcn = '\\'.$className;

        foreach ($this->paths as $prefix => $path) {
            if (0 !== strpos($fqcn, $prefix)) {
                continue;
            }

            $len = '\\' === $prefix ? 0 : strlen($prefix);
            $fileName = $path.DIRECTORY_SEPARATOR.str_replace('\\', '.', substr($className, $len)).$this->fileExtension;

            if (file_exists($fileName)) {
                return $fileName;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllClassNames()
    {
        $classes = [];

        foreach ($this->paths as $prefix => $path) {
            $iterator = new \DirectoryIterator($path);
            $nsPrefix = ltrim($prefix.'\\', '\\');

            foreach ($iterator as $file) {
                if ($file->getBasename() === ($fileName = $file->getBasename($this->fileExtension))) {
                    continue;
                }

                $classes[] = $nsPrefix.str_replace('.', '\\', $fileName);
            }
        }

        return $classes;
    }

    /**
     * {@inheritdoc}
     */
    public function fileExists($className)
    {
        return null !== $this->findMappingFile($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }
}
