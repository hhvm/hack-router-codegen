<?hh // strict
/*
 *  Copyright (c) 2016, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace Facebook\HackRouter;

use \Facebook\DefinitionFinder\FileParser;
use \Facebook\DefinitionFinder\ScannedBasicClass;
use \Facebook\HackRouter\HttpMethod;
use \Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController;

final class UriMapBuilderTest extends \PHPUnit_Framework_TestCase {
  private function invokePrivate<T>(
    T $object,
    string $method,
    mixed ...$args
  ): mixed {
    $rm = new \ReflectionMethod($object, $method);
    invariant(
      $rm->getAttribute('TestsBypassVisibility') !== null,
      '%s::%s does not have <<TestsBypassVisibility>>',
      get_class($object),
      $method,
    );
    $rm->setAccessible(true);
    return $rm->invokeArgs($object, $args);
  }

  private function isMappable(
    UriMapBuilder $builder,
    ScannedBasicClass $class,
  ): bool {
    return (bool) $this->invokePrivate(
      $builder,
      'isUriMappable',
      $class,
    );
  }

  private function getMethods(
    UriMapBuilder $builder,
    ScannedBasicClass $class,
  ): ImmSet<HttpMethod> {
    /* HH_IGNORE_ERROR[4110] mixed => ImmSet */
    return $this->invokePrivate(
      $builder,
      'getSupportedHttpMethodsForController',
      $class->getName(),
    );
  }

  public function testMappableDirectly(): void {
    $code =
      "<?hh\n".
      "final class MyController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $builder = new UriMapBuilder($scanned);
    $this->assertTrue($this->isMappable($builder, $class));
  }

  public function testMappableDirectlyFromNamespace(): void {
    $code =
      "<?hh\n".
      "namespace MySite;\n".
      "final class MyController\n".
      "implements \Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MySite\MyController');
    $builder = new UriMapBuilder($scanned);
    $this->assertTrue($this->isMappable($builder, $class));
  }

  public function testMappableDirectlyWithPrecedingBackSlash(): void {
    $code =
      "<?hh\n".
      "final class MyController\n".
      "implements \Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $builder = new UriMapBuilder($scanned);
    $this->assertTrue($this->isMappable($builder, $class));
  }

  public function testMappableDirectlyWithUsedInterface(): void {
    $code =
      "<?hh\n".
      "use \Facebook\HackRouter\IncludeInUriMap;\n".
      "final class MyController implements IncludeInUriMap {}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $builder = new UriMapBuilder($scanned);
    $this->assertTrue($this->isMappable($builder, $class));
  }

  public function testAbstractIsNotMappable(): void {
    $code =
      "<?hh\n".
      "abstract class MyController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $builder = new UriMapBuilder($scanned);
    $this->assertFalse($this->isMappable($builder, $class));
  }

  /**
   * @expectedException \HH\InvariantException
   */
  public function testNoNonFinalNonAbstract(): void {
    $code =
      "<?hh\n".
      "class MyController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $builder = new UriMapBuilder($scanned);
    $_throws = $this->isMappable($builder, $class);
  }

  public function testMappableByParentClass(): void {
    $code =
      "<?hh\n".
      "abstract class BaseController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}\n".
      "final class MyController extends BaseController {}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $base = $scanned->getClass('BaseController');
    $final = $scanned->getClass('MyController');

    $builder = new UriMapBuilder($scanned);
    $this->assertTrue($this->isMappable($builder, $final));
    $this->assertFalse($this->isMappable($builder, $base));
  }

  public function testMappableByDerivedInterface(): void {
    $code =
      "<?hh\n".
      "interface IController\n".
      "extends Facebook\HackRouter\IncludeInUriMap {}\n".
      "final class MyController implements IController {}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $builder = new UriMapBuilder($scanned);
    $this->assertTrue($this->isMappable($builder, $class));
  }

  public function testMappableByTrait(): void {
    $this->markTestSkipped(
      'https://github.com/fredemmott/definition-finder/issues/26',
    );

    $code =
      "<?hh\n".
      "trait TController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}\n".
      "final class MyController {\n".
      "  use TController;\n".
      "}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $builder = new UriMapBuilder($scanned);
    $this->assertTrue($this->isMappable($builder, $class));
  }

  public function testGetController(): void {
    $code =
      "<?hh\n".
      "final class MyController implements\n".
      "\Facebook\HackRouter\IncludeInUriMap,\n".
      "\Facebook\HackRouter\SupportsGetRequests {\n".
      "}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $builder = new UriMapBuilder($scanned);
    $this->assertEquals(
      ImmSet { HttpMethod::GET },
      $this->getMethods($builder, $class),
    );
  }

  public function testPostController(): void {
    $code =
      "<?hh\n".
      "final class MyController implements\n".
      "\Facebook\HackRouter\IncludeInUriMap,\n".
      "\Facebook\HackRouter\SupportsPostRequests {\n".
      "}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $builder = new UriMapBuilder($scanned);
    $this->assertEquals(
      ImmSet { HttpMethod::POST },
      $this->getMethods($builder, $class),
    );
  }

  /**
   * @expectedException \HH\InvariantException
   */
  public function testGetAndPostController(): void {
    $code =
      "<?hh\n".
      "final class MyController implements\n".
      "\Facebook\HackRouter\IncludeInUriMap,\n".
      "\Facebook\HackRouter\SupportsGetRequests,\n".
      "\Facebook\HackRouter\SupportsPostRequests {\n".
      "}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $builder = new UriMapBuilder($scanned);
    $_throws = $this->getMethods($builder, $class);
  }

  public function testCreatesRoutes(): void {
    $scanned = FileParser::FromFile(
      __DIR__.'/examples/GetRequestExampleController.php',
    );
    $class = $scanned->getClass(GetRequestExampleController::class);
    $builder = new UriMapBuilder($scanned);

    $this->assertEquals(
      ImmMap {
        HttpMethod::GET => ImmMap {
          '/foo' => GetRequestExampleController::class,
        },
        HttpMethod::POST => ImmMap {},
      },
      $builder->getUriMap(),
    );
  }
}
