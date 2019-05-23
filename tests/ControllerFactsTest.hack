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
use function Facebook\FBExpect\expect;
use type Facebook\DefinitionFinder\ScannedClass;
use type Facebook\HackRouter\HttpMethod;
use type Facebook\HackRouter\PrivateImpl\{ClassFacts,
  ControllerFacts
};

final class ControllerFactsTest extends \Facebook\HackTest\HackTest {
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

  public async function testMappableDirectly(): Awaitable<void> {
    $code =
      "<?hh\n".
      "final class MyController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $facts = $this->getFacts($scanned);
    expect($this->isMappable($facts, $class))->toBeTrue();
  }

  public async function testMappableDirectlyFromNamespace(): Awaitable<void> {
    $code =
      "<?hh\n".
      "namespace MySite;\n".
      "final class MyController\n".
      "implements \Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
    $class = $scanned->getClass('MySite\MyController');
    $facts = $this->getFacts($scanned);
    expect($this->isMappable($facts, $class))->toBeTrue();
  }

  public async function testMappableDirectlyWithPrecedingBackSlash(): Awaitable<void> {
    $code =
      "<?hh\n".
      "final class MyController\n".
      "implements \Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $facts = $this->getFacts($scanned);
    expect($this->isMappable($facts, $class))->toBeTrue();
  }

  public async function testMappableDirectlyWithUsedInterface(): Awaitable<void> {
    $code =
      "<?hh\n".
      "use \Facebook\HackRouter\IncludeInUriMap;\n".
      "final class MyController implements IncludeInUriMap {}";
    $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $facts = $this->getFacts($scanned);
    expect($this->isMappable($facts, $class))->toBeTrue();
  }

  public async function testAbstractIsNotMappable(): Awaitable<void> {
    $code =
      "<?hh\n".
      "abstract class MyController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}";
    $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');
    $facts = $this->getFacts($scanned);
    expect($this->isMappable($facts, $class))->toBeFalse();
  }

  public async function testNoNonFinalNonAbstract(): Awaitable<void> {
    expect(async () ==> {
      $code = "<?hh\n".
        "class MyController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}";
      $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
      $class = $scanned->getClass('MyController');
      $facts = $this->getFacts($scanned);
      $_throws = $this->isMappable($facts, $class);
    })->toThrow(InvariantException::class);
  }

  public async function testMappableByParentClass(): Awaitable<void> {
    $code =
      "<?hh\n".
      "abstract class BaseController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}\n".
      "final class MyController extends BaseController {}";
    $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
    $base = $scanned->getClass('BaseController');
    $final = $scanned->getClass('MyController');

    $facts = $this->getFacts($scanned);
    expect($this->isMappable($facts, $final))->toBeTrue();
    expect($this->isMappable($facts, $base))->toBeFalse();
  }

  public async function testMappableByParentClassInNamespace(): Awaitable<void> {
    $code =
      "<?hh\n".
      "namespace Foo\Bar;\n".
      "abstract class BaseController\n".
      "implements \Facebook\HackRouter\IncludeInUriMap {}\n".
      "final class MyController extends BaseController {}";
    $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
    $base = $scanned->getClass('Foo\\Bar\\BaseController');
    $final = $scanned->getClass('Foo\\Bar\\MyController');

    $facts = $this->getFacts($scanned);
    expect($this->isMappable($facts, $final))->toBeTrue();
    expect($this->isMappable($facts, $base))->toBeFalse();
  }

  public async function testMappableByDerivedInterface(): Awaitable<void> {
    $code =
      "<?hh\n".
      "interface IController\n".
      "extends Facebook\HackRouter\IncludeInUriMap {}\n".
      "final class MyController implements IController {}";
    $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $facts = $this->getFacts($scanned);
    expect($this->isMappable($facts, $class))->toBeTrue();
  }

  public async function testMappableByTrait(): Awaitable<void> {
    $code =
      "<?hh\n".
      "trait TController\n".
      "implements Facebook\HackRouter\IncludeInUriMap {}\n".
      "final class MyController {\n".
      "  use TController;\n".
      "}";
    $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $facts = $this->getFacts($scanned);
    expect($this->isMappable($facts, $class))->toBeTrue();
  }

  public async function testGetController(): Awaitable<void> {
    $code =
      "<?hh\n".
      "final class MyController implements\n".
      "\Facebook\HackRouter\IncludeInUriMap,\n".
      "\Facebook\HackRouter\SupportsGetRequests {\n".
      "}";
    $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $facts = $this->getFacts($scanned);
    expect($this->getMethods($facts, $class))->toBePHPEqual(
      ImmSet { HttpMethod::GET },
    );
  }

  public async function testPostController(): Awaitable<void> {
    $code =
      "<?hh\n".
      "final class MyController implements\n".
      "\Facebook\HackRouter\IncludeInUriMap,\n".
      "\Facebook\HackRouter\SupportsPostRequests {\n".
      "}";
    $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
    $class = $scanned->getClass('MyController');

    $facts = $this->getFacts($scanned);
    expect($this->getMethods($facts, $class))->toBePHPEqual(
      ImmSet { HttpMethod::POST },
    );
  }

  public async function testGetAndPostController(): Awaitable<void> {
    expect(async () ==> {
      $code = "<?hh\n".
        "final class MyController implements\n".
      "\Facebook\HackRouter\IncludeInUriMap,\n".
      "\Facebook\HackRouter\SupportsGetRequests,\n".
      "\Facebook\HackRouter\SupportsPostRequests {\n".
      "}";
      $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
      $class = $scanned->getClass('MyController');
      $facts = $this->getFacts($scanned);
      $_throws = $this->getMethods($facts, $class);
    })->toThrow(InvariantException::class);
  }

  public async function testControllerWithNoSupportedMethods(): Awaitable<void> {
    expect(async () ==> {
      $code = "<?hh\n".
        "final class MyController implements\n".
      "\Facebook\HackRouter\IncludeInUriMap {\n".
      "}";
      $scanned = await FileParser::fromDataAsync($code, __FUNCTION__);
      $class = $scanned->getClass('MyController');
      $facts = $this->getFacts($scanned);
      $_throws = $this->getMethods($facts, $class);
    })->toThrow(InvariantException::class);
  }
}
