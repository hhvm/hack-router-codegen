<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * To re-generate this file run vendor/hhvm/hacktest/bin/hacktest
 *
 *
 * @generated SignedSource<<0e9bf7c34daf198ab5439077c13199b2>>
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
      'MyString' => $p->getString('MyString'),
      'MyInt' => $p->getInt('MyInt'),
      'MyEnum' => $p->getEnum(\Facebook\HackRouter\CodeGen\Tests\MyEnum::class, 'MyEnum'),
      'MyOptionalParam' => $p->getOptionalString('MyOptionalParam'),
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
