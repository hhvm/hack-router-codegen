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
use \Facebook\HackRouter\CodeGen\Tests\Generated\{
  GetRequestExampleControllerUriBuilder,
  GetRequestExampleControllerParameters
};
use \Facebook\HackRouter\CodeGen\Tests\WebController;
use \FredEmmott\TypeAssert\TypeAssert;

use \Facebook\HackCodegen as cg;

final class RequestParametersCodegenBuilderTest extends \PHPUnit_Framework_TestCase {
  use TestTypechecksTestTrait;

  const string CODEGEN_CLASS = 'GetRequestExampleControllerParameters';
  const string CODEGEN_PATH = __DIR__.'/examples/codegen/'.
    self::CODEGEN_CLASS.'.php';

  private function getBuilder(
  ): RequestParametersCodegenBuilder<RequestParameters> {
    return (new RequestParametersCodegenBuilder(
      (classname<HasUriPattern> $class) ==> {
        $class = TypeAssert::isClassnameOf(
          WebController::class,
          $class,
        );
        return $class::__getParametersSpec();
      },
      $spec ==> (cg\hack_builder()
        ->addAssignment(
          '$params',
          '$this->__getParametersImpl()',
        )
        ->addReturn(
          'new %s($params)',
          $spec['class']['name'],
        )
        ->getCode()
      ),
      RequestParametersCodegen::class,
      RequestParameterCodegenBuilder::class,
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
    $values = ImmMap { 'MyString' => __FUNCTION__ };
    $params = $this->getCodegenParametersForValues($values);
    $this->assertSame(__FUNCTION__, $params->getMyString());
  }

  public function testCanGetOptionalParameter(): void {
    $values = ImmMap { 'MyOptionalParam' => __FUNCTION__ };
    $params = $this->getCodegenParametersForValues($values);
    $this->assertSame(__FUNCTION__, $params->getMyOptionalParam());
  }

  public function testGetNullForMissingOptionalParameter(): void {
    $values = ImmMap { };
    $params = $this->getCodegenParametersForValues($values);
    $this->assertSame(null, $params->getMyOptionalParam());
  }
}
