/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

use type Facebook\HackCodegen\{
  CodegenClass,
  CodegenFile,
  CodegenFileResult,
  CodegenFileType,
  CodegenGeneratedFrom,
  CodegenMethod,
  HackBuilderValues,
  HackCodegenFactory,
  IHackCodegenConfig
};

final class RouterCLILookupCodegenBuilder {
  private CodegenGeneratedFrom $generatedFrom;
  private HackCodegenFactory $cg;

  public function __construct(
    private IHackCodegenConfig $codegenConfig,
  ) {
    $this->cg = new HackCodegenFactory($codegenConfig);
    $this->generatedFrom = $this->cg->codegenGeneratedFromScript();
  }

  public function setGeneratedFrom(
    CodegenGeneratedFrom $generated_from,
  ): this {
    $this->generatedFrom = $generated_from;
    return $this;
  }

  public function renderToFile(
    string $path,
    ?string $namespace,
    string $router_classname,
    string $utility_classname,
  ): CodegenFileResult {
    return $this->getCodegenFile(
      $path,
      $namespace,
      $router_classname,
      $utility_classname,
    )->save();
  }

  private bool $discardChanges = false;

  public function setDiscardChanges(bool $discard): this {
    $this->discardChanges = $discard;
    return $this;
  }

  <<TestsBypassVisibility>>
  private function getCodegenFile(
    string $path,
    ?string $namespace,
    string $router_classname,
    string $utility_classname,
  ): CodegenFile {
    $file = $this->cg->codegenFile($path)
      ->setDoClobber($this->discardChanges)
      ->setShebangLine('#!/usr/bin/env hhvm')
      ->setFileType(CodegenFileType::HACK_PARTIAL)
      ->setGeneratedFrom($this->generatedFrom)
      ->setPseudoMainHeader($this->getInitCode())
      ->addClass($this->getCodegenClass($router_classname, $utility_classname))
      ->setPseudoMainFooterf(
        '(new %s())->main($argv);',
        $utility_classname,
      );

    if ($namespace !== null) {
      $file->setNamespace($namespace);
    }
    return $file;
  }

  private function getCodegenClass(
    string $router_classname,
    string $utility_classname,
  ): CodegenClass {
    return $this->cg->codegenClass($utility_classname)
      ->setIsFinal(true)
      ->addMethod(
        $this->cg->codegenMethod('getRouter')
          ->setReturnType('\\'.$router_classname)
          ->setPrivate()
          ->setManualBody(true)
          ->setBodyf(
            'return new \\%s();',
            $router_classname,
          )
      )
      ->addMethod(
        $this->cg->codegenMethod('prettifyControllerName')
          ->addParameter('string $controller')
          ->setReturnType('string')
          ->setPrivate()
          ->setManualBody(true)
          ->setBody('return $controller;')
      )
      ->addMethod($this->getControllersForPathMethod())
      ->addMethod($this->getMainMethod());
  }

  private function getControllersForPathMethod(): CodegenMethod {
    return $this->cg->codegenMethod('getControllersForPath')
      ->addParameter('string $path')
      ->setReturnTypef(
        'ImmMap<\\%s, string>',
        HttpMethod::class,
      )
      ->setPrivate()
      ->setBody(
        $this->cg->codegenHackBuilder()
          ->addAssignment(
            '$router',
            '$this->getRouter()',
            HackBuilderValues::literal(),
          )
          ->startTryBlock()
          ->addAssignment(
            '$controllers',
            'Map { }',
            HackBuilderValues::literal(),
          )
          ->startForeachLoop(
            \sprintf('\\%s::getValues()', HttpMethod::class),
            null,
            '$method',
          )
          ->startTryBlock()
          ->addLine('list($controller, $_params) =')
          ->indent()
          ->addLine('$router->routeMethodAndPath($method, $path);')
          ->unindent()
          ->addLine('$controllers[$method] = $controller;')
          ->addCatchBlock('\\'.MethodNotAllowedException::class, '$_')
          ->addInlineComment('Ignore')
          ->endTryBlock()
          ->endForeachLoop()
          ->addReturn('$controllers->immutable()', HackBuilderValues::literal())
          ->addCatchBlock('\\'.NotFoundException::class, '$_')
          ->addReturn('ImmMap { }', HackBuilderValues::literal())
          ->endTryBlock()
          ->getCode()
      );
  }

  private function getInitCode(): string {
    $autoloader_dirs = ImmSet {
      '/',
      '/vendor/',
      '/../vendor/',
      '/../',
    };
    $autoloader_files = ImmSet {
      'hh_autoload.php',
      'autoload.php',
    };
    $full_autoloader_files = Set { };
    foreach ($autoloader_files as $file) {
      foreach ($autoloader_dirs as $dir) {
        $full_autoloader_files[] = \sprintf(
          '__DIR__.%s',
          \var_export($dir.$file, true),
        );
      }
    }

    return $this->cg->codegenHackBuilder()
      ->startManualSection('init')
      ->addAssignment(
        '$autoloader',
        'null',
        HackBuilderValues::literal(),
      )
      ->addAssignment(
        '$autoloader_candidates',
        $full_autoloader_files->immutable(),
        HackBuilderValues::immSet(HackBuilderValues::literal()),
      )
      ->startForeachLoop('$autoloader_candidates', null, '$candidate')
      ->startIfBlock('\\file_exists($candidate)')
      ->addAssignment('$autoloader', '$candidate', HackBuilderValues::literal())
      ->addLine('break;')
      ->endIfBlock()
      ->endForeachLoop()
      ->startIfBlock('$autoloader === null')
      ->addLine('\\fwrite(\\STDERR, "Can\'t find autoloader.\n");')
      ->addLine('exit(1);')
      ->endIfBlock()
      ->addLine('require_once($autoloader);')
      ->endManualSection()
      ->getCode();
  }

  private function getMainMethod(): CodegenMethod {
    return $this->cg->codegenMethod('main')
      ->addParameter('array<string> $argv')
      ->setReturnType('void')
      ->setBody(
        $this->cg->codegenHackBuilder()
          ->addAssignment(
            '$path',
            '$argv[1] ?? null',
            HackBuilderValues::literal(),
          )
          ->startIfBlock('$path === null')
          ->addLine('\\fprintf(\\STDERR, "Usage: %s PATH\n", $argv[0]);')
          ->addLine('exit(1);')
          ->endIfBlock()
          ->addAssignment(
            '$controllers',
            '$this->getControllersForPath($path)',
            HackBuilderValues::literal(),
          )
          ->startIfBlock('$controllers->isEmpty()')
          ->addLine('\\printf("No controller found for \'%s\'.\n", $path);')
          ->addLine('exit(1);')
          ->endIfBlock()
          ->startForeachLoop('$controllers', '$method', '$controller')
          ->addAssignment(
            '$pretty',
            '$this->prettifyControllerName($controller)',
            HackBuilderValues::literal(),
          )
          ->addLine('\\printf("%-8s %s\n", $method.\':\', $pretty);')
          ->endForeachLoop()
          ->getCode()
      );
  }
}
