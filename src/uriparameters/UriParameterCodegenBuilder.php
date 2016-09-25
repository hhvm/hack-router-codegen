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

abstract class UriParameterCodegenBuilder {
  protected static function getParameterSpecs(
  ): ImmMap<
    classname<UriPatternParameter>,
    classname<UriParameterCodegenSpec>,
  > {
    return ImmMap {
      UriPatternIntParameter::class => UriIntParameterCodegenSpec::class,
      UriPatternStringParameter::class => UriStringParameterCodegenSpec::class,
      UriPatternEnumParameter::class => UriEnumParameterCodegenSpec::class,
    };
  }

  final public static function getGetter(
    UriPatternParameter $param,
  ): cg\CodegenMethod {
    $spec = self::getSpec($param);
    $spec = $spec::getGetterSpec($param);

    return cg\codegen_method('get'.$param->getName())
      ->setReturnType($spec['type'])
      ->setBody(
        cg\hack_builder()
          ->add('return ')
          ->addMultilineCall(
            '$this->params->'.$spec['method'],
            $spec['args']->map($arg ==> $arg->render($param))->toVector(),
          )
          ->getCode(),
      );
  }

  final public static function getSetter(
    UriPatternParameter $param,
  ): cg\CodegenMethod {
    $spec = self::getSpec($param);
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
            '$this->builder->'.$spec['method'],
            $spec['args']->map(
              $arg ==> $arg->render($param, $value_var),
            )->toVector(),
          )
          ->addReturn('$this')
          ->getCode(),
      );
  }

  final private static function getSpec(
    UriPatternParameter $param,
  ): classname<UriParameterCodegenSpec> {
    $specs = self::getParameterSpecs();
    $type = get_class($param);
    invariant(
      $specs->containsKey($type),
      "Don't know how to render a %s",
      $type,
    );
    return $specs->at($type);
  }
}
