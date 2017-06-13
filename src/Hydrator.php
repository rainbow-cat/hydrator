<?php declare(strict_types=1);

namespace RainbowCat\Hydrator;

use ReflectionClass;
use ReflectionException;
use InvalidArgumentException;

class Hydrator
{
    /**
     * @var string
     */
    protected $className;
    
    /**
     * @var array
     */
    protected $mapping = [];
    
    /**
     * @var array
     */
    protected $reflectionProperties = [];
    
    /**
     * @var array
     */
    protected $propertySetters = [];
    
    /**
     * @var array
     */
    protected $propertyGetters = [];
    
    /**
     * (non-phpdoc)
     *
     * @param string $className 
     * @param array $mapping
     * @throws ReflectionException
     */
    public function __construct(string $className, array $mapping)
    {
        $this->className = $className;
        
        $this->fixMapping($mapping);
        $this->initialize(new ReflectionClass($className));
    }
    
    /**
     * (non-phpdoc)
     *
     * @param object $object 
     * @param array $data
     * @return object
     * @throws InvalidArgumentException
     */
    public function hydrate($object, array $data)
    {
        $this->validateObject($object);
        
        foreach ($this->mapping as $dataKey => $propertyName) {
            if (isset($data[$dataKey])) {
                if (isset($this->propertySetters[$propertyName])) {
                    $object->{$this->propertySetters[$propertyName]}($data[$dataKey]);
                } elseif (isset($this->reflectionProperties[$propertyName])) {
                    $this->reflectionProperties[$propertyName]->setValue($object, $data[$dataKey]);
                } else {
                    throw new InvalidArgumentException(sprintf('Class %s doesn\'t have property $%s.', $this->className, $propertyName));
                }
            }
        }
        
        return $object;
    }
    
    /**
     * (non-phpdoc)
     *
     * @param object $object 
     * @return array
     */
    public function extract($object): array
    {
        $this->validateObject($object);
        
        $data = [];
        
        foreach ($this->mapping as $dataKey => $propertyName) {
            if (isset($this->propertyGetters[$propertyName])) {
                $data[$dataKey] = $object->{$this->propertyGetters[$propertyName]}();
            } elseif (isset($this->reflectionProperties[$propertyName])) {
                $data[$dataKey] = $this->reflectionProperties[$propertyName]->getValue($object);
            }
        }
        
        return $data;
    }
    
    /**
     * (non-phpdoc)
     *
     * @param object $object 
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateObject($object)
    {
        if ( ! is_object($object) || $this->className !== get_class($object) ) {
            throw new InvalidArgumentException(sprintf('Argument $object must be an instance of %s.', $this->className));
        }
    }
    
    /**
     * (non-phpdoc)
     *
     * @param array $mapping
     * @return void
     */
    protected function fixMapping(array $mapping)
    {
        foreach ($mapping as $dataKey => $propertyName) {
            if (is_numeric($dataKey)) {
                $dataKey = $propertyName;
            }
            
            $this->mapping[$dataKey] = $propertyName;
        }
    }
    
    /**
     * (non-phpdoc)
     *
     * @param ReflectionClass $reflectionClass
     * @return void
     */
    protected function initialize(ReflectionClass $reflectionClass)
    {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ( ! $reflectionProperty->isStatic()) {
                $propertyName = $reflectionProperty->name;
                $ucPropertyName = ucfirst($propertyName);
                
                $reflectionProperty->setAccessible(true);
                $this->reflectionProperties[$propertyName] = $reflectionProperty;
                
                $propertySetterName = 'set'. $ucPropertyName;
                $propertyGetterName = 'get'. $ucPropertyName;
                
                if ($reflectionClass->hasMethod($propertySetterName)) {
                    $reflectionMethod = $reflectionClass->getMethod($propertySetterName);
                    
                    if ($reflectionMethod->isPublic() && ! $reflectionMethod->isAbstract() && ! $reflectionMethod->isStatic()) {
                        $this->propertySetters[$propertyName] = $propertySetterName;
                    }
                }
                
                if ($reflectionClass->hasMethod($propertyGetterName)) {
                    $reflectionMethod = $reflectionClass->getMethod($propertyGetterName);
                    
                    if ($reflectionMethod->isPublic() && ! $reflectionMethod->isAbstract() && ! $reflectionMethod->isStatic()) {
                        $this->propertyGetters[$propertyName] = $propertyGetterName;
                    }
                }
            }
        }
    }
}
