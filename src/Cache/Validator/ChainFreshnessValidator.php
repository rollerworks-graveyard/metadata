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
use Rollerworks\Component\Metadata\Cache\FreshnessValidator;
use Rollerworks\Component\Metadata\ClassMetadata;

final class ChainFreshnessValidator implements FreshnessValidator
{
    /**
     * @var ChainableFreshnessValidator[]
     */
    private $validators;

    /**
     * Constructor.
     *
     * @param ChainableFreshnessValidator[] $validators
     */
    public function __construct($validators)
    {
        if (!is_array($validators) && !($validators instanceof \Iterator)) {
            throw new \InvalidArgumentException('Validators is expected to an array or Iteratable object.');
        }

        $this->validators = $validators;
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh(ClassMetadata $metadata)
    {
        foreach ($this->validators as $validator) {
            if ($validator->accepts($metadata)) {
                return $validator->isFresh($metadata);
            }
        }

        return false;
    }
}
