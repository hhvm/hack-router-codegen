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

use type \Facebook\DefinitionFinder\FileParser;
use type \Facebook\HackRouter\CodeGen\Tests\{
  GetRequestExampleController,
  MyEnum
};
use type \Facebook\HackRouter\CodeGen\Tests\Generated\MySiteRouter;
use type \Facebook\HackRouter\PrivateImpl\{ClassFacts,
  ControllerFacts
};

final class RouterCodegenBuilderTest extends BaseCodegenTestCase {
  use InvokePrivateTestTrait;
  use TestTypechecksTestTrait;

  const string CODEGEN_PATH = __DIR__.'/examples/codegen/MySiteRouter.php';
  const string CODEGEN_NS =
    "Facebook\\HackRouter\\CodeGen\\Tests\\Generated";

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
    $this->assertContains('HttpMethod::GET', $code);
    $this->assertNotContains('HttpMethod::POST', $code);
  }

  public function testDefaultGeneratedFrom(): void {
    $code = $this->renderToString($this->getBuilder());
    $this->assertContains('To re-generate this file run', $code);
    $this->assertContains('vendor/phpunit/phpunit/phpunit', $code);
  }

  public function testOverriddenGeneratedFrom(): void {
    $code = $this->renderToString(
      $this->getBuilder()->setGeneratedFrom(
        $this->getCodegenFactory()->codegenGeneratedFromClass(self::class),
      ),
    );
    $this->assertContains(
      'Generated from '.RouterCodegenBuilder::class,
      $code,
    );
  }

  public function testCreatesFinalByDefault(): void {
    $code = $this->renderToString($this->getBuilder());
    $parser = FileParser::fromData($code);
    $class = $parser->getClass(MySiteRouter::class);
    $this->assertTrue($class->isFinal(), 'should be final');
    $this->assertFalse($class->isAbstract(), 'should not be abstract');
  }

  public function testCanCreateAbstract(): void {
    $code = $this->renderToString(
      $this->getBuilder()->setCreateAbstractClass(true),
    );
    $parser = FileParser::fromData($code);
    $class = $parser->getClass(MySiteRouter::class);
    $this->assertTrue($class->isAbstract(), 'should be abstract');
    $this->assertFalse($class->isFinal(), 'should not be final');
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
    $this->assertSame(GetRequestExampleController::class, $controller);
    $params = new RequestParameters(
      GetRequestExampleController::getUriPattern()->getParameters(),
      [],
      $params,
    );
    $this->assertSame(
      'foo',
      $params->getString('MyString'),
    );
    $this->assertSame(
      123,
      $params->getInt('MyInt'),
    );
    $this->assertSame(
      MyEnum::HERP,
      $params->getEnum(MyEnum::class, 'MyEnum'),
    );
  }
}
