<?hh // strict
/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

use type Facebook\DefinitionFinder\FileParser;
use type Facebook\DefinitionFinder\ScannedClass;
use type Facebook\HackRouter\HttpMethod;
use type Facebook\HackRouter\PrivateImpl\{ClassFacts,
  ControllerFacts
};

final class ControllerFactsTest extends \PHPUnit_Framework_TestCase {
  use InvokePrivateTestTrait;

  private function getFacts(
    FileParser $parser,
  ): ControllerFacts<IncludeInUriMap> {
    return new ControllerFacts(
      IncludeInUriMap::class,
      new ClassFacts($parser),
    );
  }

  private function isMappable(
    ControllerFacts<IncludeInUriMap> $facts,
    ScannedClass $class,
  ): bool {
    return (bool) $this->invokePrivate(
      $facts,
      'isUriMappable',
      $class,
    );
  }

  private function getMethods(
    ControllerFacts<IncludeInUriMap> $facts,
    ScannedClass $class,
  ): ImmSet<HttpMethod> {
    /* HH_IGNORE_ERROR[4110] mixed => ImmSet */
    return $this->invokePrivate(
      $facts,
      'getHttpMethodsForController',
      $class->getName(),
    );
  }

  public function testMappableDirectly(): void {
    $code =
      "<?hh\n".
      "final class MyController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $facts = $this->getFacts($scanned);
    $this->assertTrue($this->isMappable($facts, $class));
  }

  public function testMappableDirectlyFromNamespace(): void {
    $code =
      "<?hh\n".
      "namespace MySite;\n".
      "final class MyController\n".
      "implements \Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $class = $scanned->getClass('MySite\MyController');
    $facts = $this->getFacts($scanned);
    $this->assertTrue($this->isMappable($facts, $class));
  }

  public function testMappableDirectlyWithPrecedingBackSlash(): void {
    $code =
      "<?hh\n".
      "final class MyController\n".
      "implements \Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $facts = $this->getFacts($scanned);
    $this->assertTrue($this->isMappable($facts, $class));
  }

  public function testMappableDirectlyWithUsedInterface(): void {
    $code =
      "<?hh\n".
      "use \Facebook\HackRouter\IncludeInUriMap;\n".
      "final class MyController implements IncludeInUriMap {}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $facts = $this->getFacts($scanned);
    $this->assertTrue($this->isMappable($facts, $class));
  }

  public function testAbstractIsNotMappable(): void {
    $code =
      "<?hh\n".
      "abstract class MyController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $facts = $this->getFacts($scanned);
    $this->assertFalse($this->isMappable($facts, $class));
  }

  /**
   * @expectedException \HH\InvariantException
   * @expectedExceptionMessage MyController
   */
  public function testNoNonFinalNonAbstract(): void {
    $code =
      "<?hh\n".
      "class MyController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $facts = $this->getFacts($scanned);
    $_throws = $this->isMappable($facts, $class);
  }

  public function testMappableByParentClass(): void {
    $code =
      "<?hh\n".
      "abstract class BaseController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}\n".
      "final class MyController extends BaseController {}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $base = $scanned->getClass('BaseController');
    $final = $scanned->getClass('MyController');

    $facts = $this->getFacts($scanned);
    $this->assertTrue($this->isMappable($facts, $final));
    $this->assertFalse($this->isMappable($facts, $base));
  }

  public function testMappableByParentClassInNamespace(): void {
    $code =
      "<?hh\n".
      "namespace Foo\Bar;\n".
      "abstract class BaseController\n".
      "implements \Facebook\HackRouter\IncludeInUriMap {}\n".
      "final class MyController extends BaseController {}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $base = $scanned->getClass('Foo\\Bar\\BaseController');
    $final = $scanned->getClass('Foo\\Bar\\MyController');

    $facts = $this->getFacts($scanned);
    $this->assertTrue($this->isMappable($facts, $final));
    $this->assertFalse($this->isMappable($facts, $base));
  }

  public function testMappableByDerivedInterface(): void {
    $code =
      "<?hh\n".
      "interface IController\n".
      "extends Facebook\HackRouter\IncludeInUriMap {}\n".
      "final class MyController implements IController {}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $facts = $this->getFacts($scanned);
    $this->assertTrue($this->isMappable($facts, $class));
  }

  public function testMappableByTrait(): void {
    $code =
      "<?hh\n".
      "trait TController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}\n".
      "final class MyController {\n".
      "  use TController;\n".
      "}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $facts = $this->getFacts($scanned);
    $this->assertTrue($this->isMappable($facts, $class));
  }

  public function testGetController(): void {
    $code =
      "<?hh\n".
      "final class MyController implements\n".
      "\Facebook\HackRouter\IncludeInUriMap,\n".
      "\Facebook\HackRouter\SupportsGetRequests {\n".
      "}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $facts = $this->getFacts($scanned);
    $this->assertEquals(
      ImmSet { HttpMethod::GET },
      $this->getMethods($facts, $class),
    );
  }

  public function testPostController(): void {
    $code =
      "<?hh\n".
      "final class MyController implements\n".
      "\Facebook\HackRouter\IncludeInUriMap,\n".
      "\Facebook\HackRouter\SupportsPostRequests {\n".
      "}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $facts = $this->getFacts($scanned);
    $this->assertEquals(
      ImmSet { HttpMethod::POST },
      $this->getMethods($facts, $class),
    );
  }

  /**
   * @expectedException \HH\InvariantException
   * @expectedExceptionMessage multiple HTTP methods
   */
  public function testGetAndPostController(): void {
    $code =
      "<?hh\n".
      "final class MyController implements\n".
      "\Facebook\HackRouter\IncludeInUriMap,\n".
      "\Facebook\HackRouter\SupportsGetRequests,\n".
      "\Facebook\HackRouter\SupportsPostRequests {\n".
      "}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $facts = $this->getFacts($scanned);
    $_throws = $this->getMethods($facts, $class);
  }

  /**
   * @expectedException \HH\InvariantException
   * @expectedExceptionMessage but does not implement
   */
  public function testControllerWithNoSupportedMethods(): void {
    $code =
      "<?hh\n".
      "final class MyController implements\n".
      "\Facebook\HackRouter\IncludeInUriMap {\n".
      "}";
    $scanned = FileParser::fromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $facts = $this->getFacts($scanned);
    $_throws = $this->getMethods($facts, $class);
  }
}
