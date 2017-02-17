<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * To re-generate this file run
 * /Users/fred/code/hack-router-codegen/vendor/phpunit/phpunit/phpunit
 *
 *
 * @generated SignedSource<<64b405b6ebb11ce8aae1d939aa1be8ff>>
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

  final public function getMyEnum(): \Facebook\HackRouter\CodeGen\Tests\MyEnum {
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
