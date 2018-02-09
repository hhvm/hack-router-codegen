#!/usr/bin/env hhvm
<?hh
/**
 * This file is partially generated. Only make modifications between BEGIN
 * MANUAL SECTION and END MANUAL SECTION designators.
 *
 * To re-generate this file run vendor/phpunit/phpunit/phpunit
 *
 *
 * @partially-generated SignedSource<<394f3dc7b352d127b17bdb87263351ef>>
 */
namespace Facebook\HackRouter\CodeGen\Tests\Generated;
/* BEGIN MANUAL SECTION init */
require_once(__DIR__.'/../../../vendor/hh_autoload.php');
/* END MANUAL SECTION */

<<Codegen>>
final class MySiteRouterCLILookup {

  private function getRouter(
  ): \Facebook\HackRouter\CodeGen\Tests\Generated\MySiteRouter {
    /* BEGIN MANUAL SECTION MySiteRouterCLILookup::getRouter */
    return new \Facebook\HackRouter\CodeGen\Tests\Generated\MySiteRouter();
    /* END MANUAL SECTION */
  }

  private function prettifyControllerName(string $controller): string {
    /* BEGIN MANUAL SECTION MySiteRouterCLILookup::prettifyControllerName */
    $parts = explode('\\', $controller);
    invariant(
      count($parts) > 3,
      'Too few NS parts found; expected everything to be in example NS',
    );
    $first = $parts[0];
    $last = array_pop(&$parts);
    return '\\'.$first.'\\...\\'.$last;
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
            $router->routeRequest($method, $path);
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

  public function main(array<string> $argv): void {
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
