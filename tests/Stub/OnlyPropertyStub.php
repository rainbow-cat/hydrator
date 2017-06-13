<?php

namespace RainbowCat\Hydrator\Test\Stub;

class OnlyPropertyStub
{
    protected $one;
    protected $two;
    
    public function __construct(int $one = null, int $two = null)
    {
        $this->one = $one;
        $this->two = $two;
    }
}
