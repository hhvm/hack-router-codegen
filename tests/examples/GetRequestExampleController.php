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
use Facebook\HackRouter\RequestParameters;

enum MyEnum: string {
  FOO = 'bar';
  HERP = 'derp';
}

abstract class WebController
implements
\Facebook\HackRouter\IncludeInUriMap,
\Facebook\HackRouter\SupportsGetRequests {
  public function __construct(
    private RequestParameters $parameters,
  ) {
  }

  final protected function __getParametersImpl(): RequestParameters {
    return $this->parameters;
  }
}

final class GetRequestExampleController extends WebController {
  use Generated\GetRequestExampleControllerUriBuilderTrait;
  use Generated\GetRequestExampleControllerParametersTrait;

  public static function getUriPattern(): UriPattern {
    return (new UriPattern())
      ->literal('/')
      ->string('MyString')
      ->literal('/')
      ->int('MyInt')
      ->literal('/')
      ->enum(MyEnum::class, 'MyEnum');
  }
}
