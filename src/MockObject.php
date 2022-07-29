<?php

namespace MockObjectHelper;

use PHPUnit\Framework\TestCase;

class MockObject extends TestCase
{
  /**
   * Mock object
   * @var
   */
  public $mock;

  protected $mockBuilder;

  public function __construct($class, $methods = [], $properties = [])
  {
    $this->mock = !interface_exists($class) ?
      // class
      $this->createClassMock($class, $methods) :
      // interface
      $this->getMockForAbstractClass($class);

    $this->addProperties($properties);
    $this->addMethods($methods);
  }

  protected function createClassMock($class, $methods)
  {
    $this->mockBuilder = $this->getMockBuilder($class);
    $this->mockBuilder->disableOriginalConstructor();

    // list of methods on class
    $classMethods = get_class_methods($class);
    array_shift($classMethods);

    $methodsToAdd = array_keys($methods);

    // list of methods that we're trying to mock that aren't present on the object
    $add = array_diff($methodsToAdd, $classMethods);
    if (!empty($add)) {
      $this->mockBuilder->addMethods($add);
    }

    // methods we're trying to mock that are already present on the object
    $only = array_diff($methodsToAdd, $add);
    if (!empty($only)) {
      $this->mockBuilder->onlyMethods($only);
    }

    return $this->mockBuilder->getMock();
  }

  protected function createInterfaceMock($class)
  {
    return $this->getMockForAbstractClass($class);
  }

  public function addMethods($methods)
  {
    foreach ($methods as $method => $will) {

      if (\is_callable($will)) {
        // if callable, give the callable the scope of $this->mock
        // and wrap it in returnCallback. Allows callbacks to have
        // access to the mock object (as $this->mock), if needed
        $will = $this->returnCallback($will->bindTo($this));
      }

      $this->mock->expects($this->any())
        ->method($method)
        ->will($will);
    }

    return $this;
  }

  public function addProperties($properties)
  {
    foreach ($properties as $key => $value) {
      $this->mock->$key = $value;
    }

    return $this;
  }

  public function getMockObject()
  {
    return $this->mock;
  }
}
