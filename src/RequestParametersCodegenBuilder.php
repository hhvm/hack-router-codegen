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

use Facebook\HackCodegen\{
  CodegenClass,
  CodegenTrait,
  CodegenMethod,
  HackBuilderValues,
  HackCodegenFactory,
  IHackCodegenConfig
};
use Facebook\HackRouter\PrivateImpl\RequestParameterRequirementState;

final class RequestParametersCodegenBuilder<T as RequestParametersBase>
extends RequestParametersCodegenBuilderBase<RequestParametersCodegenBase<T>> {
  const type TGetParameters =
    (function(classname<HasUriPattern>): ImmVector<shape(
      'spec' => RequestParameter,
      'optional' => bool,
    )>);

  private Vector<classname<mixed>> $traitRequiredClasses = Vector {};
  private Vector<classname<mixed>> $traitRequiredInterfaces = Vector {};

  public function __construct(
    IHackCodegenConfig $codegen_config,
    private self::TGetParameters $getParameters,
    private string $getRawParametersCode,
    classname<RequestParametersCodegenBase<T>> $base,
    RequestParameterCodegenBuilder $parameterBuilder,
  ) {
    parent::__construct($codegen_config, $base, $parameterBuilder);
  }

  <<__Override>>
  protected function getCodegenClass(self::TSpec $spec): CodegenClass {
    $param_builder = $this->parameterBuilder;
    $controller = $spec['controller'];

    $body = $this->cg
      ->codegenHackBuilder()
      ->addAssignment(
        '$p',
        '$this->getParameters()',
        HackBuilderValues::literal(),
      )
      ->addLine('return shape(')
      ->indent();

    $getParameters = $this->getParameters;
    $param_shape = [];

    foreach ($getParameters($controller) as $parameter) {
      $param_spec = $parameter['spec'];
      $request_spec = $param_builder::getRequestSpec($param_spec);
      $getter_spec = $request_spec::getGetterSpec($param_spec);

      $type = $getter_spec['type'];
      if ($parameter['optional']) {
        $type = '?'.$type;
      }

      $param_shape[$param_spec->getName()] = $type;
      $body
        ->ensureNewLine()
        ->addf('"%s" => ', $param_spec->getName())
        ->addMultilineCall(
          \sprintf(
            '$p->get%s%s',
            $parameter['optional'] ? 'Optional' : '',
            $getter_spec['accessorSuffix'],
          ),
          $getter_spec['args']->map(
            $arg ==> $arg->render(
              $param_spec,
            ),
          )->toVector(),
          /* semicolon at end = */ false,
        )
        ->add(',');
    }
    $body
      ->ensureNewLine()
      ->unindent()
      ->addLine(');');

    return $this->cg
      ->codegenClass($spec['class']['name'])
      ->addEmptyUserAttribute('Codegen')
      ->setIsFinal(true)
      ->setExtends("\\".$this->base)
      ->addTypeConst(
        'TParameters',
        $this->cg->codegenShape($param_shape)->render(),
      )
      ->addMethod(
        $this->cg
          ->codegenMethod('get')
          ->setReturnType('self::TParameters')
          ->setBody($body->getCode())
      );
  }

  <<__Override>>
  protected function getCodegenTrait(self::TSpec $spec): CodegenTrait {
    $trait = Shapes::idx($spec, 'trait');
    invariant(
      $trait !== null,
      "Can't codegen a trait without a trait spec",
    );


    $trait = $this->cg
      ->codegenTrait($trait['name'])
      ->addEmptyUserAttribute('Codegen')
      ->addMethod(
        $this->cg->codegenMethod($trait['method'])
          ->setIsFinal(true)
          ->setProtected()
          ->setIsMemoized(true)
          ->setReturnTypef(
            '%s::TParameters',
            $spec['class']['name'],
          )
          ->setBody(
            $this->cg
              ->codegenHackBuilder()
              ->addAssignment(
                '$raw',
                $this->getRawParametersCode,
                HackBuilderValues::literal(),
              )
              ->addLinef(
                'return (new %s($raw))',
                $spec['class']['name'],
              )
              ->indent()
              ->addLine('->get();')
              ->getCode()
          ),
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
