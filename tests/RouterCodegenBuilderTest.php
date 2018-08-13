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
use type Facebook\DefinitionFinder\FileParser;
use type Facebook\HackRouter\CodeGen\Tests\{
  GetRequestExampleController,
  MyEnum
};
use type Facebook\HackRouter\CodeGen\Tests\Generated\MySiteRouter;
use type Facebook\HackRouter\PrivateImpl\{ClassFacts, ControllerFacts};

final class RouterCodegenBuilderTest extends BaseCodegenTestCase {
  use InvokePrivateTestTrait;
  use TestTypechecksTestTrait;

  const string CODEGEN_PATH = __DIR__.'/examples/codegen/MySiteRouter.php';
  const string CODEGEN_NS = "Facebook\\HackRouter\\CodeGen\\Tests\\Generated";

  <<__Memoize>>
  private function getBuilder(
  ): RouterCodegenBuilder<GetRequestExampleController> {
    $parser = FileParser::fromFile(
      __DIR__.'/examples/GetRequestExampleController.php',
    );
    $uri_map_builder = new UriMapBuilder(new ControllerFacts(
      GetRequestExampleController::class,
      new ClassFacts($parser),
    ));
    $router_builder = new RouterCodegenBuilder(
      $this->getCodegenConfig(),
      GetRequestExampleController::class,
      $uri_map_builder->getUriMap(),
    );
    return $router_builder;
  }

  protected function rebuild(): void {
    $builder = $this->getBuilder();
    $builder->renderToFile(
      self::CODEGEN_PATH,
      self::CODEGEN_NS,
      'MySiteRouter',
    );
  }

  private function renderToString<T as IncludeInUriMap>(
    RouterCodegenBuilder<T> $builder,
  ): string {
    $class = $this->invokePrivate(
      $builder,
      'getCodegenFile',
      self::CODEGEN_PATH,
      self::CODEGEN_NS,
      'MySiteRouter',
    );
    assert($class instanceof \Facebook\HackCodegen\CodegenFile);
    return $class->render();
  }

  public function testMapOnlyContainsUsedMethods(): void {
    $code = $this->renderToString($this->getBuilder());
    expect($code)->toContain('HttpMethod::GET');
    expect($code)->toNotContain('HttpMethod::POST');
  }

  public function testDefaultGeneratedFrom(): void {
    $code = $this->renderToString($this->getBuilder());
    expect($code)->toContain('To re-generate this file run');
    expect($code)->toContain('vendor/phpunit/phpunit/phpunit');
  }

  public function testOverriddenGeneratedFrom(): void {
    $code = $this->renderToString(
      $this->getBuilder()->setGeneratedFrom(
        $this->getCodegenFactory()->codegenGeneratedFromClass(self::class),
      ),
    );
    expect($code)->toContain('Generated from '.RouterCodegenBuilder::class);
  }

  public function testCreatesFinalByDefault(): void {
    $code = $this->renderToString($this->getBuilder());
    $parser = FileParser::fromData($code);
    $class = $parser->getClass(MySiteRouter::class);
    expect($class->isFinal())->toBeTrue('should be final');
    expect($class->isAbstract())->toBeFalse('should not be abstract');
  }

  public function testCanCreateAbstract(): void {
    $code = $this->renderToString(
      $this->getBuilder()->setCreateAbstractClass(true));
    $parser = FileParser::fromData($code);
    $class = $parser->getClass(MySiteRouter::class);
    expect($class->isAbstract())->toBeTrue('should be abstract');
    expect($class->isFinal())->toBeFalse('should not be final');
  }

  public function testIsStrict(): void {
    $this->assertStringStartsWith(
      "<?hh // strict\n",
      $this->renderToString($this->getBuilder()),
    );
  }

  public function testSuccessfullyMaps(): void {
    $this->rebuild();
    /* HH_IGNORE_ERROR[1002] intentionally using require_once outside of
     * top-level */
    require_once(self::CODEGEN_PATH);
    $router = new MySiteRouter();
    list($controller, $params) = $router->routeRequest(
      HttpMethod::GET,
      '/foo/123/derp',
    );
    expect($controller)->toBeSame(GetRequestExampleController::class);
    $params = new RequestParameters(
      GetRequestExampleController::getUriPattern()->getParameters(),
      [],
      $params,
    );
    expect($params->getString('MyString'))->toBeSame('foo');
    expect($params->getInt('MyInt'))->toBeSame(123);
    expect($params->getEnum(MyEnum::class, 'MyEnum'))->toBeSame(MyEnum::HERP);
  }
}
