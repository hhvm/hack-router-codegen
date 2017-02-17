<?hh //strict
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

use Facebook\HackRouter\PrivateImpl\RequestParameterRequirementState;

use Facebook\HackCodegen\{
  IHackCodegenConfig,
  HackCodegenFactory,
  CodegenMethod
};

class RequestParameterCodegenBuilder {
  protected HackCodegenFactory $cg;

  public function __construct(
    IHackCodegenConfig $config,
  ) {
    $this->cg = new HackCodegenFactory($config);
  }

  protected static function getParameterSpecs(
  ): ImmMap<
    classname<RequestParameter>,
    classname<RequestParameterCodegenSpec>,
  > {
    return ImmMap {
      IntRequestParameter::class => IntParameterCodegenSpec::class,
      StringRequestParameter::class => StringParameterCodegenSpec::class,
      EnumRequestParameter::class => EnumParameterCodegenSpec::class,
    };
  }

  public function getGetter(
    RequestParameter $param,
    RequestParameterRequirementState $required,
  ): CodegenMethod {
    $spec = self::getRequestSpec($param);
    $spec = $spec::getGetterSpec($param);

    if ($required === RequestParameterRequirementState::IS_REQUIRED) {
      $type = $spec['type'];
      $method = 'get'.$spec['accessorSuffix'];
    } else {
      $type = '?'.$spec['type'];
      $method = 'getOptional'.$spec['accessorSuffix'];
    }

    return $this->cg->codegenMethod('get'.$param->getName())
      ->setIsFinal(true)
      ->setReturnType($type)
      ->setBody(
        $this->cg->codegenHackBuilder()
          ->add('return ')
          ->addMultilineCall(
            '$this->getParameters()->'.$method,
            $spec['args']->map($arg ==> $arg->render($param))->toVector(),
          )
          ->getCode(),
      );
  }

  public function getSetter(
    UriParameter $param,
  ): CodegenMethod {
    $spec = self::getUriSpec($param);
    $spec = $spec::getSetterSpec($param);

    $value_var = '$value';

    $cg = $this->cg;
    return $cg->codegenMethod('set'.$param->getName())
      ->setParameters(Vector {
        $spec['type'].' '.$value_var,
      })
      ->setIsFinal(true)
      ->setReturnType('this')
      ->setBody(
        $cg->codegenHackBuilder()
          ->addMultilineCall(
            '$this->getBuilder()->set'.$spec['accessorSuffix'],
            $spec['args']->map(
              $arg ==> $arg->render($param, $value_var),
            )->toVector(),
          )
          ->addReturn('$this')
          ->getCode(),
      );
  }

  final protected static function getRequestSpec(
    RequestParameter $param,
  ): classname<RequestParameterCodegenSpec> {
    $specs = self::getParameterSpecs();
    $type = get_class($param);
    invariant(
      $specs->containsKey($type),
      "Don't know how to render a %s",
      $type,
    );
    return $specs->at($type);
  }

  final protected static function getUriSpec(
    UriParameter $param,
  ): classname<UriParameterCodegenSpec> {
    $spec = self::getRequestSpec($param);
    invariant(
      is_subclass_of($spec, UriParameterCodegenSpec::class),
      "Expected %s to be a %s",
      $spec,
      UriParameterCodegenSpec::class,
    );
    /* HH_FIXME[4110] can't coerce classnames */
    return $spec;
  }
}
