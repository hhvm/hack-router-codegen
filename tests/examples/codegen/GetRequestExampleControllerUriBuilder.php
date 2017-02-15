<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * To re-generate this file run vendor/phpunit/phpunit/phpunit
 *
 *
 * @generated SignedSource<<05a357d0ebb349d7076df84ffdd5ed3e>>
 */
namespace Facebook\HackRouter\CodeGen\Tests\Generated;

final class GetRequestExampleControllerUriBuilder
  extends \Facebook\HackRouter\UriBuilderCodegen {

  const classname<\Facebook\HackRouter\HasUriPattern> CONTROLLER =
    \Facebook\HackRouter\CodeGen\Tests\GetRequestExampleController::class;

  final public function setMyString(string $value): this {
    $this->getBuilder()->setString('MyString', $value);
    return $this;
  }

  final public function setMyInt(int $value): this {
    $this->getBuilder()->setInt('MyInt', $value);
    return $this;
  }

  final public function setMyEnum(
    \Facebook\HackRouter\CodeGen\Tests\MyEnum $value,
  ): this {
    $this->getBuilder()->setEnum(
      \Facebook\HackRouter\CodeGen\Tests\MyEnum::class,
      'MyEnum',
      $value,
    );
    return $this;
  }
}

trait GetRequestExampleControllerUriBuilderTrait {

  final public static function getUriBuilder(
  ): GetRequestExampleControllerUriBuilder {
    return new GetRequestExampleControllerUriBuilder();
  }
}
