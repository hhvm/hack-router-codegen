<?hh // strict
/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace Facebook\HackRouter;

use \Facebook\HackCodegen\{
  CodegenClass,
  CodegenFile,
  CodegenFileResult,
  CodegenFileType,
  CodegenTrait,
  CodegenGeneratedFrom,
  HackCodegenFactory,
  IHackCodegenConfig
};

abstract class RequestParametersCodegenBuilderBase<TBase> {
  const type TTraitSpec = shape(
    'name' => string,
    'method' => string,
  );
  const type TSpec = shape(
    'controller' => classname<HasUriPattern>,
    'namespace' => ?string,
    'class' => shape(
      'name' => string,
    ),
    'trait' => ?self::TTraitSpec,
  );

  protected CodegenGeneratedFrom $generatedFrom;
  protected HackCodegenFactory $cg;

  public function __construct(
    IHackCodegenConfig $hackCodegenConfig,
    protected classname<TBase> $base,
    protected RequestParameterCodegenBuilder $parameterBuilder,
  ) {
    $this->cg = new HackCodegenFactory($hackCodegenConfig);
    $this->generatedFrom = $this->cg->codegenGeneratedFromScript();
  }

  final public function renderToFile(
    string $path,
    self::TSpec $spec,
  ): CodegenFileResult {
    return $this->getCodegenFile($path, $spec)->save();
  }

  final public function setGeneratedFrom(
    CodegenGeneratedFrom $generated_from,
  ): this {
    $this->generatedFrom = $generated_from;
    return $this;
  }

  private bool $discardChanges = false;

  final public function setDiscardChanges(bool $discard): this {
    $this->discardChanges = $discard;
    return $this;
  }

  final private function getCodegenFile(
    string $path,
    self::TSpec $spec,
  ): CodegenFile {
    $file = ($this->cg->codegenFile($path)
      ->setDoClobber($this->discardChanges)
      ->setFileType(CodegenFileType::HACK_STRICT)
      ->setGeneratedFrom($this->generatedFrom)
      ->addClass($this->getCodegenClass($spec))
    );
    $namespace = Shapes::idx($spec, 'namespace');
    if ($namespace !== null) {
      $file->setNamespace($namespace);
    }
    if (Shapes::idx($spec, 'trait')) {
      $file->addTrait($this->getCodegenTrait($spec));
    }
    return $file;
  }

  protected abstract function getCodegenClass(
    self::TSpec $spec,
  ): CodegenClass;

  protected abstract function getCodegenTrait(
    self::TSpec $spec,
  ): CodegenTrait;
}
