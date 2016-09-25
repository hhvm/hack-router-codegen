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

abstract class UriSimpleParameterCodegenSpec extends UriParameterCodegenSpec {
  const type TSimpleSpec = shape(
    'type' => string,
    'getter' => string,
    'setter' => string,
  );
  abstract protected static function getSimpleSpec(): self::TSimpleSpec;

  final public static function getGetterSpec(
    UriPatternParameter $_,
  ): self::TSpec {
    $spec = static::getSimpleSpec();
    return shape(
      'type' => $spec['type'],
      'method' => $spec['getter'],
      'args' => ImmVector {
        Args::ParameterName(),
      },
    );
  }

  public static function getSetterSpec(
    UriPatternParameter $_,
  ): self::TSpec {
    $spec = static::getSimpleSpec();
    return shape(
      'type' => $spec['type'],
      'method' => $spec['setter'],
      'args' => ImmVector {
        Args::ParameterName(),
        Args::ParameterValue(),
      },
    );
  }
}
