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

use type \Facebook\HackCodegen\{
  CodegenClass,
  CodegenTrait,
  HackBuilderValues,
  HackCodegenFactory,
  IHackCodegenConfig
};

final class UriBuilderCodegenBuilder<T as UriBuilderBase>
extends RequestParametersCodegenBuilderBase<UriBuilderCodegenBase<T>> {
  public function __construct(
    IHackCodegenConfig $hackCodegenConfig,
    classname<UriBuilderCodegenBase<T>> $base,
    RequestParameterCodegenBuilder $parameterBuilder,
    private string $uriGetter,
    private string $uriType,
  ) {
    parent::__construct($hackCodegenConfig, $base, $parameterBuilder);
  }

  <<__Override>>
  protected function getCodegenClass(self::TSpec $spec): CodegenClass {
    $param_builder = $this->parameterBuilder;
    $controller = $spec['controller'];
    $param_shape = [];

    $body = $this->cg
      ->codegenHackBuilder()
      ->addLine('return self::createInnerBuilder()')
      ->indent();

    foreach ($controller::getUriPattern()->getParameters() as $param) {
      $param_spec = $param_builder::getUriSpec($param);
      $setter_spec = $param_spec::getSetterSpec($param);
      $param_shape[$param->getName()] = $setter_spec['type'];
      $body
        ->ensureNewLine()
        ->addMultilineCall(
          '->set'.$setter_spec['accessorSuffix'],
          $setter_spec['args']->map(
            $arg ==> $arg->render(
              $param,
              \sprintf('$parameters[\'%s\']', $param->getName()),
            ),
          )->toVector(),
          /* semicolon at end = */ false,
        );
    }
    $body
      ->addLinef('->%s();', $this->uriGetter)
      ->unindent();

    $method = $this->cg
      ->codegenMethod($this->uriGetter)
      ->setReturnType($this->uriType)
      ->setIsStatic(true)
      ->setBody($body->getCode());
    if (\count($param_shape) !== 0) {
      $method->addParameter('self::TParameters $parameters');
    }

    $common = $this->cg
      ->codegenClass($spec['class']['name'])
      ->addEmptyUserAttribute('Codegen')
      ->addConst(
        \sprintf("classname<\\%s> CONTROLLER", HasUriPattern::class),
        $controller,
        /* comment = */ null,
        HackBuilderValues::classname(),
      )
      ->addTypeConst(
        'TParameters',
        $this->cg->codegenShape($param_shape)->render(),
      )
      ->addMethod($method)
      ->setIsAbstract(true)
      ->setIsFinal(true)
      ->setExtends("\\".$this->base);

    return $common;
  }

  <<__Override>>
  protected function getCodegenTrait(self::TSpec $spec): CodegenTrait {
    $trait = Shapes::idx($spec, 'trait');
    invariant(
      $trait !== null,
      "Can't codegen a trait without a trait spec",
    );

    $controller = $spec['controller'];
    $parameters = $controller::getUriPattern()->getParameters();

    $method = $this->cg
      ->codegenMethod($trait['method'])
      ->setIsFinal(true)
      ->setIsStatic(true)
      ->setReturnType($this->uriType);
    if ($parameters->isEmpty()) {
      $method->setBodyf(
        'return %s::%s();',
        $spec['class']['name'],
        $this->uriGetter,
      );
    } else {
      $method
        ->addParameterf(
          '%s::TParameters $parameters',
          $spec['class']['name'],
        )
        ->setBodyf(
          'return %s::%s($parameters);',
          $spec['class']['name'],
          $this->uriGetter,
        );
    }

    return $this->cg
      ->codegenTrait($trait['name'])
      ->addEmptyUserAttribute('Codegen')
      ->addMethod($method);
  }
}
