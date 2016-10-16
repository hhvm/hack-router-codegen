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
 * @generated SignedSource<<4039386318dd7e6e77e01e1a42eddaf0>>
 */
namespace Facebook\HackRouter\CodeGen\Tests\Generated;

class GetRequestExampleControllerParameters
  extends \Facebook\HackRouter\RequestParametersCodegen {

  final public function getMyString(): string {
    return $this->getParameters()->getString('MyString');
  }

  final public function getMyInt(): int {
    return $this->getParameters()->getInt('MyInt');
  }

  final public function getMyEnum(
  ): \Facebook\HackRouter\CodeGen\Tests\MyEnum {
    return $this->getParameters()->getEnum(
      \Facebook\HackRouter\CodeGen\Tests\MyEnum::class,
      'MyEnum',
    );
  }

  final public function getMyOptionalParam(): ?string {
    return $this->getParameters()->getOptionalString('MyOptionalParam');
  }
}

trait GetRequestExampleControllerParametersTrait {

  require extends \Facebook\HackRouter\CodeGen\Tests\WebController;

  final protected function getParameters(
  ): GetRequestExampleControllerParameters {
    $params = $this->__getParametersImpl();
    return new GetRequestExampleControllerParameters($params);
  }
}
