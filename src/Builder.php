<?php

namespace MockObject;

class Builder
{
  /**
   * @param  string $class             Name of class to mock the object from
   * @param  array  $args              Arguments passed up the chain from child methods ([$methods, $props])
   * @param  array  $defaultMethods    Methods set in base createX() method (in this class)
   * @param  array  $defaultProperties Properties set in base createX() method (in this class)
   * @return mixed
   */
  public function build($class, $args = [], $defaultMethods = [], $defaultProperties = [])
  {
    // methods and properties passed farther down the chain
    $methods = $args[0] ?? [];
    $properties = $args[1] ?? [];

    $mockBuilder = new MockObject(
      $class,
      array_merge($defaultMethods, $methods),
      array_merge($defaultProperties, $properties)
    );
    return $mockBuilder->getMockObject();
  }
}
