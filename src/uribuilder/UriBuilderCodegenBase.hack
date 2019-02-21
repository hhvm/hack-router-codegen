/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

abstract class UriBuilderCodegenBase<+T as UriBuilderBase> {
  abstract const classname<HasUriPattern> CONTROLLER;

  abstract protected static function createInnerBuilder(): T;

  final protected static function getParts(
  ): ImmVector<UriPatternPart> {
    $controller = static::CONTROLLER;
    return $controller::getUriPattern()->getParts();
  }
}
