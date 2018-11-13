<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * To re-generate this file run vendor/hhvm/hacktest/bin/hacktest
 *
 *
 * @generated SignedSource<<d0e5134ed8258f1477e8fcecb4812021>>
 */
namespace Facebook\HackRouter\CodeGen\Tests\Generated;

abstract final class GetRequestExampleControllerUriBuilder
  extends \Facebook\HackRouter\UriBuilderCodegen {

  const classname<\Facebook\HackRouter\HasUriPattern> CONTROLLER = \Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController::class;
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
