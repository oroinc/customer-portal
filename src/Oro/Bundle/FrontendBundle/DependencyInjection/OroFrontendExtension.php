<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection;

use Oro\Bundle\ApiBundle\DependencyInjection\OroApiExtension;
use Oro\Bundle\ApiBundle\Util\DependencyInjectionUtil;
use Oro\Bundle\LayoutBundle\DependencyInjection\OroLayoutExtension;
use Oro\Component\Config\CumulativeResourceInfo;
use Oro\Component\Config\Loader\CumulativeConfigLoader;
use Oro\Component\Config\Loader\FolderingCumulativeFileLoader;
use Oro\Component\Config\Loader\YamlCumulativeFileLoader;
use Oro\Component\DependencyInjection\ExtendedContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OroFrontendExtension extends Extension implements PrependExtensionInterface
{
    public const ALIAS = 'oro_frontend';

    public const API_DOC_VIEWS_PARAMETER_NAME        = 'oro_frontend.api_doc.views';
    public const API_DOC_DEFAULT_VIEW_PARAMETER_NAME = 'oro_frontend.api_doc.default_view';

    private const RESOURCES_FOLDER_PLACEHOLDER = '{folder}';
    private const RESOURCES_FOLDER_PATTERN     = '[a-zA-Z][a-zA-Z0-9_\-:]*';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('services_api.yml');
        $loader->load('form_type.yml');
        $loader->load('block_types.yml');
        $loader->load('commands.yml');

        $container->prependExtensionConfig($this->getAlias(), array_intersect_key($config, array_flip(['settings'])));

        $config = $this->processConfiguration($configuration, $configs);
        $container
            ->getDefinition('oro_frontend.extractor.frontend_exposed_routes_extractor')
            ->replaceArgument(1, $config['routes_to_expose']);

        $container->setParameter('oro_frontend.debug_routes', $config['debug_routes']);

        $apiDocViews = $this->getApiDocViews($container);
        $frontendApiDocViews = $config['frontend_api_doc_views'];
        $container->setParameter(self::API_DOC_VIEWS_PARAMETER_NAME, $frontendApiDocViews);
        $container->setParameter(
            self::API_DOC_DEFAULT_VIEW_PARAMETER_NAME,
            $this->getFrontendDefaultApiView($apiDocViews, $frontendApiDocViews)
        );
        $container->setParameter(
            OroApiExtension::API_DOC_DEFAULT_VIEW_PARAMETER_NAME,
            $this->getBackendDefaultApiView($apiDocViews, $frontendApiDocViews)
        );
        $this->setDefaultHtmlFormatterForFrontendApiViews($container, $apiDocViews, $frontendApiDocViews);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if ($container instanceof ExtendedContainerBuilder) {
            $this->modifyFosRestConfig($container);
        }

        $this->prependScreensConfigs($container);
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return self::ALIAS;
    }

    /**
     * Put screens configurations to oro_layout.themes.*.config.screens.
     *
     * @param ContainerBuilder $container
     */
    protected function prependScreensConfigs(ContainerBuilder $container)
    {
        $resources = $this->loadScreensConfigResources($container);
        if ($resources) {
            $screenConfigs = [];
            foreach ($resources as $resource) {
                $screenConfigs[] = $this->getScreensConfigArray($resource);
            }

            $layoutBundleConfiguration = new ScreensConfiguration();
            $processedScreenConfigs = $this->processConfiguration($layoutBundleConfiguration, $screenConfigs);

            $container->prependExtensionConfig(OroLayoutExtension::ALIAS, $processedScreenConfigs);
        }
    }

    /**
     * Load screens config files.
     *
     * @param ContainerBuilder $container
     *
     * @return CumulativeResourceInfo[]
     */
    protected function loadScreensConfigResources(ContainerBuilder $container)
    {
        $resourceLoaders = [];
        $resourceLoaders[] = new FolderingCumulativeFileLoader(
            self::RESOURCES_FOLDER_PLACEHOLDER,
            self::RESOURCES_FOLDER_PATTERN,
            [
                new YamlCumulativeFileLoader('Resources/views/layouts/{folder}/config/screens.yml'),
            ]
        );

        $configLoader = new CumulativeConfigLoader(OroLayoutExtension::ALIAS, $resourceLoaders);

        return $configLoader->load($container);
    }

    /**
     * @param CumulativeResourceInfo $resource
     *
     * @return array
     */
    protected function getScreensConfigArray(CumulativeResourceInfo $resource)
    {
        $themeName = basename(dirname(dirname($resource->path)));

        return [
            'themes' => [
                $themeName => [
                    'config' => $resource->data,
                ],
            ],
        ];
    }

    /**
     * @param ExtendedContainerBuilder $container
     */
    protected function modifyFosRestConfig(ExtendedContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('fos_rest');
        foreach ($configs as $configKey => $config) {
            if (isset($config['format_listener']['rules']) && is_array($config['format_listener']['rules'])) {
                foreach ($config['format_listener']['rules'] as $key => $rule) {
                    if (!empty($rule['path']) && $rule['path'] === '^/api/(?!(rest|doc)(/|$)+)') {
                        $rules = $config['format_listener']['rules'];
                        // make a a copy of the backend REST API rule
                        $frontendRule = $rule;
                        // add backend prefix to the path of the backend REST API rule
                        $backendPrefix = trim(trim($container->getParameter('web_backend_prefix')), '/');
                        $rule['path'] = str_replace('/api/', '/' . $backendPrefix . '/api/', $rule['path']);
                        $rules[$key] = $rule;
                        // add the frontend REST API rule
                        array_unshift($rules, $frontendRule);
                        // save updated rules
                        $configs[$configKey]['format_listener']['rules'] = $rules;

                        break 2;
                    }
                }
            }
        }

        $container->setExtensionConfig('fos_rest', $configs);
    }

    /**
     * @param array    $views
     * @param string[] $frontendViewNames
     *
     * @return string|null
     */
    private function getBackendDefaultApiView(array $views, array $frontendViewNames): ?string
    {
        $backendDefaultView = null;
        foreach ($views as $name => $view) {
            if (\array_key_exists('default', $view)
                && $view['default']
                && !\in_array($name, $frontendViewNames, true)
            ) {
                $backendDefaultView = $name;
            }
        }

        return $backendDefaultView;
    }

    /**
     * @param array    $views
     * @param string[] $frontendViewNames
     *
     * @return string|null
     */
    private function getFrontendDefaultApiView(array $views, array $frontendViewNames): ?string
    {
        $frontendDefaultView = null;
        foreach ($views as $name => $view) {
            if (\array_key_exists('default', $view)
                && $view['default']
                && \in_array($name, $frontendViewNames, true)
            ) {
                $frontendDefaultView = $name;
            }
        }

        return $frontendDefaultView;
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $apiDocViews
     * @param string[]         $frontendViewNames
     */
    private function setDefaultHtmlFormatterForFrontendApiViews(
        ContainerBuilder $container,
        array $apiDocViews,
        array $frontendViewNames
    ) {
        $config = DependencyInjectionUtil::getConfig($container);
        foreach ($frontendViewNames as $name) {
            if (!array_key_exists($name, $apiDocViews)) {
                throw new LogicException(sprintf(
                    'The view "%s" defined in %s.frontend_api_doc_views is unknown.'
                    . ' Check that it is configured in oro_api.api_doc_views.',
                    $name,
                    self::ALIAS
                ));
            }
            if (empty($apiDocViews[$name]['html_formatter'])
                || 'oro_api.api_doc.formatter.html_formatter' === $apiDocViews[$name]['html_formatter']
            ) {
                $config['api_doc_views'][$name]['html_formatter'] = 'oro_frontend.api_doc.formatter.html_formatter';
            }
        }
        DependencyInjectionUtil::setConfig($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    private function getApiDocViews(ContainerBuilder $container): array
    {
        $config = DependencyInjectionUtil::getConfig($container);

        return $config['api_doc_views'];
    }
}
