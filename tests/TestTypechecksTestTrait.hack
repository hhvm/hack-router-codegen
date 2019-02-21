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

  abstract protected function rebuild(): void;

  final public function testTypechecks(): void {
    $this->rebuild();
    $exit_code = 0;
    $out_array = [];
    \exec(
      'hh_client',
      &$out_array,
      &$exit_code,
    );
    expect($exit_code)->toBeSame(0, "Typechecker errors found");
  }
}
