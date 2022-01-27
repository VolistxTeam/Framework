<?php

namespace App\DataTransferObjects;

use ReflectionClass;
use ReflectionProperty;

abstract class DataTransferObjectBase
{
    protected $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;

        $class = new ReflectionClass(static::class);

        $parameters = $entity->toArray();

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty){
            $property = $reflectionProperty->getName();
            $this->{$property} = $parameters[$property];
        }
    }

}