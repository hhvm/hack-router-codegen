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
use \Facebook\HackCodegen\HackBuilderValues;
use \Facebook\HackRouter\HttpMethod;
use \Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController;
use \Facebook\HackRouter\CodeGen\Tests\Generated\{
  GetRequestExampleControllerUriBuilder,
  GetRequestExampleControllerParameters
};
use \Facebook\HackRouter\CodeGen\Tests\WebController;
use namespace \Facebook\TypeAssert;

final class RequestParametersCodegenBuilderTest extends BaseCodegenTestCase {
  use TestTypechecksTestTrait;

  const string CODEGEN_CLASS = 'GetRequestExampleControllerParameters';
  const string CODEGEN_PATH = __DIR__.'/examples/codegen/'.
    self::CODEGEN_CLASS.'.php';

  private function getBuilder(
  ): RequestParametersCodegenBuilder<RequestParameters> {
    return (new RequestParametersCodegenBuilder(
      $this->getCodegenConfig(),
      (classname<HasUriPattern> $class) ==> {
        $class = TypeAssert\classname_of(
          WebController::class,
          $class,
        );
        return $class::__getParametersSpec();
      },
      '$this->__getParametersImpl()',
      RequestParametersCodegen::class,
      new RequestParameterCodegenBuilder($this->getCodegenConfig()),
    ))
    ->traitRequireExtends(
      \Facebook\HackRouter\CodeGen\Tests\WebController::class
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
          'method' => 'getParameters',
        ),
      ),
    );
  }

  private function getCodegenParametersForValues(
    ImmMap<string, string> $values,
  ): GetRequestExampleControllerParameters {
    $params = GetRequestExampleController::__getParametersSpec();
    return new GetRequestExampleControllerParameters(new RequestParameters(
      $params->filter($p ==> !$p['optional'])->map($p ==> $p['spec']),
      $params->filter($p ==> $p['optional'])->map($p ==> $p['spec']),
      $values,
    ));
  }

  public function testCanGetParameter(): void {
    $values = ImmMap {
      'MyString' => __FUNCTION__,
      'MyInt' => (string) __LINE__,
      'MyEnum' => (string) CodeGen\Tests\MyEnum::HERP,
    };
    $params = $this->getCodegenParametersForValues($values);
    $this->assertSame(__FUNCTION__, $params->get()['MyString']);
  }

  public function testCanGetOptionalParameter(): void {
    $values = ImmMap {
      'MyString' => __FUNCTION__,
      'MyInt' => (string) __LINE__,
      'MyEnum' => (string) CodeGen\Tests\MyEnum::HERP,
      'MyOptionalParam' => __FUNCTION__,
    };
    $params = $this->getCodegenParametersForValues($values);
    $this->assertSame(__FUNCTION__, $params->get()['MyOptionalParam']);
  }

  public function testGetNullForMissingOptionalParameter(): void {
    $values = ImmMap {
      'MyString' => __FUNCTION__,
      'MyInt' => (string) __LINE__,
      'MyEnum' => (string) CodeGen\Tests\MyEnum::HERP,
    };
    $params = $this->getCodegenParametersForValues($values);
    $this->assertSame(null, $params->get()['MyOptionalParam']);
  }
}
