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
  CodegenTrait,
  HackBuilderValues,
  HackCodegenFactory
};

final class UriBuilderCodegenBuilder<T as UriBuilderBase>
extends RequestParametersCodegenBuilderBase<UriBuilderCodegenBase<T>> {
  <<__Override>>
  protected function getCodegenClass(self::TSpec $spec): CodegenClass {
    $param_builder = $this->parameterBuilder;
    $controller = $spec['controller'];

    $common = $this->cg->codegenClass($spec['class']['name'])
      ->addConst(
        sprintf("classname<\\%s> CONTROLLER", HasUriPattern::class),
        $controller,
        /* comment = */ null,
        HackBuilderValues::classname(),
      )
      ->setIsFinal(true)
      ->setExtends("\\".$this->base);

    $pattern = $controller::getUriPattern();
    foreach ($pattern->getParameters() as $parameter) {
      $common->addMethod($param_builder->getSetter($parameter));
    }

    return $common;
  }

  <<__Override>>
  protected function getCodegenTrait(self::TSpec $spec): CodegenTrait {
    $trait = Shapes::idx($spec, 'trait');
    invariant(
      $trait !== null,
      "Can't codegen a trait without a trait spec",
    );
    $class = $spec['class']['name'];

    $cg = $this->cg;
    return $cg->codegenTrait($trait['name'])
      ->addMethod(
        $cg->codegenMethod($trait['method'])
          ->setIsFinal(true)
          ->setIsStatic(true)
          ->setReturnType($class)
          ->setBody(
            $cg->codegenHackBuilder()
              ->addReturnf('new %s()', $class)
              ->getCode()
          )
      );
  }
}
