/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

abstract class UriParameterCodegenSpec extends RequestParameterCodegenSpec {
  abstract public static function getSetterSpec(
    UriParameter $param,
  ): self::TSpec;
}
