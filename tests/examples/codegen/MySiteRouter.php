<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * To re-generate this file run vendor/hhvm/hacktest/bin/hacktest
 *
 *
 * @generated SignedSource<<87e793dd3e7f67b9636fe2de5fd524d3>>
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
