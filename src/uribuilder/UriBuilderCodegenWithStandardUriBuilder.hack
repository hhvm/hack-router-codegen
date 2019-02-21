/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

abstract class UriBuilderCodegenWithStandardUriBuilder
extends UriBuilderCodegenBase<UriBuilder> {
  <<__Override>>
  final protected static function createInnerBuilder(): UriBuilder {
    return new UriBuilder(static::getParts());
  }
}
