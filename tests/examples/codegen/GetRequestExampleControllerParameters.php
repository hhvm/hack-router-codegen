<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * To re-generate this file run
 * /Users/fred/code/hack-router-codegen/vendor/phpunit/phpunit/phpunit
 *
 *
 * @generated SignedSource<<8b79ae990ea41d52f4bcc4c0774dc5b1>>
 */
namespace Facebook\HackRouter\CodeGen\Tests\Generated;

final class GetRequestExampleControllerParameters
  extends \Facebook\HackRouter\RequestParametersCodegen {

  const type TParameters = shape(
    'MyString' => string,
    'MyInt' => int,
    'MyEnum' => \Facebook\HackRouter\CodeGen\Tests\MyEnum,
    'MyOptionalParam' => ?string,
  );

  public function get(): self::TParameters {
    $p = $this->getParameters();
    return shape(
      "MyString" => $p->getString('MyString'),
      "MyInt" => $p->getInt('MyInt'),
      "MyEnum" => $p->getEnum(\Facebook\HackRouter\CodeGen\Tests\MyEnum::class, 'MyEnum'),
      "MyOptionalParam" => $p->getOptionalString('MyOptionalParam'),
    );
  }
}

trait GetRequestExampleControllerParametersTrait {

  require extends \Facebook\HackRouter\CodeGen\Tests\WebController;

  <<__Memoize>>
  final protected function getParameters(
  ): GetRequestExampleControllerParameters::TParameters {
    $raw = $this->__getParametersImpl();
    return (new GetRequestExampleControllerParameters($raw))
      ->get();
  }
}
