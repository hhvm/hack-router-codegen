Hack-Router-Codegen
===================

[![Continuous Integration](https://github.com/hhvm/hack-router-codegen/actions/workflows/build-and-test.yml/badge.svg)](https://github.com/hhvm/hack-router-codegen/actions/workflows/build-and-test.yml)

Code generation for controller classes using the `UriPattern` system from
[`hhvm/hack-router`](https://github.com/hhvm/hack-router)

This currently supports generating:
 - Request routing maps
 - Hack request routing classes for your site

 For now, looking at the unit tests is the best way to learn how to use
 it.

Building a Request Router
=========================

```Hack
<?hh
use \Facebook\HackRouter\Codegen;

final class UpdateCodegen {
  public function main(): void {
    $codegen = await Codegen::forTree(
      __DIR__.'/../src/',
      shape(
        'controllerBase' => WebController::class,
        'router' => shape(
          'abstract' => false,
          'file' => __DIR__.'/../codegen/Router.php',
          'class' => 'Router',
        ),
      ),
    );
    $codegen->build();
  }
}
```


This will generate a class called 'Router', complete with an
automatically-generated route map, based on the URI patterns in your
controllers.

`WebController` is the root controller for your site, and must implement
`Facebook\HackRouter\IncludeInUriMap`, which in turn requires
`Facebook\HackRouter\HasUriPattern` - for example:

```Hack
public static function getUriPattern(): UriPattern {
  return (new UriPattern())
    ->literal('/')
    ->string('MyString')
    ->literal('/')
    ->int('MyInt')
    ->literal('/')
    ->enum(MyEnum::class, 'MyEnum');
}
```

Commit Your Codegen!
====================

This is unusual advice, but it's the best approach for Hack code as you
otherwise have a circular dependency:
 - HHVM will not execute hack code if there are references to undefined classes
 - Once you use the codegen, you reference the codegen classes
 - ... so you can't build them if you don't already have them

Contributing
============

We welcome GitHub issues and pull requests - please see CONTRIBUTING.md for details.

License
=======

hack-router-codegen is MIT-licensed.
