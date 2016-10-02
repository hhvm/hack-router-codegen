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

abstract class SimpleParameterCodegenSpec extends UriParameterCodegenSpec {
  const type TSimpleSpec = shape(
    'type' => string,
    'accessorSuffix' => string,
  );
  abstract protected static function getSimpleSpec(): self::TSimpleSpec;

  final public static function getGetterSpec(
    RequestParameter $_,
  ): self::TSpec {
    $spec = static::getSimpleSpec();
    return shape(
      'type' => $spec['type'],
      'accessorSuffix' => $spec['accessorSuffix'],
      'args' => ImmVector {
        Args::ParameterName(),
      },
    );
  }

  public static function getSetterSpec(
    UriParameter $_,
  ): self::TSpec {
    $spec = static::getSimpleSpec();
    return shape(
      'type' => $spec['type'],
      'accessorSuffix' => $spec['accessorSuffix'],
      'args' => ImmVector {
        Args::ParameterName(),
        Args::ParameterValue(),
      },
    );
  }
}
