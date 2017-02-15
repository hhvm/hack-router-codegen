<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * To re-generate this file run vendor/phpunit/phpunit/phpunit
 *
 *
 * @generated SignedSource<<c4e185b063d3f7747c5d3cabf1451922>>
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
