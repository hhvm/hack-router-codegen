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
    'trait' => self::TTraitSpec,
  );

  protected cg\CodegenGeneratedFrom $generatedFrom;
  public function __construct(
    protected classname<TBase> $base,
    protected classname<RequestParameterCodegenBuilder> $parameterBuilder,
  ) {
    $this->generatedFrom = cg\codegen_generated_from_script();
  }

  final public function renderToFile(
    string $path,
    self::TSpec $spec,
  ): cg\CodegenFileResult {
    return $this->getCodegenFile($path, $spec)->save();
  }

  final private function getCodegenFile(
    string $path,
    self::TSpec $spec,
  ): cg\CodegenFile {
    $file = (cg\codegen_file($path)
      ->setFileType(cg\CodegenFileType::HACK_STRICT)
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
  ): cg\CodegenClass;

  protected abstract function getCodegenTrait(
    self::TSpec $spec,
  ): cg\CodegenTrait;
}
