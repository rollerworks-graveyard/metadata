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

use Rollerworks\Component\Metadata\Cache\FreshnessValidator;
use Rollerworks\Component\Metadata\ClassMetadata;

final class AlwaysFreshValidator implements FreshnessValidator
{
    public function isFresh(ClassMetadata $metadata)
    {
        return true;
    }
}
