<?hh // strict
/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

use \Facebook\HackCodegen\{
  CodegenClass,
  CodegenFile,
  CodegenFileResult,
  CodegenFileType,
  CodegenGeneratedFrom,
  HackBuilderKeys,
  HackBuilderValues,
  HackCodegenFactory,
  IHackCodegenConfig
};
use \Facebook\DefinitionFinder\BaseParser;

final class RouterCodegenBuilder<T as IncludeInUriMap> {
  private CodegenGeneratedFrom $generatedFrom;
  private HackCodegenFactory $cg;
  private bool $createAbstract = false;

  public function __construct(
    private IHackCodegenConfig $codegenConfig,
    private classname<T> $responderClass,
    private ImmMap<HttpMethod, ImmMap<string, classname<T>>> $uriMap,
  ) {
    $this->cg = new HackCodegenFactory($codegenConfig);
    $this->generatedFrom = $this->cg->codegenGeneratedFromScript();
  }

  public function setCreateAbstractClass(bool $abstract): this {
    $this->createAbstract = $abstract;
    return $this;
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
    string $classname,
  ): CodegenFileResult {
    return $this->getCodegenFile($path, $namespace, $classname)->save();
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
    string $classname,
  ): CodegenFile{
    $file = $this->cg->codegenFile($path)
      ->setDoClobber($this->discardChanges)
      ->setFileType(CodegenFileType::HACK_STRICT)
      ->setGeneratedFrom($this->generatedFrom)
      ->addClass($this->getCodegenClass($classname));
    if ($namespace !== null) {
      $file->setNamespace($namespace);
    }
    return $file;
  }

  private function getCodegenClass(
    string $classname,
  ): CodegenClass{
    $class = ($this->cg->codegenClass($classname)
      ->addEmptyUserAttribute('Codegen')
      ->setExtends(\sprintf(
        "\\%s<classname<\\%s>>",
        BaseRouter::class,
        $this->responderClass,
      ))
      ->addMethod(
        $this->cg->codegenMethod('getRoutes')
          ->setIsFinal(true)
          ->setIsOverride(true)
          ->setReturnTypef(
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

    return $this->cg->codegenHackBuilder()
      ->addAssignment(
        '$map',
        $map,
        HackBuilderValues::immMap(
          HackBuilderKeys::lambda(($_config, $method) ==>
            \sprintf(
              "\\%s::%s",
              HttpMethod::class,
              $method,
            ),
          ),
          HackBuilderValues::immMap(
            HackBuilderKeys::export(),
            HackBuilderValues::classname(),
          ),
        ),
      )
      ->addReturnf('$map')
      ->getCode();
  }
}
