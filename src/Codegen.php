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

use Facebook\DefinitionFinder\BaseParser;
use Facebook\DefinitionFinder\TreeParser;
use Facebook\HackRouter\PrivateImpl\{ClassFacts, ControllerFacts};

final class Codegen {
  const type TUriBuilderOutput = shape(
    'file' => string,
    'namespace' => ?string,
    'class' => shape(
      'name' => string,
    ),
    'trait' => ?shape(
      'name' => string,
      'method' => string,
    ),
  );

  const type TUriBuilderCodegenConfig = shape(
    'base_class' => ?classname<UriBuilderCodegenBase<UriBuilderBase>>,
    'parameter_codegen_builder' => ?classname<RequestParameterCodegenBuilder>,
    'output' =>
      (function(classname<IncludeInUriMap>): ?self::TUriBuilderOutput),
  );

  const type TRequestParametersOutput = shape(
    'file' => string,
    'namespace' => ?string,
    'class' => shape(
      'name' => string,
    ),
    'trait' => shape(
      'name' => string,
    ),
  );

  const type TRequestParametersCodegenConfig = shape(
    'get_parameters' => ?RequestParametersCodegenBuilder::TGetParameters,
    'base_class' =>
      ?classname<RequestParametersCodegenBase<RequestParametersBase>>,
    'parameter_codegen_builder' => ?classname<RequestParameterCodegenBuilder>,
    'trait' => shape(
      'methodName' => string,
      'methodImplementation' =>
        RequestParametersCodegenBuilder::TGetTraitMethodBody,
      'requireExtends' => ?ImmSet<classname<mixed>>,
      'requireImplements' => ?ImmSet<classname<mixed>>,
    ),
    'output' =>
      (function(classname<IncludeInUriMap>): ?self::TRequestParametersOutput),
  );

  const type TRouterCodegenConfig = shape(
    'file' => string,
    'namespace' => ?string,
    'class' => string,
    'abstract' => bool,
  );

  const type TCodegenConfig = shape(
    'controller_base' => ?classname<IncludeInUriMap>,
    'router' => ?self::TRouterCodegenConfig,
    'uri_builders' => ?self::TUriBuilderCodegenConfig,
    'request_parameters' => ?self::TRequestParametersCodegenConfig,
  );

  public static function forTree(
    string $source_root,
    self::TCodegenConfig $config,
  ): Codegen {
    return new self(TreeParser::FromPath($source_root), $config);
  }

  public function build(): void {
    $this->buildRouter();
    $this->buildUriBuilders();
    $this->buildRequestParameters();
  }

  private ControllerFacts<IncludeInUriMap> $controllerFacts;
  private classname<IncludeInUriMap> $controllerBase;

  private function __construct(
    BaseParser $parser,
    private self::TCodegenConfig $config,
  ) {
    $this->controllerBase =
      $config['controller_base'] ?? IncludeInUriMap::class;
    $this->controllerFacts = (new ControllerFacts(
      $this->controllerBase,
      new ClassFacts($parser),
    ));
  }

  private function buildRouter(): void {
    $config = Shapes::idx($this->config, 'router');
    if ($config === null) {
      return;
    }

    $uri_map = (new UriMapBuilder($this->controllerFacts))->getUriMap();

    (new RouterCodegenBuilder($this->controllerBase, $uri_map))
      ->setCreateAbstractClass($config['abstract'])
      ->renderToFile(
        $config['file'],
        Shapes::idx($config, 'namespace'),
        $config['class'],
      );
  }

  private function buildUriBuilders(): void {
    $config = Shapes::idx($this->config, 'uri_builders');
    if ($config === null) {
      return;
    }
    $base = $config['base_class'] ?? UriBuilderCodegen::class;
    $param_builder = $config['parameter_codegen_builder']
      ?? RequestParameterCodegenBuilder::class;
    $get_output = $config['output'];
    $builder = new UriBuilderCodegenBuilder($base, $param_builder);

    $controllers = $this->controllerFacts->getControllers()->keys();
    foreach ($controllers as $controller) {
      $output = $get_output($controller);
      if ($output === null) {
        continue;
      }

      $builder->renderToFile(
        $output['file'],
        shape(
          'controller' => $controller,
          'namespace' => Shapes::idx($output, 'namespace'),
          'class' => $output['class'],
          'trait' => $output['trait'],
        ),
      );
    }
  }

  private function buildRequestParameters(): void {
    $config = Shapes::idx($this->config, 'request_parameters');
    if ($config === null) {
      return;
    }
    $base = $config['base_class'] ?? RequestParametersCodegen::class;
    $param_builder = $config['parameter_codegen_builder']
      ?? RequestParameterCodegenBuilder::class;
    $get_output = $config['output'];
    $get_parameters = $config['get_parameters'] ?? (
      (classname<HasUriPattern> $class) ==>
        $class::getUriPattern()->getParameters()
    );
    $get_trait_impl = $config['trait']['methodImplementation'];

    $builder = new RequestParametersCodegenBuilder(
      $get_parameters,
      $config['trait']['methodImplementation'],
      $base,
      $param_builder,
    );
    foreach ($config['trait']['requireExtends'] ?? [] as $what) {
      $builder->traitRequireExtends($what);
    }
    foreach ($config['trait']['requireImplements'] ?? [] as $what) {
      $builder->traitRequireImplements($what);
    }

    $controllers = $this->controllerFacts->getControllers()->keys();
    foreach ($controllers as $controller) {
      $output = $get_output($controller);
      if ($output === null) {
        continue;
      }
      $builder->renderToFile(
        $output['file'],
        shape(
          'controller' => $controller,
          'namespace' => Shapes::idx($output, 'namespace'),
          'class' => shape(
            'name' => $output['class']['name'],
          ),
          'trait' => shape(
            'name' => $output['trait']['name'],
            'method' => $config['trait']['methodName'],
          ),
        ),
      );
    }
  }
}
