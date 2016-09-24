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

  public function testTypechecks(): void {
    $path = __DIR__.'/codegen/MySiteRouter.php';
    $builder = $this->getBuilder();
    $builder->renderToFile(
      $path,
      /* ns = */ null,
      'MySiteRouter',
    );
    $args = ImmVector {
      'hh_server',
      '--check',
      __DIR__.'/../',
    };
    $exit_code = 0;
    $out_array = [];
    exec(
      implode(' ', $args->map($x ==> escapeshellarg($x))),
      $out_array,
      $exit_code,
    );
    $this->assertSame(0, $exit_code, "Typechecker errors found");
  }

  public function testMapOnlyContainsUsedMethods(): void {
    $builder = $this->getBuilder();
    $class = $this->invokePrivate(
      $builder,
      'getCodegenFile',
      'MySiteRouter.php',
      /* namespace = */ null,
      'MySiteRouter',
    );
    assert($class instanceof \Facebook\HackCodegen\CodegenFile);
    $code = $class->render();
    $this->assertContains('HttpMethod::GET', $code);
    $this->assertNotContains('HttpMethod::POST', $code);
  }
}
