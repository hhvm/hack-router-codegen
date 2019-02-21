/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

trait InvokePrivateTestTrait {
  require extends \Facebook\HackTest\HackTest;
  final protected function invokePrivate<T>(
    T $object,
    string $method,
    /* HH_FIXME[4033] when support for HHVM < 3.13 is dropped: mixed ...$args*/
    ...$args
  ): mixed {
    $rm = new \ReflectionMethod($object, $method);
    invariant(
      $rm->getAttribute('TestsBypassVisibility') !== null,
      '%s::%s does not have <<TestsBypassVisibility>>',
      \get_class($object),
      $method,
    );
    $rm->setAccessible(true);
    return $rm->invokeArgs($object, $args);
  }
}
