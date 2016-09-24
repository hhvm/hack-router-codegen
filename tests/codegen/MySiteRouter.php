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

/**
 * This file is generated. Do not modify it manually!
 *
 * Generated from Facebook\HackRouter\RouterCodegenBuilder
 *
 *
 * @generated SignedSource<<367f2b61b8581193df78d8e9b2b71921>>
 */

final class MySiteRouter
  extends \Facebook\HackRouter\BaseRouter<classname<\Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController>> {

  public function getRoutes(
  ): ImmMap<\Facebook\HackRouter\HttpMethod, ImmMap<string, classname<\Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController>>> {
    $get = ImmMap {
      '/users/{user_name}' =>
        \Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController::class,
    };
    $post = ImmMap {
    };
    return ImmMap {
      \Facebook\HackRouter\HttpMethod::GET => $get,
      \Facebook\HackRouter\HttpMethod::POST => $post,
    };
  }
}
