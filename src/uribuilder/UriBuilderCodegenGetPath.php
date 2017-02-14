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

trait UriBuilderCodegenGetPath<T as UriBuilderWithPath>{
  require extends UriBuilderCodegenBase<T>;

  final public function getPath(): string {
    return $this->getBuilder()->getPath();
  }
}
