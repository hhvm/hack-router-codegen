<?hh //strict
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

abstract class RequestParameterCodegenBuilder {
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

  final public static function getGetter(
    RequestParameter $param,
  ): cg\CodegenMethod {
    $spec = self::getRequestSpec($param);
    $spec = $spec::getGetterSpec($param);

    return cg\codegen_method('get'.$param->getName())
      ->setReturnType($spec['type'])
      ->setBody(
        cg\hack_builder()
          ->add('return ')
          ->addMultilineCall(
            '$this->getParameters()->'.$spec['method'],
            $spec['args']->map($arg ==> $arg->render($param))->toVector(),
          )
          ->getCode(),
      );
  }

  final public static function getSetter(
    UriParameter $param,
  ): cg\CodegenMethod {
    $spec = self::getUriSpec($param);
    $spec = $spec::getSetterSpec($param);

    $value_var = '$value';

    return cg\codegen_method('set'.$param->getName())
      ->setParameters(Vector {
        $spec['type'].' '.$value_var,
      })
      ->setReturnType('this')
      ->setBody(
        cg\hack_builder()
          ->addMultilineCall(
            '$this->getBuilder()->'.$spec['method'],
            $spec['args']->map(
              $arg ==> $arg->render($param, $value_var),
            )->toVector(),
          )
          ->addReturn('$this')
          ->getCode(),
      );
  }

  final private static function getRequestSpec(
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

  final private static function getUriSpec(
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
