/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;


final class StringParameterCodegenSpec extends SimpleParameterCodegenSpec {
  <<__Override>>
  protected static function getSimpleSpec(): self::TSimpleSpec {
    return shape(
      'type' => 'string',
      'accessorSuffix' => 'String',
    );
  }
}
