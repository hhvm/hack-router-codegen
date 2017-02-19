<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * To re-generate this file run vendor/phpunit/phpunit/phpunit
 *
 *
 * @generated SignedSource<<c6eb258d7e5c2ab8f161deafaf7aee19>>
 */
namespace Facebook\HackRouter\CodeGen\Tests\Generated;

<<Codegen>>
final class MySiteRouter
  extends \Facebook\HackRouter\BaseRouter<classname<\Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController>> {

  <<__Override>>
  final public function getRoutes(
  ): ImmMap<\Facebook\HackRouter\HttpMethod, ImmMap<string, classname<\Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController>>> {
    $map = ImmMap {
      \Facebook\HackRouter\HttpMethod::GET => ImmMap {
        '/{MyString}/{MyInt:\\d+}/{MyEnum:(?:bar|derp)}' =>
          \Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController::class,
      },
    };
    return $map;
  }
}
