<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection;

use Oro\Bundle\ApiBundle\DependencyInjection\OroApiExtension;
use Oro\Bundle\ApiBundle\Util\DependencyInjectionUtil;
use Oro\Bundle\FrontendBundle\Request\NotInstalledFrontendHelper;
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

    public const FRONTEND_SESSION_STORAGE_OPTIONS_PARAMETER_NAME = 'oro_frontend.session.storage.options';

    public const API_DOC_VIEWS_PARAMETER_NAME        = 'oro_frontend.api_doc.views';
    public const API_DOC_DEFAULT_VIEW_PARAMETER_NAME = 'oro_frontend.api_doc.default_view';

    private const API_CACHE_CONTROL_PROCESSOR_SERVICE_ID = 'oro_frontend.api.options.rest.set_cache_control';
    private const API_MAX_AGE_PROCESSOR_SERVICE_ID       = 'oro_frontend.api.options.rest.cors.set_max_age';
    private const API_ALLOW_ORIGIN_PROCESSOR_SERVICE_ID  = 'oro_frontend.api.rest.cors.set_allow_origin';
    private const API_CORS_HEADERS_PROCESSOR_SERVICE_ID  = 'oro_frontend.api.rest.cors.set_allow_and_expose_headers';

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
        $loader->load('controllers.yml');

        if ('test' === $container->getParameter('kernel.environment')) {
            $loader->load('services_test.yml');
        }

        $container->prependExtensionConfig($this->getAlias(), array_intersect_key($config, array_flip(['settings'])));

        $config = $this->processConfiguration($configuration, $configs);
        $container
            ->getDefinition('oro_frontend.extractor.frontend_exposed_routes_extractor')
            ->replaceArgument(1, $config['routes_to_expose']);

        $container->setParameter('oro_frontend.debug_routes', $config['debug_routes']);

        $this->configureFrontendHelper($container);
        $this->configureFrontendSession($container, $config);
        $this->configureApiDocViews($container, $config);
        $this->configureApiCors($container, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if ($container instanceof ExtendedContainerBuilder) {
            $this->modifySecurityConfig($container);
            $this->modifyFosRestConfig($container);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return self::ALIAS;
    }

    /**
     * @param ExtendedContainerBuilder $container
     */
    private function modifySecurityConfig(ExtendedContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('security');
        $restApiPatternPlaceholder = '%' . OroApiExtension::REST_API_PATTERN_PARAMETER_NAME . '%';
        foreach ($configs as $configKey => $config) {
            if (isset($config['firewalls']) && is_array($config['firewalls'])) {
                foreach ($config['firewalls'] as $key => $firewall) {
                    if (!empty($firewall['pattern'])
                        && $firewall['pattern'] === $restApiPatternPlaceholder
                        && 0 !== strpos($key, 'frontend_')
                    ) {
                        // add backend prefix to the pattern of the backend REST API firewall
                        $configs[$configKey]['firewalls'][$key]['pattern'] = $this->getBackendApiPattern($container);
                    }
                }
            }
        }
        $container->setExtensionConfig('security', $configs);
    }

    /**
     * @param ExtendedContainerBuilder $container
     */
    private function modifyFosRestConfig(ExtendedContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('fos_rest');
        $restApiPatternPlaceholder = '%' . OroApiExtension::REST_API_PATTERN_PARAMETER_NAME . '%';
        foreach ($configs as $configKey => $config) {
            if (isset($config['format_listener']['rules']) && is_array($config['format_listener']['rules'])) {
                foreach ($config['format_listener']['rules'] as $key => $rule) {
                    if (!empty($rule['path']) && $rule['path'] === $restApiPatternPlaceholder) {
                        $rules = $config['format_listener']['rules'];
                        // make a a copy of the backend REST API rule
                        $frontendRule = $rule;
                        // add backend prefix to the path of the backend REST API rule
                        $rule['path'] = $this->getBackendApiPattern($container);
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
     * @param ContainerBuilder $container
     */
    private function configureFrontendHelper(ContainerBuilder $container)
    {
        if ($container->hasParameter('installed') && $container->getParameter('installed')) {
            return;
        }

        $container->getDefinition('oro_frontend.request.frontend_helper')
            ->setClass(NotInstalledFrontendHelper::class);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function configureFrontendSession(ContainerBuilder $container, array $config)
    {
        $options = [];
        if (!empty($config['session']['name'])) {
            $sessionConfig = $config['session'];
            $keys = [
                'name',
                'cookie_lifetime',
                'cookie_path',
                'gc_maxlifetime',
                'gc_probability',
                'gc_divisor'
            ];
            foreach ($keys as $key) {
                if (isset($sessionConfig[$key])) {
                    $options[$key] = $sessionConfig[$key];
                }
            }
        }

        $container->setParameter(self::FRONTEND_SESSION_STORAGE_OPTIONS_PARAMETER_NAME, $options);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function configureApiDocViews(ContainerBuilder $container, array $config)
    {
        $apiDocViews = $this->getApiDocViews($container);
        $frontendApiDocViews = $config['frontend_api']['api_doc_views'];
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
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function configureApiCors(ContainerBuilder $container, array $config)
    {
        $corsConfig = $config['frontend_api']['cors'];
        $container->getDefinition(self::API_CACHE_CONTROL_PROCESSOR_SERVICE_ID)
            ->replaceArgument(0, $corsConfig['preflight_max_age']);
        $container->getDefinition(self::API_MAX_AGE_PROCESSOR_SERVICE_ID)
            ->replaceArgument(0, $corsConfig['preflight_max_age']);
        $container->getDefinition(self::API_ALLOW_ORIGIN_PROCESSOR_SERVICE_ID)
            ->replaceArgument(0, $corsConfig['allow_origins']);
        $container->getDefinition(self::API_CORS_HEADERS_PROCESSOR_SERVICE_ID)
            ->replaceArgument(0, $corsConfig['allow_headers'])
            ->replaceArgument(1, $corsConfig['expose_headers'])
            ->replaceArgument(2, $corsConfig['allow_credentials']);
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return string
     */
    private function getBackendApiPattern(ContainerBuilder $container)
    {
        $backendPrefix = trim(trim($container->getParameter('web_backend_prefix')), '/');
        $prefix = $container->getParameter(OroApiExtension::REST_API_PREFIX_PARAMETER_NAME);
        $pattern = $container->getParameter(OroApiExtension::REST_API_PATTERN_PARAMETER_NAME);

        return str_replace($prefix, '/' . $backendPrefix . $prefix, $pattern);
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
                    'The view "%s" defined in %s.frontend_api.api_doc_views is unknown.'
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
