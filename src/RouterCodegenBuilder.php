<?hh // strict
/*
 *  Copyright (c) 2016, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace Facebook\HackRouter;

use \Facebook\HackCodegen as cg;
use \Facebook\DefinitionFinder\BaseParser;

final class RouterCodegenBuilder<T as IncludeInUriMap> {
  private cg\CodegenGeneratedFrom $generatedFrom;
  private bool $createAbstract = false;

  public function __construct(
    private classname<T> $responderClass,
    private ImmMap<HttpMethod, ImmMap<string, classname<T>>> $uriMap,
  ) {
    $this->generatedFrom = cg\codegen_generated_from_script();
  }

  public function setCreateAbstractClass(bool $abstract): this {
    $this->createAbstract = $abstract;
    return $this;
  }

  public function setGeneratedFrom(
    cg\CodegenGeneratedFrom $generated_from,
  ): this {
    $this->generatedFrom = $generated_from;
    return $this;
  }

  public function renderToFile(
    string $path,
    ?string $namespace,
    string $classname,
  ): cg\CodegenFileResult {
    return $this->getCodegenFile($path, $namespace, $classname)->save();
  }

  <<TestsBypassVisibility>>
  private function getCodegenFile(
    string $path,
    ?string $namespace,
    string $classname,
  ): cg\CodegenFile{
    $file = cg\codegen_file($path)
      ->setFileType(cg\CodegenFileType::HACK_STRICT)
      ->setGeneratedFrom($this->generatedFrom)
      ->addClass($this->getCodegenClass($classname));
    if ($namespace !== null) {
      $file->setNamespace($namespace);
    }
    return $file;
  }

  private function getCodegenClass(
    string $classname,
  ): cg\CodegenClass{
    $class = (cg\codegen_class($classname)
      ->setExtends(sprintf(
        "\\%s<classname<\\%s>>",
        BaseRouter::class,
        $this->responderClass,
      ))
      ->addMethod(
        cg\codegen_method('getRoutes')
          ->setReturnType(
            'ImmMap<\\%s, ImmMap<string, classname<\\%s>>>',
            HttpMethod::class,
            $this->responderClass,
          )
          ->setBody($this->getUriMapBody())
      )
    );

    $abstract = $this->createAbstract;
    $class->setIsAbstract($abstract);
    $class->setIsFinal(!$abstract);
    return $class;
  }

  private function getUriMapBody(): string {
    $map = $this->uriMap;
    $body = cg\hack_builder();
    $parts = Map { };
    foreach ($map as $method => $routes) {
      $sub_map = cg\hack_builder()
        ->addImmMap(
          $routes->map($class ==> '\\'.$class.'::class'),
          cg\HackBuilderKeys::EXPORT,
          cg\HackBuilderValues::LITERAL,
        )
        ->getCode();
      $var = '$'.strtolower($method);
      $body->addAssignment($var, $sub_map);
      $parts["\\".HttpMethod::class.'::'.$method] = $var;
    }

    $map = cg\hack_builder()
      ->addImmMap(
        $parts->immutable(),
        cg\HackBuilderKeys::LITERAL,
        cg\HackBuilderValues::LITERAL,
      )
      ->getCode();
    $body->addReturn('%s', $map);
    return $body->getCode();
  }
}
