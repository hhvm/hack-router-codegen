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

use Facebook\HackCodegen\{
  HackCodegenConfig,
  HackCodegenFactory,
  IHackCodegenConfig
};

abstract class BaseCodegenTestCase extends \PHPUnit\Framework\TestCase {
  protected function getCodegenConfig(): HackCodegenConfig {
    return new HackCodegenConfig(realpath(__DIR__.'/../'));
  }

  protected function getCodegenFactory(): HackCodegenFactory {
    return new HackCodegenFactory($this->getCodegenConfig());
  }
}
