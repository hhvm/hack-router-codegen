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
 * @generated SignedSource<<12ea52f22c1529c36c81f13b567e7159>>
 */

final class MySiteRouter
  extends \Facebook\HackRouter\BaseRouter<classname<\Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController>> {

  public function getRoutes(
  ): ImmMap<\Facebook\HackRouter\HttpMethod, ImmMap<string, classname<\Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController>>> {
    $get = ImmMap {
      '/foo' =>
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
