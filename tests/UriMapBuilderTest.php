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
use function Facebook\FBExpect\expect;
use type Facebook\HackRouter\HttpMethod;
use type Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController;
use type Facebook\HackRouter\PrivateImpl\{ClassFacts,
  ControllerFacts
};

final class UriMapBuilderTest extends \Facebook\HackTest\HackTest {
  use InvokePrivateTestTrait;

  private function getBuilder(
    FileParser $parser,
  ): UriMapBuilder<IncludeInUriMap> {
    return new UriMapBuilder(new ControllerFacts(
      IncludeInUriMap::class,
      new ClassFacts($parser),
    ));
  }

  public function testCreatesRoutes(): void {
    $scanned = FileParser::fromFile(
      __DIR__.'/examples/GetRequestExampleController.php',
    );
    $class = $scanned->getClass(GetRequestExampleController::class);
    $builder = $this->getBuilder($scanned);

    expect($builder->getUriMap()[HttpMethod::GET]->values())->toBePHPEqual(
      ImmVector { GetRequestExampleController::class },
    );
  }

  public function testNoMapForUnusedMethods(): void {
    $scanned = FileParser::fromFile(
      __DIR__.'/examples/GetRequestExampleController.php',
    );
    $class = $scanned->getClass(GetRequestExampleController::class);
    $builder = $this->getBuilder($scanned);
    $map = $builder->getUriMap();
    expect($map->containsKey(HttpMethod::POST))->toBeFalse(
      'No POST controllers, should be no POST key',
    );
  }
}
