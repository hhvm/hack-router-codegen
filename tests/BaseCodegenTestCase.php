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

use type Facebook\HackCodegen\{
  HackCodegenConfig,
  HackCodegenFactory,
};

abstract class BaseCodegenTestCase extends \PHPUnit\Framework\TestCase {
  protected function getCodegenConfig(): HackCodegenConfig {
    return (new HackCodegenConfig())
      ->withRootDir(\realpath(__DIR__.'/../'));
  }

  protected function getCodegenFactory(): HackCodegenFactory {
    return new HackCodegenFactory($this->getCodegenConfig());
  }
}
