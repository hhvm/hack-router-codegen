<?hh // strict
/*
 *  Copyright (c) 2016, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace Facebook\HackRouter;

abstract class UriBuilderCodegenBase<T as UriBuilderBase> {
  abstract const classname<HasUriPattern> CONTROLLER;

  abstract protected static function createInnerBuilder(): T;

  <<__Memoize>>
  final protected function getBuilder(): T {
    return static::createInnerBuilder();
  }

  final protected static function getParts(
  ): ImmVector<UriPatternPart> {
    $controller = static::CONTROLLER;
    return $controller::getUriPattern()->getParts();
  }
}
