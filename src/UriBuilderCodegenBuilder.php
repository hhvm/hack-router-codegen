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

final class UriBuilderCodegenBuilder<T as UriBuilderBase> {
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

  private cg\CodegenGeneratedFrom $generatedFrom;
  public function __construct(
    private classname<UriBuilderCodegenBase<T>> $base,
    private classname<UriParameterCodegenBuilder> $parameterBuilder,
  ) {
    $this->generatedFrom = cg\codegen_generated_from_script();
  }

  public function renderToFile(
    string $path,
    self::TSpec $spec,
  ): cg\CodegenFileResult {
    return $this->getCodegenFile($path, $spec)->save();
  }

  private function getCodegenFile(
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

  private function getCodegenClass(self::TSpec $spec): cg\CodegenClass {
    $param_builder = $this->parameterBuilder;
    $controller = $spec['controller'];

    $common = cg\codegen_class($spec['class']['name'])
      ->addConst(
        sprintf("classname<\\%s> CONTROLLER", HasUriPattern::class),
        sprintf("\\%s::class", $controller),
        /* comment = */ null,
        cg\HackBuilderValues::LITERAL,
      )
      ->setIsFinal(true)
      ->setExtends("\\".$this->base);

    $pattern = $controller::getUriPattern();
    foreach ($pattern->getParameters() as $parameter) {
      $common->addMethod($param_builder::getSetter($parameter));
    }

    return $common;
  }

  private function getCodegenTrait(self::TSpec $spec): cg\CodegenTrait {
    $trait = Shapes::idx($spec, 'trait');
    invariant(
      $trait !== null,
      "Can't codegen a trait without a trait spec",
    );
    $class = $spec['class']['name'];
    return cg\codegen_trait($trait['name'])
      ->addMethod(
        cg\codegen_method($trait['method'])
          ->setIsFinal(true)
          ->setIsStatic(true)
          ->setReturnType($class)
          ->setBody(
            cg\hack_builder()
              ->addReturn('new %s();', $class)
              ->getCode()
          )
      );
  }
}
