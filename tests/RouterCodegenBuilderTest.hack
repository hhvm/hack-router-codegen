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
use type Facebook\HackRouter\CodeGen\Tests\{
  GetRequestExampleController,
  MyEnum,
};
use type Facebook\HackRouter\CodeGen\Tests\Generated\MySiteRouter;
use type Facebook\HackRouter\PrivateImpl\{ClassFacts, ControllerFacts};
use namespace HH\Lib\Str;

final class RouterCodegenBuilderTest extends BaseCodegenTestCase {
  use InvokePrivateTestTrait;
  use TestTypechecksTestTrait;

  const string CODEGEN_PATH = __DIR__.'/examples/codegen/MySiteRouter.php';
  const string CODEGEN_NS = "Facebook\\HackRouter\\CodeGen\\Tests\\Generated";

  private async function getBuilderAsync(
  ): Awaitable<RouterCodegenBuilder<GetRequestExampleController>> {
    $parser = await FileParser::fromFileAsync(
      __DIR__.'/examples/GetRequestExampleController.hack',
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

  protected async function rebuildAsync(): Awaitable<void> {
    $builder = await $this->getBuilderAsync();
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
    $class =
      expect($class)->toBeInstanceOf(\Facebook\HackCodegen\CodegenFile::class);
    return $class->render();
  }

  public async function testMapOnlyContainsUsedMethods(): Awaitable<void> {
    $code = $this->renderToString(await $this->getBuilderAsync());
    expect($code)->toContainSubstring('HttpMethod::GET');
    expect($code)->toNotContainSubstring('HttpMethod::POST');
  }

  public async function testDefaultGeneratedFrom(): Awaitable<void> {
    $code = $this->renderToString(await $this->getBuilderAsync());
    expect($code)->toContainSubstring('To re-generate this file run');
    expect($code)->toContainSubstring('vendor/hhvm/hacktest/bin/hacktest');
  }

  public async function testOverriddenGeneratedFrom(): Awaitable<void> {
    $code = $this->renderToString(
      (await $this->getBuilderAsync())->setGeneratedFrom(
        $this->getCodegenFactory()->codegenGeneratedFromClass(self::class),
      ),
    );
    expect($code)
      ->toContainSubstring('Generated from '.RouterCodegenBuilder::class);
  }

  public async function testCreatesFinalByDefault(): Awaitable<void> {
    $code = $this->renderToString(await $this->getBuilderAsync());
    $parser = await FileParser::fromDataAsync($code);
    $class = $parser->getClass(MySiteRouter::class);
    expect($class->isFinal())->toBeTrue('should be final');
    expect($class->isAbstract())->toBeFalse('should not be abstract');
  }

  public async function testCanCreateAbstract(): Awaitable<void> {
    $code = $this->renderToString(
      (await $this->getBuilderAsync())->setCreateAbstractClass(true),
    );
    $parser = await FileParser::fromDataAsync($code);
    $class = $parser->getClass(MySiteRouter::class);
    expect($class->isAbstract())->toBeTrue('should be abstract');
    expect($class->isFinal())->toBeFalse('should not be final');
  }

  public async function testIsStrict(): Awaitable<void> {
    expect(
      Str\starts_with(
        $this->renderToString(await $this->getBuilderAsync()),
        "<?hh // strict\n",
      ),
    )->toBeTrue();
  }

  public async function testSuccessfullyMaps(): Awaitable<void> {
    await $this->rebuildAsync();
    /* HH_IGNORE_ERROR[1002] intentionally using require_once outside of
     * top-level */
    require_once(self::CODEGEN_PATH);
    $router = new MySiteRouter();
    list($controller, $params) =
      $router->routeMethodAndPath(HttpMethod::GET, '/foo/123/derp');
    expect($controller)->toBeSame(GetRequestExampleController::class);
    $params = new RequestParameters(
      GetRequestExampleController::getUriPattern()->getParameters(),
      vec[],
      $params,
    );
    expect($params->getString('MyString'))->toBeSame('foo');
    expect($params->getInt('MyInt'))->toBeSame(123);
    expect($params->getEnum(MyEnum::class, 'MyEnum'))->toBeSame(MyEnum::HERP);
  }
}
