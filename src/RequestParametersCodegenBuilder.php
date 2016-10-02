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

use Facebook\HackCodegen as cg;
use Facebook\HackRouter\PrivateImpl\RequestParameterRequirementState;

final class RequestParametersCodegenBuilder<T as RequestParametersBase>
extends RequestParametersCodegenBuilderBase<RequestParametersCodegenBase<T>> {
  const type TGetParameters =
    (function(classname<HasUriPattern>): ImmVector<shape(
      'spec' => RequestParameter,
      'optional' => bool,
    )>);
  const type TGetTraitMethodBody = (function(self::TSpec):string);

  private Vector<classname<mixed>> $traitRequiredClasses = Vector {};
  private Vector<classname<mixed>> $traitRequiredInterfaces = Vector {};

  public function __construct(
    private self::TGetParameters $getParameters,
    private self::TGetTraitMethodBody $getTraitMethodBody,
    classname<RequestParametersCodegenBase<T>> $base,
    classname<RequestParameterCodegenBuilder> $parameterBuilder,
  ) {
    parent::__construct($base, $parameterBuilder);
  }

  <<__Override>>
  protected function getCodegenClass(self::TSpec $spec): cg\CodegenClass {
    $param_builder = $this->parameterBuilder;
    $controller = $spec['controller'];

    $common = cg\codegen_class($spec['class']['name'])
      ->setExtends("\\".$this->base);

    $getParameters = $this->getParameters;
    foreach ($getParameters($controller) as $parameter) {
      $common->addMethod($param_builder::getGetter(
        $parameter['spec'],
        $parameter['optional']
          ? RequestParameterRequirementState::IS_OPTIONAL
          : RequestParameterRequirementState::IS_REQUIRED,
      ));
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

    $getTraitMethodBody = $this->getTraitMethodBody;
    $trait = (cg\codegen_trait($trait['name'])
      ->addMethod(cg\codegen_method($trait['method'])
        ->setIsFinal(true)
        ->setReturnType($spec['class']['name'])
        ->setBody($getTraitMethodBody($spec))
      )
    );
    foreach ($this->traitRequiredClasses as $class) {
      $trait->addRequireClass('\\'.$class);
    }
    foreach ($this->traitRequiredInterfaces as $class) {
      $trait->addRequireInterface('\\'.$class);
    }
    return $trait;
  }

  public function traitRequireExtends(classname<mixed> $what): this {
    $this->traitRequiredClasses[] = $what;
    return $this;
  }

  public function traitRequireImplements(classname<mixed> $what): this {
    $this->traitRequiredInterfaces[] = $what;
    return $this;
  }

}
