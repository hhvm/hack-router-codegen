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
  const type TCodegenClassTraitConfig = shape(
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
      (function(classname<IncludeInUriMap>): ?self::TCodegenClassTraitConfig),
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
        $config['namespace'],
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
          'namespace' => $output['namespace'],
          'class' => $output['class'],
          'trait' => $output['trait'],
        ),
      );
    }
  }
}
