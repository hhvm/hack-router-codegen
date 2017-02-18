<?hh // strict
/*
 *  Copyright (c) 2016-present, Facebook, Inc.
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

final class UriBuilderCodegenBuilderTest extends BaseCodegenTestCase {
  use TestTypechecksTestTrait;

  const string CODEGEN_CLASS = 'GetRequestExampleControllerUriBuilder';
  const string CODEGEN_PATH = __DIR__.'/examples/codegen/'.
    self::CODEGEN_CLASS.'.php';

  private function getBuilder(): UriBuilderCodegenBuilder<UriBuilder> {
    return new UriBuilderCodegenBuilder(
      $this->getCodegenConfig(),
      UriBuilderCodegen::class,
      new RequestParameterCodegenBuilder($this->getCodegenConfig()),
      'getPath',
      'string',
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
          'method' => 'getPath',
        ),
      ),
    );
  }

  public function testUriBuilderClass(
  ): void {
    $path = GetRequestExampleControllerUriBuilder::getPath(shape(
      'MyString' => 'some value',
      'MyInt' => 42,
      'MyEnum' => CodeGen\Tests\MyEnum::HERP,
    ));
    $this->assertSame(
      '/some value/42/derp',
      $path,
    );
  }

  public function testUriBuilderTrait(
  ): void {
    $path = GetRequestExampleController::getPath(shape(
      'MyString' => 'some value',
      'MyInt' => 42,
      'MyEnum' => CodeGen\Tests\MyEnum::HERP,
    ));
    $this->assertSame(
      '/some value/42/derp',
      $path,
    );
  }
}
