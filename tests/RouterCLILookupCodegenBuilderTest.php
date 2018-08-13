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
use type Facebook\HackRouter\CodeGen\Tests\Generated\MySiteRouter;
use type Facebook\HackRouter\CodeGen\Tests\{
  GetRequestExampleController,
  MyEnum
};

final class RouterCLILookupCodegenBuilderTest extends BaseCodegenTestCase {
  use TestTypechecksTestTrait;

  const string CODEGEN_PATH = __DIR__.'/examples/codegen/lookup-path.php';
  const string CODEGEN_NS = "Facebook\\HackRouter\\CodeGen\\Tests\\Generated";

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
    expect($exit_code)->toBeSame(0);
    expect($output)->toMatchRegExp('/^HEAD:.+GetRequestExampleController$/m');
    expect($output)->toMatchRegExp('/^GET:.+GetRequestExampleController$/m');
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
    expect($exit_code)->toBeGreaterThan(0);
    expect($output)->toNotContain('HEAD');
    expect($output)->toNotContain('GET');
    // Brittle - don't care about this string, just that there's a friendly
    // error message rather than a hack error, exception, etc
    expect($output)->toContain('No controller found');
  }
}
