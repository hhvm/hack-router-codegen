<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * To re-generate this file run
 * /Users/fred/code/hack-router-codegen/vendor/phpunit/phpunit/phpunit
 *
 *
 * @generated SignedSource<<294f395a99462244d5a8e44eea0e061d>>
 */
namespace Facebook\HackRouter\CodeGen\Tests\Generated;

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
