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

use \Facebook\HackCodegen as cg;

final class UriParameterCodegenArgumentSpec {
  const type TRenderer = (function(RequestParameter,?string):string);

  private function __construct(
    private self::TRenderer $renderer,
  ) {
  }

  public function render(
    RequestParameter $param,
    ?string $value_variable = null,
  ): string {
    $renderer = $this->renderer;
    return $renderer($param, $value_variable);
  }

  public static function ParameterName(): this {
    return new self(
      ($param, $_value) ==> cg\hack_builder()
        ->addVarExport($param->getName())
        ->getCode(),
    );
  }

  public static function ParameterValue(): this {
    return new self(
      ($_param, $value) ==> {
        invariant(
          $value !== null,
          '%s should never be used for getters',
          __FUNCTION__,
        );
        return $value;
      },
    );
  }

  public static function Custom(self::TRenderer $renderer): this {
    return new self($renderer);
  }
}
