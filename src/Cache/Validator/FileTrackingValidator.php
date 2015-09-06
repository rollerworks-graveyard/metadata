<?php

/*
 * This file is part of the Rollerworks Metadata Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Metadata\Cache\Validator;

use Rollerworks\Component\Metadata\Cache\ChainableFreshnessValidator;
use Rollerworks\Component\Metadata\ClassMetadata;
use Rollerworks\Component\Metadata\FileTrackingClassMetadata;

final class FileTrackingValidator implements ChainableFreshnessValidator
{
    /**
     * {@inheritdoc}
     */
    public function isFresh(ClassMetadata $metadata)
    {
        if ($metadata instanceof FileTrackingClassMetadata) {
            return $metadata->isFresh();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function accepts(ClassMetadata $metadata)
    {
        return $metadata instanceof FileTrackingClassMetadata;
    }
}
