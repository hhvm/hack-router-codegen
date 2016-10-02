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
 * @generated SignedSource<<155ea17dc2a11c508783a82fcaa4d39a>>
 */
namespace Facebook\HackRouter\CodeGen\Tests\Generated;

class GetRequestExampleControllerParameters
  extends \Facebook\HackRouter\RequestParametersCodegen {

  public function getMyString(): string {
    return $this->getParameters()->getString('MyString');
  }

  public function getMyInt(): int {
    return $this->getParameters()->getInt('MyInt');
  }

  public function getMyEnum(): \Facebook\HackRouter\CodeGen\Tests\MyEnum {
    return $this->getParameters()->getEnum(
      \Facebook\HackRouter\CodeGen\Tests\MyEnum::class,
      'MyEnum',
    );
  }

  public function getMyOptionalParam(): ?string {
    return $this->getParameters()->getOptionalString('MyOptionalParam');
  }
}

trait GetRequestExampleControllerParametersTrait {

  require extends \Facebook\HackRouter\CodeGen\Tests\WebController;

  final public function getParameters(): GetRequestExampleControllerParameters {
    $params = $this->__getParametersImpl();
    return new GetRequestExampleControllerParameters($params);
  }
}
