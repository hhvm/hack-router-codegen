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

use \Facebook\HackRouter\PrivateImpl\ControllerFacts;

final class UriMapBuilder<TBase as IncludeInUriMap> {

  public function __construct(
    private ControllerFacts<TBase> $controllerFacts,
  ) {
  }

  public function getUriMap(
  ): ImmMap<HttpMethod, ImmMap<string, classname<TBase>>> {
    $map = Map { };
    foreach (HttpMethod::getValues() as $method) {
      $map[$method] = Map { };
    }

    $controllers = $this->controllerFacts->getControllers();
    foreach ($controllers as $controller => $methods) {
      $path = $controller::getUriPattern()->getFastRouteFragment();
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

    foreach ($map as $submap) {
      natsort(&$submap);
    }

    return $map
      ->filter($submap ==> !$submap->isEmpty())
      ->map($submap ==> $submap->immutable())
      ->immutable();
  }
}
