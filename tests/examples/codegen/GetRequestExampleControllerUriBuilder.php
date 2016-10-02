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
 * To re-generate this file run
 * /root/hackdev/hack-router-codegen/vendor/phpunit/phpunit/phpunit
 *
 *
 * @generated SignedSource<<7baf393b557b6be64ce8e0acd742e79f>>
 */
namespace Facebook\HackRouter\CodeGen\Tests\Generated;

final class GetRequestExampleControllerUriBuilder
  extends \Facebook\HackRouter\UriBuilderCodegen {

  const classname<\Facebook\HackRouter\HasUriPattern> CONTROLLER =
    \Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController::class;

  final public function setMyString(string $value): this {
    $this->getBuilder()->setString('MyString', $value);
    return $this;
  }

  final public function setMyInt(int $value): this {
    $this->getBuilder()->setInt('MyInt', $value);
    return $this;
  }

  final public function setMyEnum(
    \Facebook\HackRouter\CodeGen\Tests\MyEnum $value,
  ): this {
    $this->getBuilder()->setEnum(
      \Facebook\HackRouter\CodeGen\Tests\MyEnum::class,
      'MyEnum',
      $value,
    );
    return $this;
  }
}

trait GetRequestExampleControllerUriBuilderTrait {

  final public static function getUriBuilder(
  ): GetRequestExampleControllerUriBuilder {
    return new GetRequestExampleControllerUriBuilder();
  }
}
