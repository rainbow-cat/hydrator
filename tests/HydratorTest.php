<?php

namespace RainbowCat\Hydrator\Test;

use PHPUnit\Framework\TestCase;
use RainbowCat\Hydrator\Hydrator;
use RainbowCat\Hydrator\Test\Stub\{OnlyPropertyStub, SetterAndGetterStub};
use InvalidArgumentException;

class HydratorTest extends TestCase
{
    public function testHydrateToProperties()
    {
        $object = new OnlyPropertyStub();
        
        $hydrator = new Hydrator(OnlyPropertyStub::class, ['one', 'two']);
        $hydrator->hydrate($object, ['one' => 1, 'two' => 2]);
        
        $this->assertAttributeSame(1, 'one', $object);
        $this->assertAttributeSame(2, 'two', $object);
    }
    
    public function testHydrateViaPropertySetterMethods()
    {
        $object = new SetterAndGetterStub();
        
        $hydrator = new Hydrator(SetterAndGetterStub::class, ['one', 'two']);
        $hydrator->hydrate($object, ['one' => 1, 'two' => 2]);
        
        $this->assertAttributeSame(100, 'one', $object);
        $this->assertAttributeSame(200, 'two', $object);
    }
    
    public function testExtractFromProperties()
    {
        $object = new OnlyPropertyStub(1, 2);
        
        $hydrator = new Hydrator(OnlyPropertyStub::class, ['one', 'two']);
        $data = $hydrator->extract($object);
        
        $this->assertSame($data, ['one' => 1, 'two' => 2]);
    }
    
    public function testExtractViaPropertyGetterMethods()
    {
        $object = new SetterAndGetterStub(1, 2);
        
        $hydrator = new Hydrator(SetterAndGetterStub::class, ['one', 'two']);
        $data = $hydrator->extract($object);
        
        $this->assertSame($data, ['one' => 100, 'two' => 200]);
    }
    
    public function testHydrateUsingSpecificMapping()
    {
        $object = new OnlyPropertyStub();
        
        $hydrator = new Hydrator(OnlyPropertyStub::class, ['first' => 'one', 'second' => 'two']);
        $hydrator->hydrate($object, ['first' => 1, 'second' => 2]);
        
        $this->assertAttributeSame(1, 'one', $object);
        $this->assertAttributeSame(2, 'two', $object);
    }
    
    public function testExctractUsingSpecificMapping()
    {
        $object = new OnlyPropertyStub(1, 2);
        
        $hydrator = new Hydrator(OnlyPropertyStub::class, ['first' => 'one', 'second' => 'two']);
        $data = $hydrator->extract($object);
        
        $this->assertSame($data, ['first' => 1, 'second' => 2]);
    }
    
    public function testHydrateUnvalidObjectThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Argument $object must be an instance of %s.', OnlyPropertyStub::class));
        
        $hydrator = new Hydrator(OnlyPropertyStub::class, []);
        $hydrator->hydrate(new \stdClass, []);
    }
    
    public function testHydrateNonExistingPropertyThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Class %s doesn\'t have property $three.', OnlyPropertyStub::class));
        
        $object = new OnlyPropertyStub();
        
        $hydrator = new Hydrator(OnlyPropertyStub::class, ['one', 'two', 'three']);
        $hydrator->hydrate($object, ['one' => 1, 'two' => 2, 'three' => 3]);
    }
    
    public function testExtractUnvalidObjectThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Argument $object must be an instance of %s.', OnlyPropertyStub::class));
        
        $hydrator = new Hydrator(OnlyPropertyStub::class, []);
        $hydrator->extract(new \stdClass);
    }
}
