<?php

namespace Rollerworks\Component\Metadata\Tests\Fixtures\ComplexHierarchy;

class SubClassB extends BaseClass implements InterfaceA, InterfaceB
{
    private $baz;

    public function getBaz()
    {
    }

    public function getWho()
    {
    }
}
