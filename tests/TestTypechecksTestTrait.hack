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

trait TestTypechecksTestTrait {
  require extends \Facebook\HackTest\HackTest;

  abstract protected function rebuildAsync(): Awaitable<void>;

  final public async function testTypechecks(): Awaitable<void> {
    await $this->rebuildAsync();
    $exit_code = 0;
    $out_array = vec[];
    \exec(
      'hh_client',
      inout $out_array,
      inout $exit_code,
    );
    expect($exit_code)->toBeSame(0, 'Typechecker errors found');
  }
}
