<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * To re-generate this file run vendor/phpunit/phpunit/phpunit
 *
 *
 * @generated SignedSource<<011323bb344522c03cbdeb7cebc19883>>
 */
namespace Facebook\HackRouter\CodeGen\Tests\Generated;

<<Codegen>>
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

<<Codegen>>
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
