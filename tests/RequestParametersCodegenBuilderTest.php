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

use function Facebook\FBExpect\expect;
use type Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController;
use type Facebook\HackRouter\CodeGen\Tests\Generated\{
  GetRequestExampleControllerParameters
};
use type Facebook\HackRouter\CodeGen\Tests\WebController;
use namespace Facebook\TypeAssert;

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
      'MyInt' => (string)__LINE__,
      'MyEnum' => (string)CodeGen\Tests\MyEnum::HERP,
    };
    $params = $this->getCodegenParametersForValues($values);
    expect($params->get()['MyString'])->toBeSame(__FUNCTION__);
  }

  public function testCanGetOptionalParameter(): void {
    $values = ImmMap {
      'MyString' => __FUNCTION__,
      'MyInt' => (string)__LINE__,
      'MyEnum' => (string)CodeGen\Tests\MyEnum::HERP,
      'MyOptionalParam' => __FUNCTION__,
    };
    $params = $this->getCodegenParametersForValues($values);
    expect($params->get()['MyOptionalParam'])->toBeSame(__FUNCTION__);
  }

  public function testGetNullForMissingOptionalParameter(): void {
    $values = ImmMap {
      'MyString' => __FUNCTION__,
      'MyInt' => (string)__LINE__,
      'MyEnum' => (string)CodeGen\Tests\MyEnum::HERP,
    };
    $params = $this->getCodegenParametersForValues($values);
    expect($params->get()['MyOptionalParam'])->toBeSame(null);
  }
}
