<?php

/*
 * This file is part of the Rollerworks Metadata Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Metadata\Tests\Cache\Validator;

use Prophecy\Argument;
use Rollerworks\Component\Metadata\Cache\Validator\ChainFreshnessValidator;
use Rollerworks\Component\Metadata\DefaultClassMetadata;

final class ChainFreshnessValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_false_when_no_compatible_validator_is_provided()
    {
        $validator1 = $this->prophesize('Rollerworks\Component\Metadata\Cache\ChainableFreshnessValidator');
        $validator1->accepts(Argument::any())->willReturn(false);
        $validator1->isFresh(Argument::any())->shouldNotBeCalled();

        $validator2 = $this->prophesize('Rollerworks\Component\Metadata\Cache\ChainableFreshnessValidator');
        $validator2->accepts(Argument::any())->willReturn(false);
        $validator2->isFresh(Argument::any())->shouldNotBeCalled();

        $chainValidator = new ChainFreshnessValidator([$validator1->reveal(), $validator2->reveal()]);

        $this->assertFalse($chainValidator->isFresh(new DefaultClassMetadata('stdClass')));
    }

    /**
     * @test
     */
    public function it_only_checks_the_first_accepting_validator()
    {
        $validator1 = $this->prophesize('Rollerworks\Component\Metadata\Cache\ChainableFreshnessValidator');
        $validator1->accepts(Argument::any())->willReturn(false);
        $validator1->isFresh(Argument::any())->shouldNotBeCalled();

        $validator2 = $this->prophesize('Rollerworks\Component\Metadata\Cache\ChainableFreshnessValidator');
        $validator2->accepts(Argument::any())->willReturn(true);
        $validator2->isFresh(Argument::any())->willReturn(true);

        $validator3 = $this->prophesize('Rollerworks\Component\Metadata\Cache\ChainableFreshnessValidator');
        $validator3->accepts(Argument::any())->shouldNotBeCalled();
        $validator3->isFresh(Argument::any())->shouldNotBeCalled();

        $chainValidator = new ChainFreshnessValidator(
            [$validator1->reveal(), $validator2->reveal(), $validator3->reveal()]
        );

        $this->assertTrue($chainValidator->isFresh(new DefaultClassMetadata('stdClass')));
        $this->assertTrue($chainValidator->isFresh(new DefaultClassMetadata('stdClass')));
    }
}
