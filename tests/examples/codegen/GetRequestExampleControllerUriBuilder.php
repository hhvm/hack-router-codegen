<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * To re-generate this file run
 * /Users/fred/code/hack-router-codegen/vendor/phpunit/phpunit/phpunit
 *
 *
 * @generated SignedSource<<d296f4e5f1afd61139d806c7b4b88ddc>>
 */
namespace Facebook\HackRouter\CodeGen\Tests\Generated;

abstract final class GetRequestExampleControllerUriBuilder
  extends \Facebook\HackRouter\UriBuilderCodegen {

  const classname<\Facebook\HackRouter\HasUriPattern> CONTROLLER =
    \Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController::class;
  const type TParameters = shape(
    'MyString' => string,
    'MyInt' => int,
    'MyEnum' => \Facebook\HackRouter\CodeGen\Tests\MyEnum,
  );

  public static function getPath(self::TParameters $parameters): string {
    return self::createInnerBuilder()
      ->setString('MyString', $parameters['MyString'])
      ->setInt('MyInt', $parameters['MyInt'])
      ->setEnum(
        \Facebook\HackRouter\CodeGen\Tests\MyEnum::class,
        'MyEnum',
        $parameters['MyEnum'],
      )->getPath();
  }
}

trait GetRequestExampleControllerUriBuilderTrait {

  final public static function getPath(
    GetRequestExampleControllerUriBuilder::TParameters $parameters,
  ): string {
    return GetRequestExampleControllerUriBuilder::getPath($parameters);
  }
}
