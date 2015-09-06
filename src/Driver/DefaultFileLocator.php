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
 * Credits: This class is largely based on the Doctrine Common DefaultFileLocator.
 *
 * This behavior is independent of the actual content of the file. It just detects
 * the file which is responsible for the given class name.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
final class DefaultFileLocator implements FileLocator
{
    /**
     * The paths where to look for mapping files.
     *
     * @var array
     */
    private $paths = [];

    /**
     * The file extension of mapping documents.
     *
     * @var string|null
     */
    private $fileExtension;

    /**
     * Initializes a new FileDriver that looks in the given path(s) for mapping
     * documents and operates in the specified operating mode.
     *
     * @param string|array $paths         One or multiple paths where mapping documents
     *                                    can be found.
     * @param string|null  $fileExtension The file extension of mapping documents,
     *                                    usually prefixed with a dot.
     */
    public function __construct($paths, $fileExtension = null)
    {
        $this->paths = array_unique(array_merge($this->paths, (array) $paths));
        $this->fileExtension = $fileExtension;
    }

    /**
     * Retrieves the defined metadata lookup paths.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Gets the file extension used to look for mapping files under.
     *
     * @return string|null
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function findMappingFile($className)
    {
        $fileName = str_replace('\\', '.', $className).$this->fileExtension;

        // Check whether file exists
        foreach ($this->paths as $path) {
            if (is_file($path.DIRECTORY_SEPARATOR.$fileName)) {
                return $path.DIRECTORY_SEPARATOR.$fileName;
            }
        }

        throw MappingException::mappingFileNotFound($className, $fileName);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllClassNames($globalBasename)
    {
        $classes = [];

        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                $fileName = $file->getBasename($this->fileExtension);

                if ($fileName === $globalBasename || $fileName === $file->getBasename()) {
                    continue;
                }

                // NOTE: All files found here means classes are not transient!
                $classes[] = str_replace('.', '\\', $fileName);
            }
        }

        return $classes;
    }

    /**
     * {@inheritdoc}
     */
    public function fileExists($className)
    {
        $fileName = str_replace('\\', '.', $className).$this->fileExtension;

        foreach ($this->paths as $path) {
            if (is_file($path.DIRECTORY_SEPARATOR.$fileName)) {
                return true;
            }
        }

        return false;
    }
}
