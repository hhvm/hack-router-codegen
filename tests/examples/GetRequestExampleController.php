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

namespace Facebook\HackRouter\CodeGen\Tests;

use Facebook\HackRouter\UriPattern;

final class GetRequestExampleController implements
\Facebook\HackRouter\IncludeInUriMap,
\Facebook\HackRouter\SupportsGetRequests {
  public static function getUriPattern(): UriPattern {
    return (new UriPattern())
      ->literal('/users/')
      ->string('user_name');
  }
}
