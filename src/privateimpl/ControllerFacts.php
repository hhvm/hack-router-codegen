<?hh // strict
/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter\PrivateImpl;

use type Facebook\DefinitionFinder\ScannedClass;
use type Facebook\DefinitionFinder\ScannedClassish;
use type Facebook\HackRouter\{
  HttpMethod,
  IncludeInUriMap,
  SupportsGetRequests,
  SupportsPostRequests
};

final class ControllerFacts<T as IncludeInUriMap> {
  public function __construct(
    private classname<T> $baseClass,
    private ClassFacts $classFacts,
  ) {
  }

  public function getControllers(
  ): ImmMap<classname<T>, ImmSet<HttpMethod>> {
    $controllers = Map { };
    $subclasses = $this->classFacts->getSubclassesOf($this->baseClass);
    foreach ($subclasses as $name => $def) {
      if (!$this->isUriMappable($def)) {
        continue;
      }
      $controllers[$name] = $this->getHttpMethodsForController($name);
    }
    return $controllers->immutable();
  }

  <<TestsBypassVisibility>>
  private function isUriMappable(
    ScannedClassish $class
  ): bool {
    if (!$class instanceof ScannedClass) {
      return false;
    }
    if ($class->isAbstract()) {
      return false;
    }

    $cf = $this->classFacts;
    if (!$cf->doesImplement(IncludeInUriMap::class, $class->getName())) {
      return false;
    }

    // This is also me being opinionated.
    invariant(
      $class->isFinal(),
      'Classes implementing IncludeInUriMap should be abstract or final; '.
      '%s is neither',
      $class->getName(),
    );
    return true;
  }

  <<TestsBypassVisibility>>
  private function getHttpMethodsForController(
    classname<T> $classname,
  ): ImmSet<HttpMethod> {
    $supported = Set { };
    $cf = $this->classFacts;
    if ($cf->doesImplement(SupportsGetRequests::class, $classname)) {
      $supported[] = HttpMethod::GET;
    }
    if ($cf->doesImplement(SupportsPostRequests::class, $classname)) {
      $supported[] = HttpMethod::POST;
    }

    invariant(
      !$supported->isEmpty(),
      '%s implements %s, but does not implement %s or %s',
      $classname,
      IncludeInUriMap::class,
      SupportsGetRequests::class,
      SupportsPostRequests::class,
    );

    /* This is me being opinionated, not a technical limitation:
     *
     * I think each controller should do one thing. Multiple HTTP methods
     * implies it does multiple things.
     *
     * Returning a set instead of a single method so it's easy to change
     * if someone convinces me that this is a bad idea.
     */
    invariant(
      $supported->count() === 1,
      '%s is marked as supporting multiple HTTP methods; build 1 controller '.
      'per method instead, refactoring common code out (eg to a trait).',
      $classname,
    );

    return $supported->immutable();
  }
}
