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

namespace Facebook\HackRouter;

use \Facebook\DefinitionFinder\BaseParser;
use \Facebook\DefinitionFinder\ScannedBasicClass;
use \Facebook\DefinitionFinder\ScannedClass;
use \Facebook\DefinitionFinder\DefinitionType;

final class UriMapBuilder<TBase as IncludeInUriMap> {
  private ImmMap<string, ScannedClass> $classes;

  public function __construct(
    private classname<TBase> $baseClass,
    BaseParser $parser,
  ) {
    $classes = Map { };

    foreach ($parser->getClasses() as $class) {
      $classes[$class->getName()] = $class;
    }
    foreach ($parser->getInterfaces() as $interface) {
      $classes[$interface->getName()] = $interface;
    }
    foreach ($parser->getTraits() as $trait) {
      $classes[$trait->getName()] = $trait;
    }
    $this->classes = $classes->immutable();
  }

  public function getUriMap(
  ): ImmMap<HttpMethod, ImmMap<string, classname<TBase>>> {
    $map = Map { };
    foreach (HttpMethod::getValues() as $method) {
      $map[$method] = Map { };
    }

    foreach ($this->getControllerNames() as $controller) {
      $path = $controller::getUriPattern()->getFastRouteFragment();
      $methods = $this->getSupportedHttpMethodsForController($controller);
      foreach ($methods as $method) {
        invariant(
          !$map[$method]->containsKey($path),
          "Duplicate entry for path '%s': '%s' and '%s'",
          $path,
          $map[$method]->at($path),
          $controller,
        );
        $map[$method][$path] = $controller;
      }
    }

    return $map->map($submap ==> $submap->immutable())->immutable();
  }

  private function getControllerNames(
  ): ImmSet<classname<TBase>> {
    $mappable = Set { };
    foreach ($this->classes as $class) {
      if (!$this->isUriMappable($class)) {
        continue;
      }
      $mappable[] = $class->getName();
    }
    /* HH_IGNORE_ERROR[4110] it really is a classname<T> :) */
    return $mappable->immutable();
  }

  <<TestsBypassVisibility>>
  private function getSupportedHttpMethodsForController(
    classname<TBase> $classname,
  ): ImmSet<HttpMethod> {
    $supported = Set { };
    if ($this->doesImplement($classname, SupportsGetRequests::class)) {
      $supported[] = HttpMethod::GET;
    }
    if ($this->doesImplement($classname, SupportsPostRequests::class)) {
      $supported[] = HttpMethod::POST;
    }

    invariant(
      !$supported->isEmpty(),
      '%s implements %s, but does not implement %s or %s',
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

  <<TestsBypassVisibility>>
  private function isUriMappable(
    ScannedClass $class
  ): bool {
    if (!$class instanceof ScannedBasicClass) {
      return false;
    }
    if ($class->isAbstract()) {
      return false;
    }

    if (!$this->doesImplement($class->getName(), IncludeInUriMap::class)) {
      return false;
    }

    // This is also me being opinionated.
    invariant(
      $class->isFinal(),
      'Classes implementing IncludeInUriMap should be abstract or final',
    );
    return true;
  }

  <<__Memoize>>
  private function doesImplement<T>(
    string $name,
    classname<T> $wanted,
  ): bool {
    if (substr($name, 0, 1) === "\\") {
      $name = substr($name, 1);
    }

    if ($name === $wanted) {
      return true;
    }
    $class = $this->classes[($name)] ?? null;
    if (!$class) {
      return false;
    }

    foreach ($class->getInterfaceNames() as $interface) {
      if ($this->doesImplement($interface, $wanted)) {
        return true;
      }
    }

    $parent = $class->getParentClassName();
    if ($parent !== null && $this->doesImplement($parent, $wanted)) {
      return true;
    }

    return false;
  }
}
