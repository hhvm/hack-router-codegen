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

use type Facebook\DefinitionFinder\BaseParser;
use type Facebook\DefinitionFinder\ScannedBasicClass;
use type Facebook\DefinitionFinder\ScannedClass;

final class ClassFacts {
  private ImmMap<string, ScannedClass> $classes;

  public function __construct(
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

  public function getSubclassesOf<T>(
    classname<T> $wanted,
  ): ImmMap<classname<T>, ScannedBasicClass> {
    $mappable = Map { };
    foreach ($this->classes as $class) {
      if (!$class instanceof ScannedBasicClass) {
        continue;
      }
      $name = $this->asClassname($wanted, $class->getName());
      if ($name === null) {
        continue;
      }
      $mappable[$name] = $class;
    }
    return $mappable->immutable();
  }

  public function asClassname<T>(
    classname<T> $wanted,
    string $name,
  ): ?classname<T> {
    if ($this->doesImplement($wanted, $name)) {
      /* HH_IGNORE_ERROR[4110] */
      return $name;
    }
    return null;
  }

  <<__Memoize>>
  public function doesImplement<T>(
    classname<T> $wanted,
    string $name,
  ): bool {
    if (\substr($name, 0, 1) === "\\") {
      $name = \substr($name, 1);
    }

    if ($name === $wanted) {
      return true;
    }
    $class = $this->classes[($name)] ?? null;
    if (!$class) {
      return false;
    }

    foreach ($class->getInterfaceNames() as $interface) {
      if ($this->doesImplement($wanted, $interface)) {
        return true;
      }
    }

    foreach ($class->getTraitNames() as $trait) {
      if ($this->doesImplement($wanted, $trait)) {
        return true;
      }
    }

    $parent = $class->getParentClassName();
    if ($parent !== null && $this->doesImplement($wanted, $parent)) {
      return true;
    }

    return false;
  }
}
