/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

use type Facebook\HackRouter\CodeGen\Tests\Generated\MySiteRouter;
use function Facebook\FBExpect\expect;
use type Facebook\HackRouter\CodeGen\Tests\{
  GetRequestExampleController,
  MyEnum,
};

final class RouterCLILookupCodegenBuilderTest extends BaseCodegenTestCase {
  use TestTypechecksTestTrait;

  const string CODEGEN_PATH = __DIR__.'/examples/codegen/lookup-path.php';
  const string CODEGEN_NS = "Facebook\\HackRouter\\CodeGen\\Tests\\Generated";

  protected async function rebuildAsync(): Awaitable<void> {
    (
      new RouterCLILookupCodegenBuilder($this->getCodegenConfig())
    )->renderToFile(
      self::CODEGEN_PATH,
      self::CODEGEN_NS,
      MySiteRouter::class,
      'MySiteRouterCLILookup',
    );
  }

  public async function testCanLookupExampleController(): Awaitable<void> {
    await $this->rebuildAsync();

    $path = GetRequestExampleController::getPath(shape(
      'MyString' => 'foo',
      'MyInt' => 123,
      'MyEnum' => MyEnum::FOO,
    ));

    $exit_code = null;
    $output = vec[];
    \exec(
      \vsprintf(
        '%s -d hhvm.jit=0 %s %s',
        (
          ImmSet {
            \PHP_BINARY,
            self::CODEGEN_PATH,
            $path,
          }
        )->map(\escapeshellarg<>),
      ),
      inout $output,
      inout $exit_code,
    );
    $output = \implode("\n", $output);
    expect($exit_code)->toBeSame(0);
    expect($output)->toMatchRegExp('/^HEAD:.+GetRequestExampleController$/m');
    expect($output)->toMatchRegExp('/^GET:.+GetRequestExampleController$/m');
  }

  public async function testCantLookupInvalidPath(): Awaitable<void> {
    await $this->rebuildAsync();

    $exit_code = 0;
    $output = vec[];
    \exec(
      \vsprintf(
        '%s -d hhvm.jit=0 %s /foo/bar',
        (
          ImmSet {
            \PHP_BINARY,
            self::CODEGEN_PATH,
          }
        )->map(\escapeshellarg<>),
      ),
      inout $output,
      inout $exit_code,
    );
    $output = \implode("\n", $output);
    expect($exit_code)->toBeGreaterThan(0);
    expect($output)->toNotContainSubstring('HEAD');
    expect($output)->toNotContainSubstring('GET');
    // Brittle - don't care about this string, just that there's a friendly
    // error message rather than a hack error, exception, etc
    expect($output)->toContainSubstring('No controller found');
  }
}
