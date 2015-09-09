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
 * All classes are assumed to have the same base namespace.
 *
 * The base namespace is stripped strip from the fully-qualified class-name
 * and the remaining part is used as a filename. Subdirectories are ignored.
 *
 * In practice `Acme\SubNamespace\ClassName` with base-namespace `Acme`
 * is transformed as `SubNamespace.ClassName` in the configured path.
 *
 * This behavior is independent of the actual content of the file. It just detects
 * the file which is responsible for the given class name.
 */
final class SingleBaseFileLocator implements FileLocator
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $nsPrefix;

    /**
     * @var string
     */
    private $fileExtension;

    /**
     * @var int
     */
    private $nsPrefixLen;

    /**
     * Constructor.
     *
     * @param string $nsPrefix      The namespace prefix to strip from the class name.
     * @param string $path          The path where mapping documents can be found.
     * @param string $fileExtension File extension which is suffixed on the filename.
     *
     * @throws MappingException When the path does not exist.
     */
    public function __construct($nsPrefix, $path, $fileExtension)
    {
        if (!is_dir($path)) {
            throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
        }

        $this->nsPrefix = '\\'.trim($nsPrefix, '\\');
        $this->nsPrefixLen = '\\' === $this->nsPrefix ? 0 : strlen($this->nsPrefix);

        $this->fileExtension = $fileExtension;
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function findMappingFile($className)
    {
        $className = ltrim($className, '\\');
        $fqcn = '\\'.$className;

        if (0 !== strpos($fqcn, $this->nsPrefix)) {
            return;
        }

        $classPath = substr($className, $this->nsPrefixLen);
        $filePath = $this->path.DIRECTORY_SEPARATOR.str_replace('\\', '.', $classPath).$this->fileExtension;

        if (is_file($filePath)) {
            return $filePath;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllClassNames()
    {
        $classes = [];
        $nsPrefix = ltrim($this->nsPrefix.'\\', '\\');

        $iterator = new \DirectoryIterator($this->path);

        foreach ($iterator as $file) {
            if (($fileName = $file->getBasename($this->fileExtension)) === $file->getBasename()) {
                continue;
            }

            // NOTE: All files found here means classes are not transient!
            $classes[] = $nsPrefix.str_replace('.', '\\', $fileName);
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
