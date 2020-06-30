#!/usr/bin/env hhvm
<?hh // partial
/**
 * This file is partially generated. Only make modifications between BEGIN
 * MANUAL SECTION and END MANUAL SECTION designators.
 *
 * To re-generate this file run vendor/hhvm/hacktest/bin/hacktest
 *
 *
 * @partially-generated SignedSource<<584ccd4103e1cb5ddaa5cdc80ba52017>>
 */
namespace Facebook\HackRouter\CodeGen\Tests\Generated;
/* BEGIN MANUAL SECTION init */
$autoloader = null;
$autoloader_candidates = ImmSet {
  __DIR__.'/vendor/hh_autoload.php',
  __DIR__.'/../vendor/hh_autoload.php',
  __DIR__.'/../../vendor/hh_autoload.php',
  __DIR__.'/../../../vendor/hh_autoload.php',
};
foreach ($autoloader_candidates as $candidate) {
  if (\file_exists($candidate)) {
    $autoloader = $candidate;
    break;
  }
}
if ($autoloader === null) {
  \fwrite(\STDERR, "Can't find autoloader.\n");
  exit(1);
}
require_once($autoloader);
/* END MANUAL SECTION */

final class MySiteRouterCLILookup {

  private function getRouter(
  ): \Facebook\HackRouter\CodeGen\Tests\Generated\MySiteRouter {
    /* BEGIN MANUAL SECTION MySiteRouterCLILookup::getRouter */
    return new \Facebook\HackRouter\CodeGen\Tests\Generated\MySiteRouter();
    /* END MANUAL SECTION */
  }

  private function prettifyControllerName(string $controller): string {
    /* BEGIN MANUAL SECTION MySiteRouterCLILookup::prettifyControllerName */
    return $controller;
    /* END MANUAL SECTION */
  }

  private function getControllersForPath(
    string $path,
  ): ImmMap<\Facebook\HackRouter\HttpMethod, string> {
    $router = $this->getRouter();
    try {
      $controllers = Map { };
      foreach (\Facebook\HackRouter\HttpMethod::getValues() as $method) {
        try {
          list($controller, $_params) =
            $router->routeMethodAndPath($method, $path);
          $controllers[$method] = $controller;
        } catch (\Facebook\HackRouter\MethodNotAllowedException $_) {
          // Ignore
        }
      }
      return $controllers->immutable();
    } catch (\Facebook\HackRouter\NotFoundException $_) {
      return ImmMap { };
    }
  }

  public function main(KeyedContainer<int, string> $argv): void {
    $path = $argv[1] ?? null;
    if ($path === null) {
      \fprintf(\STDERR, "Usage: %s PATH\n", $argv[0]);
      exit(1);
    }
    $controllers = $this->getControllersForPath($path);
    if ($controllers->isEmpty()) {
      \printf("No controller found for '%s'.\n", $path);
      exit(1);
    }
    foreach ($controllers as $method => $controller) {
      $pretty = $this->prettifyControllerName($controller);
      \printf("%-8s %s\n", $method.':', $pretty);
    }
  }
}

(new MySiteRouterCLILookup())->main($argv);
