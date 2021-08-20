<?php

namespace MockObjectHelper;

class MockObject extends \PHPUnit\Framework\TestCase
{
  /**
   * Mock object
   * @var
   */
  public $mock;

  public function __construct($class, $methods = [], $properties = [])
  {
    $this->mock = $this->getMockBuilder($class)
      ->disableOriginalConstructor()
      ->setMethods(array_keys($methods))
      ->getMock();

    $this->addProperties($properties);
    $this->addMethods($methods);
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
