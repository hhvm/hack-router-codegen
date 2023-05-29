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

final class CodegenTestCase extends \Facebook\HackTest\HackTest {
  public async function testCanCreateForTreeAsync(): Awaitable<void> {
    // Just test it parses and we can create an instance
    $codegen = await Codegen::forTreeAsync(__DIR__.'/examples/', shape());
    expect($codegen)->toBeInstanceOf(Codegen::class);
  }
}
