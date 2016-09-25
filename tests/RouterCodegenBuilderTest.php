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
use \Facebook\HackRouter\CodeGen\Tests\MyEnum;

final class RouterCodegenBuilderTest extends \PHPUnit_Framework_TestCase {
  const string CODEGEN_PATH = __DIR__.'/examples/codegen/MySiteRouter.php';
  use InvokePrivateTestTrait;
  use TestTypechecksTestTrait;

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

  protected function rebuild(): void {
    $builder = $this->getBuilder();
    $builder->renderToFile(
      self::CODEGEN_PATH,
      /* ns = */ null,
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
      /* namespace = */ null,
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
        \Facebook\HackCodegen\codegen_generated_from_class(self::class)
      ),
    );
    $this->assertContains(
      'Generated from '.RouterCodegenBuilder::class,
      $code,
    );
  }

  public function testCreatesFinalByDefault(): void {
    $code = $this->renderToString($this->getBuilder());
    $parser = FileParser::FromData($code);
    $class = $parser->getClass('MySiteRouter');
    $this->assertTrue($class->isFinal(), 'should be final');
    $this->assertFalse($class->isAbstract(), 'should not be abstract');
  }

  public function testCanCreateAbstract(): void {
    $code = $this->renderToString(
      $this->getBuilder()->setCreateAbstractClass(true),
    );
    $parser = FileParser::FromData($code);
    $class = $parser->getClass('MySiteRouter');
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
    $router = new \MySiteRouter();
    list($controller, $params) = $router->routeRequest(
      HttpMethod::GET,
      '/foo/123/derp',
    );
    $this->assertSame(GetRequestExampleController::class, $controller);
    $params = new UriParameters(
      GetRequestExampleController::getUriPattern()->getParameters(),
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

  public function testCreateForTree(): void {
    $builder = RouterCodegenBuilder::createForTree(
      GetRequestExampleController::class,
      __DIR__.'/examples/',
    );
    $code = $this->renderToString($builder);

    $parser = FileParser::FromData($code);
    $this->assertNotNull($parser->getClass('MySiteRouter'));
    $this->assertContains(GetRequestExampleController::class, $code);
  }
}
