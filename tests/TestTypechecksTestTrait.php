<?hh // strict
/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace Facebook\HackRouter;

trait TestTypechecksTestTrait {
  require extends \PHPUnit_Framework_TestCase;

  abstract protected function rebuild(): void;

  final public function testTypechecks(): void {
    $this->rebuild();
    $exit_code = 0;
    $out_array = [];
    exec(
      'hh_client',
      &$out_array,
      &$exit_code,
    );
    $this->assertSame(0, $exit_code, "Typechecker errors found");
  }
}
