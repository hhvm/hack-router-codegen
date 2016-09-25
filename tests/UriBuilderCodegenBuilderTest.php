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
use \Facebook\HackRouter\CodeGen\Tests\Generated\GetRequestExampleControllerUriBuilder;

final class UriBuilderCodegenTest extends \PHPUnit_Framework_TestCase {
  use TestTypechecksTestTrait;

  const string CODEGEN_CLASS = 'GetRequestExampleControllerUriBuilder';
  const string CODEGEN_PATH = __DIR__.'/examples/codegen/'.
    self::CODEGEN_CLASS.'.php';

  private function getBuilder(): UriBuilderCodegenBuilder<UriBuilder> {
    return new UriBuilderCodegenBuilder(
      UriBuilderCodegen::class,
      RequestParameterCodegenBuilder::class,
    );
  }

  protected function rebuild(): void {
    $this->getBuilder()->renderToFile(
      self::CODEGEN_PATH,
      shape(
        'controller' => GetRequestExampleController::class,
        'namespace' => "Facebook\\HackRouter\\CodeGen\\Tests\\Generated",
        'class' => shape(
          'name' => self::CODEGEN_CLASS,
        ),
        'trait' => shape(
          'name' => self::CODEGEN_CLASS.'Trait',
          'method' => 'getUriBuilder',
        ),
      ),
    );
  }

  public function testRebuild(): void{
    $this->rebuild();
  }

  private function assertBuilderWorks(
    GetRequestExampleControllerUriBuilder $builder,
  ): void {
    $path = $builder
      ->setMyString('some value')
      ->setMyInt(42)
      ->setMyEnum(CodeGen\Tests\MyEnum::HERP)
      ->getPath();
    $this->assertSame(
      '/some value/42/derp',
      $path,
    );
  }

  public function testUriBuilderClass(): void {
    $this->assertBuilderWorks(new GetRequestExampleControllerUriBuilder());
  }

  public function testUriBuilderTrait(): void {
    $builder = GetRequestExampleController::getUriBuilder();
    $this->assertInstanceOf(
      GetRequestExampleControllerUriBuilder::class,
      $builder,
    );
    $this->assertBuilderWorks($builder);
  }

  /**
   * @expectedException \HH\InvariantException
   * @expectedExceptionMessageRegExp /Parameter "[^"]+" must be set/
   */
  public function testThrowsIfUnsetParam(): void {
    (new GetRequestExampleControllerUriBuilder())->getPath();
  }
}
