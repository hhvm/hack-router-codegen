<?hh // strict
/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

use type \Facebook\HackRouter\UriParameterCodegenArgumentSpec as Args;

final class EnumParameterCodegenSpec extends UriParameterCodegenSpec {
  private static function cast<T>(
    RequestParameter $param,
  ): EnumRequestParameter<T> {
    invariant(
      $param instanceof EnumRequestParameter,
      'Expected %s to be an enum parameter, got %s',
      $param->getName(),
      \get_class($param),
    );
    return $param;
  }

  private static function getType(
    RequestParameter $param,
  ): string {
    return "\\".self::cast($param)->getEnumName();
  }

  private static function getTypeName(
    RequestParameter $param,
  ): string {
    return self::getType($param).'::class';
  }

  <<__Override>>
  final public static function getGetterSpec(
    RequestParameter $param,
  ): self::TSpec {
    return shape(
      'type' => self::getType($param),
      'accessorSuffix' => 'Enum',
      'args' => ImmVector {
        Args::Custom(($_, $_) ==> self::getTypeName($param)),
        Args::ParameterName(),
      },
    );
  }

  <<__Override>>
  public static function getSetterSpec(
    UriParameter $param,
  ): self::TSpec {
    $param = self::cast($param);
    return shape(
      'type' => self::getType($param),
      'accessorSuffix' => 'Enum',
      'args' => ImmVector {
        Args::Custom(($_, $_) ==> self::getTypeName($param)),
        Args::ParameterName(),
        Args::ParameterValue(),
      },
    );
  }
}
