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

final class CodegenTestCase extends \PHPUnit_Framework_TestCase {
  public function testCanCreateForTree(): void {
    // Just test it parses and we can create an instance
    $codegen = Codegen::forTree(__DIR__.'/examples/', shape());
    $this->assertInstanceOf(
      Codegen::class,
      $codegen,
    );
  }
}
