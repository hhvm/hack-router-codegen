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
  use InvokePrivateTestTrait;

  private function getBuilder(
    FileParser $parser,
  ): UriMapBuilder<IncludeInUriMap> {
    return new UriMapBuilder(IncludeInUriMap::class, $parser);
  }

  private function isMappable<T as IncludeInUriMap>(
    UriMapBuilder<T> $builder,
    ScannedBasicClass $class,
  ): bool {
    return (bool) $this->invokePrivate(
      $builder,
      'isUriMappable',
      $class,
    );
  }

  private function getMethods<T as IncludeInUriMap>(
    UriMapBuilder<T> $builder,
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
    $builder = $this->getBuilder($scanned);
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
    $builder = $this->getBuilder($scanned);
    $this->assertTrue($this->isMappable($builder, $class));
  }

  public function testMappableDirectlyWithPrecedingBackSlash(): void {
    $code =
      "<?hh\n".
      "final class MyController\n".
      "implements \Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $builder = $this->getBuilder($scanned);
    $this->assertTrue($this->isMappable($builder, $class));
  }

  public function testMappableDirectlyWithUsedInterface(): void {
    $code =
      "<?hh\n".
      "use \Facebook\HackRouter\IncludeInUriMap;\n".
      "final class MyController implements IncludeInUriMap {}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $builder = $this->getBuilder($scanned);
    $this->assertTrue($this->isMappable($builder, $class));
  }

  public function testAbstractIsNotMappable(): void {
    $code =
      "<?hh\n".
      "abstract class MyController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $builder = $this->getBuilder($scanned);
    $this->assertFalse($this->isMappable($builder, $class));
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
    $scanned = FileParser::FromData($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $builder = $this->getBuilder($scanned);
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

    $builder = $this->getBuilder($scanned);
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

    $builder = $this->getBuilder($scanned);
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

    $builder = $this->getBuilder($scanned);
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

    $builder = $this->getBuilder($scanned);
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

    $builder = $this->getBuilder($scanned);
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

    $builder = $this->getBuilder($scanned);
    $_throws = $this->getMethods($builder, $class);
  }

  public function testCreatesRoutes(): void {
    $scanned = FileParser::FromFile(
      __DIR__.'/examples/GetRequestExampleController.php',
    );
    $class = $scanned->getClass(GetRequestExampleController::class);
    $builder = $this->getBuilder($scanned);

    $this->assertEquals(
      ImmMap {
        HttpMethod::GET => ImmMap {
          '/users/{user_name}' => GetRequestExampleController::class,
        },
      },
      $builder->getUriMap(),
    );
  }

  public function testGetUriMapForTree(): void {
    $map = UriMapBuilder::getUriMapForTree(
      GetRequestExampleController::class,
      __DIR__.'/examples',
    );
    $this->assertEquals(
      ImmMap {
        HttpMethod::GET => ImmMap {
          '/users/{user_name}' => GetRequestExampleController::class,
        },
      },
      $map,
    );
  }

  public function testNoMapForUnusedMethods(): void {
    $scanned = FileParser::FromFile(
      __DIR__.'/examples/GetRequestExampleController.php',
    );
    $class = $scanned->getClass(GetRequestExampleController::class);
    $builder = $this->getBuilder($scanned);
    $map = $builder->getUriMap();
    $this->assertFalse(
      $map->containsKey(HttpMethod::POST),
      'No POST controllers, should be no POST key',
    );
  }
}
