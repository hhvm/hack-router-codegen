<?hh //strict
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

use \Facebook\HackRouter\UriParameterCodegenArgumentSpec as Args;

final class UriEnumParameterCodegenSpec extends UriParameterCodegenSpec {
  private static function cast<T>(
    UriPatternParameter $param,
  ): UriPatternEnumParameter<T> {
    invariant(
      $param instanceof UriPatternEnumParameter,
      'Expected %s to be an enum parameter, got %s',
      $param->getName(),
      get_class($param),
    );
    return $param;
  }

  private static function getType(
    UriPatternParameter $param,
  ): string {
    return "\\".self::cast($param)->getEnumName();
  }

  private static function getTypeName(
    UriPatternParameter $param,
  ): string {
    return self::getType($param).'::class';
  }

  final public static function getGetterSpec(
    UriPatternParameter $param,
  ): self::TSpec {
    return shape(
      'type' => self::getType($param),
      'method' => 'getEnum',
      'args' => ImmVector {
        Args::Custom(($_, $_) ==> self::getTypeName($param)),
        Args::ParameterName(),
      },
    );
  }

  public static function getSetterSpec(
    UriPatternParameter $param,
  ): self::TSpec {
    $param = self::cast($param);
    return shape(
      'type' => self::getType($param),
      'method' => 'setEnum',
      'args' => ImmVector {
        Args::Custom(($_, $_) ==> self::getTypeName($param)),
        Args::ParameterName(),
        Args::ParameterValue(),
      },
    );
  }
}
