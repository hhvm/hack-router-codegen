/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter\CodeGen\Tests;

use type Facebook\HackRouter\{
  UriPattern,
  RequestParameter,
  RequestParameters,
  StringRequestParameter,
  StringRequestParameterSlashes
};

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

  final public static function __getParametersSpec(
  ): ImmVector<shape('spec' => RequestParameter, 'optional' => bool)> {
    $params = static::getUriPattern()->getParameters()->map(
      $param ==> shape('spec' => $param, 'optional' => false),
    )->toVector();
    $params[] = shape(
      'spec' => new StringRequestParameter(
        StringRequestParameterSlashes::WITHOUT_SLASHES,
        'MyOptionalParam',
      ),
      'optional' => true,
    );
    return $params->immutable();
  }
}

final class GetRequestExampleController extends WebController {
  use Generated\GetRequestExampleControllerUriBuilderTrait;
  use Generated\GetRequestExampleControllerParametersTrait;

  <<__Override>>
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
