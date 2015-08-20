<?php

namespace Rollerworks\Component\Metadata\Tests\Fixtures\ComplexHierarchy;

class SubClassA extends BaseClass implements InterfaceA
{
    private $bar;

    public function getBaz()
    {
    }
}
