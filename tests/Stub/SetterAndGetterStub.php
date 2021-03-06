<?php declare(strict_types=1);

namespace RainbowCat\Hydrator\Test\Stub;

class SetterAndGetterStub extends OnlyPropertyStub
{
    public function setOne(int $one)
    {
        $this->one = $one * 100;
    }
    
    public function getOne(): int
    {
        return $this->one * 100;
    }
    
    public function setTwo(int $two)
    {
        $this->two = $two * 100;
    }
    
    public function getTwo(): int
    {
        return $this->two * 100;
    }
}
