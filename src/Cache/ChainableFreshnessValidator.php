<?php

/*
 * This file is part of the Rollerworks Metadata Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Metadata\Cache;

use Rollerworks\Component\Metadata\ClassMetadata;

/**
 * Implement this interface instead of FreshnessValidator to make
 * the validator chainable.
 *
 * Each validator is checked using the accepts() method till one returns true
 * then the freshness is checked.
 */
interface ChainableFreshnessValidator extends FreshnessValidator
{
    /**
     * Returns whether the validator is able to validate the
     * ClassMetadata object.
     *
     * @param ClassMetadata $metadata ClassMetadata implementation
     *                                to check against.
     *
     * @return bool
     */
    public function accepts(ClassMetadata $metadata);
}
