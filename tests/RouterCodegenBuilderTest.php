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
use \Facebook\HackRouter\HttpMethod;
use \Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController;

final class RouterCodegenBuilderTest extends \PHPUnit_Framework_TestCase {
  use InvokePrivateTestTrait;

  <<__Memoize>>
  private function getBuilder(
  ): RouterCodegenBuilder<GetRequestExampleController> {
    $scanned = FileParser::FromFile(
      __DIR__.'/examples/GetRequestExampleController.php',
    );
    $uri_map_builder = new UriMapBuilder(
      GetRequestExampleController::class,
      $scanned,
    );
    $router_builder = new RouterCodegenBuilder(
      GetRequestExampleController::class,
      $uri_map_builder->getUriMap(),
    );
    return $router_builder;
  }

  private function getRenderedString(
    ?string $namespace,
    string $classname,
    string $filename,
  ): string {
    $builder = $this->getBuilder();
    $class = $this->invokePrivate(
      $builder,
      'getCodegenFile',
      $namespace,
      $classname,
      $filename,
    );
    assert($class instanceof \Facebook\HackCodegen\CodegenFile);
    return $class->render();
  }

  public function testDump(): void {
    print($this->getRenderedString(
      /* ns = */ 'Foo\Bar',
      'MySiteRouter',
      'MySiteRouter.php',
    ));
  }
}
