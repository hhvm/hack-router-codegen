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

final class UriBuilderCodegenBuilder<T as UriBuilderBase>
extends RequestParametersCodegenBuilderBase<UriBuilderCodegenBase<T>> {
  <<__Override>>
  protected function getCodegenClass(self::TSpec $spec): cg\CodegenClass {
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

  <<__Override>>
  protected function getCodegenTrait(self::TSpec $spec): cg\CodegenTrait {
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
