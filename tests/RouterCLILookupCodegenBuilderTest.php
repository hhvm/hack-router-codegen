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

use type \Facebook\HackRouter\CodeGen\Tests\Generated\MySiteRouter;
use type \Facebook\HackRouter\CodeGen\Tests\{
  GetRequestExampleController,
  MyEnum
};

final class RouterCLILookupCodegenBuilderTest extends BaseCodegenTestCase {
  use TestTypechecksTestTrait;

  const string CODEGEN_PATH = __DIR__.'/examples/codegen/lookup-path.php';
  const string CODEGEN_NS =
    "Facebook\\HackRouter\\CodeGen\\Tests\\Generated";

  protected function rebuild(): void {
    (new RouterCLILookupCodegenBuilder(
      $this->getCodegenConfig(),
    ))->renderToFile(
      self::CODEGEN_PATH,
      self::CODEGEN_NS,
      MySiteRouter::class,
      'MySiteRouterCLILookup',
    );
  }

  public function testCanLookupExampleController(): void {
    $this->rebuild();

    $path = GetRequestExampleController::getPath(shape(
      'MyString' => 'foo',
      'MyInt' => 123,
      'MyEnum' => MyEnum::FOO,
    ));

    $exit_code = null;
    $output = [];
    \exec(
      \vsprintf(
        '%s -d hhvm.jit=0 %s %s',
        (ImmSet {
          \PHP_BINARY,
          self::CODEGEN_PATH,
          $path,
        })->map($x ==> \escapeshellarg($x)),
      ),
      &$output,
      &$exit_code,
    );
    $output = \implode("\n", $output);
    $this->assertSame(0, $exit_code);
    $this->assertRegExp(
      '/^HEAD:.+GetRequestExampleController$/m',
      $output,
    );
    $this->assertRegExp(
      '/^GET:.+GetRequestExampleController$/m',
      $output,
    );
  }

  public function testCantLookupInvalidPath(): void {
    $this->rebuild();

    $exit_code = 0;
    $output = [];
    \exec(
      \vsprintf(
        '%s -d hhvm.jit=0 %s /foo/bar',
        (ImmSet {
          \PHP_BINARY,
          self::CODEGEN_PATH,
        })->map($x ==> \escapeshellarg($x)),
      ),
      &$output,
      &$exit_code,
    );
    $output = \implode("\n", $output);
    $this->assertGreaterThan(0, $exit_code);
    $this->assertNotContains('HEAD', $output);
    $this->assertNotContains('GET', $output);
    // Brittle - don't care about this string, just that there's a friendly
    // error message rather than a hack error, exception, etc
    $this->assertContains('No controller found', $output);
  }
}
