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

final class UriBuilderCodegenTest extends \PHPUnit_Framework_TestCase {
  use TestTypechecksTestTrait;

  const string CODEGEN_CLASS = 'GetRequestExampleControllerUriBuilder';
  const string CODEGEN_PATH = __DIR__.'/examples/codegen/'.
    self::CODEGEN_CLASS.'.php';

  private function getBuilder(): UriBuilderCodegenBuilder {
    return new UriBuilderCodegenBuilder(
      UriBuilderCodegenWithPath::class,
      UriParameterCodegenBuilder::class,
    );
  }

  protected function rebuild(): void {
    $this->getBuilder()->renderToFile(
      self::CODEGEN_PATH,
      GetRequestExampleController::class,
      /* ns = */ null,
      self::CODEGEN_CLASS,
    );
  }

  public function testRebuild(): void{
    $this->rebuild();
  }

  public function testCorrectUsage(): void {
    $path = (new \GetRequestExampleControllerUriBuilder())
      ->setMyString('some value')
      ->setMyInt(42)
      ->setMyEnum(CodeGen\Tests\MyEnum::HERP)
      ->getPath();
    $this->assertSame(
      '/some value/42/derp',
      $path,
    );
  }

  /**
   * @expectedException \HH\InvariantException
   * @expectedExceptionMessageRegExp /Parameter "[^"]+" must be set/
   */
  public function testThrowsIfUnsetParam(): void {
    (new \GetRequestExampleControllerUriBuilder())->getPath();
  }
}
