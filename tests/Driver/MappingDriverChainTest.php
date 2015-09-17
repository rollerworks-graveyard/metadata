<?php

/*
 * This file is part of the Rollerworks Metadata Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Metadata\Tests\Driver;

use Prophecy\Argument;
use Rollerworks\Component\Metadata\DefaultClassMetadata;
use Rollerworks\Component\Metadata\Driver\MappingDriverChain;

final class MappingDriverChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_load_metadata_for_a_class()
    {
        $r = new \ReflectionClass('Rollerworks\Component\Metadata\Tests\Fixtures\User');

        $driver1 = $this->prophesize('Rollerworks\Component\Metadata\Driver\MappingDriver');
        $driver1->loadMetadataForClass($r)->willReturn(null);

        $driver2 = $this->prophesize('Rollerworks\Component\Metadata\Driver\MappingDriver');
        $driver2->loadMetadataForClass($r)->willReturn($metadata = new DefaultClassMetadata($r->name));

        $driverChain = new MappingDriverChain([$driver1->reveal(), $driver2->reveal()]);
        $this->assertEquals($metadata, $driverChain->loadMetadataForClass($r));
    }

    /**
     * @test
     */
    public function it_can_get_all_class_names()
    {
        $driver1 = $this->prophesize('Rollerworks\Component\Metadata\Driver\MappingDriver');
        $driver1->getAllClassNames()->willReturn(['stdClass']);

        $driver2 = $this->prophesize('Rollerworks\Component\Metadata\Driver\MappingDriver');
        $driver2->getAllClassNames()->willReturn(['stdClass', 'User']);

        $driverChain = new MappingDriverChain([$driver1->reveal(), $driver2->reveal()]);
        $this->assertEquals(['stdClass', 'User'], $driverChain->getAllClassNames());
    }

    /**
     * @test
     */
    public function it_gives_transient_true_when_a_driver_returns_true()
    {
        $driver1 = $this->prophesize('Rollerworks\Component\Metadata\Driver\MappingDriver');
        $driver1->isTransient('stdClass')->willReturn(true);
        $driver1->isTransient(Argument::any())->willReturn(false);

        $driver2 = $this->prophesize('Rollerworks\Component\Metadata\Driver\MappingDriver');
        $driver2->isTransient('stdClass')->shouldNotBeCalled();
        $driver2->isTransient(Argument::any())->willReturn(false);

        $driverChain = new MappingDriverChain([$driver1->reveal(), $driver2->reveal()]);

        $this->assertTrue($driverChain->isTransient('stdClass'));
        $this->assertFalse($driverChain->isTransient('User'));
    }
}
