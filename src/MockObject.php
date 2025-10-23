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
    $this->mock = $this->createClassMock($class, $methods);
    $this->addProperties($properties);
    $this->addMethods($methods);
  }

  protected function createClassMock($class, $methods)
  {
    $intClass = 'Mock_' . str_replace('\\', '_', $class) . '_' . uniqid();

    $classMethods = get_class_methods($class);
    $get = !in_array('__get', $classMethods) ? "public function __get(string \$name) {
        return \$this->properties[\$name] ?? null;
      }" : '';
    $set = !in_array('__set', $classMethods) ? "public function __set(string \$name, \$value): void {
        \$this->properties[\$name] = \$value;
      }" : '';

    eval("
      class $intClass extends \\$class {
        public \$properties = [];
        public function __construct() { }
        $get
        $set
        public function setProperty(string \$name, \$value): void {
          \$this->properties[\$name] = \$value;
        }
      }
    ");

    $this->mockBuilder = $this->getMockBuilder($intClass);
    $this->mockBuilder->disableOriginalConstructor();

    // list of methods on class
    $classMethods = get_class_methods($class);

    // // remove __construct, if it exists
    // if (($key = array_search('__construct', $classMethods)) !== false) {
    //   unset($classMethods[$key]);
    // }

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
