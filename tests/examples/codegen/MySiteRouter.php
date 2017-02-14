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

/**
 * This file is generated. Do not modify it manually!
 *
 * To re-generate this file run
 * /root/hackdev/hack-router-codegen/vendor/phpunit/phpunit/phpunit
 *
 *
 * @generated SignedSource<<dbf6edd330440635568742b64289624c>>
 */
namespace Facebook\HackRouter\CodeGen\Tests\Generated;

final class MySiteRouter
  extends \Facebook\HackRouter\BaseRouter<classname<\Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController>> {

  <<__Override>>
  final public function getRoutes(
  ): ImmMap<\Facebook\HackRouter\HttpMethod, ImmMap<string, classname<\Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController>>> {
    $get = ImmMap {
      '/{MyString}/{MyInt:\\d+}/{MyEnum:(?:bar|derp)}' =>
        \Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController::class,
    };
    return ImmMap {
      \Facebook\HackRouter\HttpMethod::GET => $get,
    };
  }
}
