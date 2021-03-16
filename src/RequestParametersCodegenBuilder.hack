/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

use namespace HH\Lib\C;
use type Facebook\HackCodegen\{
  CodegenClass,
  CodegenShapeMember,
  CodegenTrait,
  HackBuilderValues,
  IHackCodegenConfig,
};

final class RequestParametersCodegenBuilder<T as RequestParametersBase>
  extends RequestParametersCodegenBuilderBase<RequestParametersCodegenBase<T>> {
  const type TGetParameters = (function(
    classname<HasUriPattern>,
  ): ImmVector<shape(
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
    $getParameters = $this->getParameters;
    $parameters = $getParameters($controller);

    $body = $this->cg->codegenHackBuilder();

    if (!C\is_empty($parameters)) {
      // avoid generating an assignment to an unused variable
      $body->addAssignment(
        '$p',
        '$this->getParameters()',
        HackBuilderValues::literal(),
      );
    }

    $body
      ->addLine('return shape(')
      ->indent();

    $param_shape = vec[];
    foreach ($parameters as $parameter) {
      $param_spec = $parameter['spec'];
      $request_spec = $param_builder::getRequestSpec($param_spec);
      $getter_spec = $request_spec::getGetterSpec($param_spec);

      $type = $getter_spec['type'];
      if ($parameter['optional']) {
        $type = '?'.$type;
      }

      $param_shape[] = new CodegenShapeMember($param_spec->getName(), $type);
      $body
        ->ensureNewLine()
        ->addf("'%s' => ", $param_spec->getName())
        ->addMultilineCall(
          \sprintf(
            '$p->get%s%s',
            $parameter['optional'] ? 'Optional' : '',
            $getter_spec['accessorSuffix'],
          ),
          $getter_spec['args']->map($arg ==> $arg->render($param_spec)),
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
      ->setIsFinal(true)
      ->setExtends('\\'.$this->base)
      ->addTypeConstant(
        $this->cg
          ->codegenTypeConstant('TParameters')
          ->setValue(
            $this->cg->codegenShape(...$param_shape),
            HackBuilderValues::codegen(),
          ),
      )
      ->addMethod(
        $this->cg
          ->codegenMethod('get')
          ->setReturnType('self::TParameters')
          ->setBody($body->getCode()),
      );
  }

  <<__Override>>
  protected function getCodegenTrait(self::TSpec $spec): CodegenTrait {
    $trait = Shapes::idx($spec, 'trait');
    invariant($trait !== null, "Can't codegen a trait without a trait spec");

    $trait = $this->cg
      ->codegenTrait($trait['name'])
      ->addMethod(
        $this->cg
          ->codegenMethod($trait['method'])
          ->setIsFinal(true)
          ->setProtected()
          ->setIsMemoized(true)
          ->setReturnTypef('%s::TParameters', $spec['class']['name'])
          ->setBody(
            $this->cg
              ->codegenHackBuilder()
              ->addAssignment(
                '$raw',
                $this->getRawParametersCode,
                HackBuilderValues::literal(),
              )
              ->addLinef('return (new %s($raw))', $spec['class']['name'])
              ->indent()
              ->addLine('->get();')
              ->getCode(),
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
