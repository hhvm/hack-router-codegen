/*
 *  Copyright (c) 2016-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

use type Facebook\HackCodegen\{
  CodegenGeneratedFrom,
  HackCodegenConfig,
  HackCodegenFactory,
  IHackCodegenConfig,
};
use type Facebook\DefinitionFinder\{BaseParser, TreeParser};
use type Facebook\HackRouter\PrivateImpl\{ClassFacts, ControllerFacts};

final class Codegen {
  const type TUriBuilderOutput = shape(
    'file' => string,
    ?'namespace' => string,
    'class' => shape(
      'name' => string,
    ),
    ?'trait' => shape(
      'name' => string,
      'method' => string,
    ),
  );

  const type TUriBuilderCodegenConfig = shape(
    ?'baseClass' => classname<UriBuilderCodegenBase<UriBuilderBase>>,
    ?'parameterCodegenBuilder' => RequestParameterCodegenBuilder,
    ?'returnSpec' => shape(
      'type' => string,
      'getter' => string,
    ),
    'output' =>
      (function(classname<IncludeInUriMap>): ?self::TUriBuilderOutput),
  );

  const type TRequestParametersOutput = shape(
    'file' => string,
    ?'namespace' => string,
    'class' => shape(
      'name' => string,
    ),
    'trait' => shape(
      'name' => string,
    ),
  );

  const type TRequestParametersCodegenConfig = shape(
    ?'getParameters' => RequestParametersCodegenBuilder::TGetParameters,
    ?'baseClass' =>
      classname<RequestParametersCodegenBase<RequestParametersBase>>,
    ?'parameterCodegenBuilder' => RequestParameterCodegenBuilder,
    'trait' => shape(
      'methodName' => string,
      'getRawParametersCode' => string,
      ?'requireExtends' => ImmSet<classname<mixed>>,
      ?'requireImplements' => ImmSet<classname<mixed>>,
    ),
    'output' =>
      (function(classname<IncludeInUriMap>): ?self::TRequestParametersOutput),
  );

  const type TRouterCodegenConfig = shape(
    'file' => string,
    ?'namespace' => string,
    'class' => string,
    'abstract' => bool,
    ?'cliLookup' => shape(
      'class' => string,
      'file' => string,
    ),
  );

  const type TCodegenConfig = shape(
    ?'hackCodegenConfig' => IHackCodegenConfig,
    ?'controllerBase' => classname<IncludeInUriMap>,
    ?'generatedFrom' => CodegenGeneratedFrom,
    ?'router' => self::TRouterCodegenConfig,
    ?'uriBuilders' => self::TUriBuilderCodegenConfig,
    ?'requestParameters' => self::TRequestParametersCodegenConfig,
    ?'discardChanges' => bool,
  );

  public static function forTree(
    string $source_root,
    self::TCodegenConfig $config,
  ): Codegen {
    return
      new self(\HH\Asio\join(TreeParser::fromPathAsync($source_root)), $config);
  }

  <<__Memoize>>
  private function getGeneratedFrom(): CodegenGeneratedFrom {
    return $this->config['generatedFrom']
      ?? $this->getHackCodegenFactory()->codegenGeneratedFromScript();
  }

  public function build(): void {
    $this->buildRouter();
    $this->buildUriBuilders();
    $this->buildRequestParameters();
  }

  private ControllerFacts<IncludeInUriMap> $controllerFacts;

  private function getControllerBase(): classname<IncludeInUriMap> {
    return $this->config['controllerBase'] ?? IncludeInUriMap::class;
  }

  <<__Memoize>>
  private function getHackCodegenConfig(): IHackCodegenConfig {
    return $this->config['hackCodegenConfig'] ?? new HackCodegenConfig();
  }

  <<__Memoize>>
  private function getHackCodegenFactory(): HackCodegenFactory {
    return new HackCodegenFactory($this->getHackCodegenConfig());
  }

  private function __construct(
    BaseParser $parser,
    private self::TCodegenConfig $config,
  ) {
    $this->controllerFacts = (new ControllerFacts(
      $this->getControllerBase(),
      new ClassFacts($parser),
    ));
  }

  private function buildRouter(): void {
    $config = Shapes::idx($this->config, 'router');
    if ($config === null) {
      return;
    }

    $uri_map = (new UriMapBuilder($this->controllerFacts))->getUriMap();

    (new RouterCodegenBuilder(
      $this->getHackCodegenConfig(),
      $this->getControllerBase(),
      $uri_map,
    ))
      ->setCreateAbstractClass($config['abstract'])
      ->setGeneratedFrom($this->getGeneratedFrom())
      ->setDiscardChanges($this->config['discardChanges'] ?? false)
      ->renderToFile(
        $config['file'],
        Shapes::idx($config, 'namespace'),
        $config['class'],
      );

    $cli_config = $config['cliLookup'] ?? null;
    if ($cli_config === null) {
      return;
    }
    (new RouterCLILookupCodegenBuilder($this->getHackCodegenConfig()))
      ->setGeneratedFrom($this->getGeneratedFrom())
      ->setDiscardChanges($this->config['discardChanges'] ?? false)
      ->renderToFile(
        $cli_config['file'],
        Shapes::idx($config, 'namespace'),
        $config['class'],
        $cli_config['class'],
      );
  }

  private function buildUriBuilders(): void {
    $config = Shapes::idx($this->config, 'uriBuilders');
    if ($config === null) {
      return;
    }
    $base = $config['baseClass'] ?? UriBuilderCodegen::class;
    $param_builder = $config['parameterCodegenBuilder']
      ?? new RequestParameterCodegenBuilder($this->getHackCodegenConfig());
    $get_output = $config['output'];
    $return_spec = $config['returnSpec'] ?? shape(
      'getter' => 'getPath',
      'type' => 'string',
    );

    $builder = (new UriBuilderCodegenBuilder(
      $this->getHackCodegenConfig(),
      $base,
      $param_builder,
      $return_spec['getter'],
      $return_spec['type'],
    ))
      ->setGeneratedFrom($this->getGeneratedFrom())
      ->setDiscardChanges(
        Shapes::idx($this->config ?? shape(), 'discardChanges', false),
      );

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
          'trait' => $output['trait'] ?? null,
        ),
      );
    }
  }

  private function buildRequestParameters(): void {
    $config = Shapes::idx($this->config, 'requestParameters');
    if ($config === null) {
      return;
    }
    $base = $config['baseClass'] ?? RequestParametersCodegen::class;
    $param_builder = $config['parameterCodegenBuilder']
      ?? new RequestParameterCodegenBuilder($this->getHackCodegenConfig());
    $get_output = $config['output'];
    $getParameters = $config['getParameters'] ??
      function (classname<HasUriPattern> $class) {
        return $class::getUriPattern()->getParameters()->map(
          $param ==> shape('spec' => $param, 'optional' => false),
        );
      };

    $builder = (new RequestParametersCodegenBuilder(
      $this->getHackCodegenConfig(),
      $getParameters,
      $config['trait']['getRawParametersCode'],
      $base,
      $param_builder,
    ))
      ->setDiscardChanges(Shapes::idx($this->config ?? shape(), 'discardChanges', false))
      ->setGeneratedFrom($this->getGeneratedFrom());
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
