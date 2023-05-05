<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection;

use Oro\Bundle\ApiBundle\DependencyInjection\OroApiExtension;
use Oro\Bundle\ApiBundle\Util\DependencyInjectionUtil;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Oro\Component\DependencyInjection\ExtendedContainerBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class OroFrontendExtension extends Extension implements PrependExtensionInterface
{
    public const FRONTEND_SESSION_STORAGE_OPTIONS_PARAMETER_NAME = 'oro_frontend.session.storage.options';

    public const API_DOC_VIEWS_PARAMETER_NAME = 'oro_frontend.api_doc.views';
    public const API_DOC_DEFAULT_VIEW_PARAMETER_NAME = 'oro_frontend.api_doc.default_view';

    private const CORS_SETTINGS_SERVICE_ID = 'oro_frontend.api.rest.cors_settings';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $container->prependExtensionConfig($this->getAlias(), SettingsBuilder::getSettings($config));

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('services_api.yml');
        $loader->load('form_type.yml');
        $loader->load('block_types.yml');
        $loader->load('commands.yml');
        $loader->load('controllers.yml');

        $container
            ->getDefinition('oro_frontend.extractor.frontend_exposed_routes_extractor')
            ->replaceArgument(1, $config['routes_to_expose']);

        $container->setParameter('oro_frontend.debug_routes', $config['debug_routes']);

        $this->configureFrontendSession($container, $config);
        $this->configureApiDocViews($container, $config);
        $this->configureApiCors($container, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        if ($container instanceof ExtendedContainerBuilder) {
            $this->validateBackendPrefix($container);
            $this->modifySecurityConfig($container);
            $this->modifyFosRestConfig($container);
        }

        if ('test' === $container->getParameter('kernel.environment')) {
            $fileLocator = new FileLocator(__DIR__ . '/../Tests/Functional/Environment');
            $configData = Yaml::parse(file_get_contents($fileLocator->locate('app.yml')));
            foreach ($configData as $name => $config) {
                $container->prependExtensionConfig($name, $config);
            }
        }
    }

    /**
     * Validates the web_backend_prefix parameter.
     */
    private function validateBackendPrefix(ContainerBuilder $container): void
    {
        $prefix = $container->getParameter('web_backend_prefix');
        if (!$prefix) {
            throw new InvalidConfigurationException(
                'The "web_backend_prefix" parameter value should not be empty.'
            );
        }
        if (!str_starts_with($prefix, '/')) {
            throw new InvalidConfigurationException(
                'The "web_backend_prefix" parameter should start with a "/" character.'
            );
        }
        if (str_ends_with($prefix, '/')) {
            throw new InvalidConfigurationException(
                'The "web_backend_prefix" parameter should not end with a "/" character.'
            );
        }
    }

    private function modifySecurityConfig(ExtendedContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig('security');
        $restApiPatternPlaceholder = '%' . OroApiExtension::REST_API_PATTERN_PARAMETER_NAME . '%';
        foreach ($configs as $configKey => $config) {
            if (isset($config['firewalls']) && \is_array($config['firewalls'])) {
                foreach ($config['firewalls'] as $key => $firewall) {
                    if (!empty($firewall['pattern'])
                        && $firewall['pattern'] === $restApiPatternPlaceholder
                        && !str_starts_with($key, 'frontend_')
                    ) {
                        // add backend prefix to the pattern of the backend REST API firewall
                        $configs[$configKey]['firewalls'][$key]['pattern'] = $this->getBackendApiPattern($container);
                    }
                }
            }
        }
        $container->setExtensionConfig('security', $configs);
    }

    private function modifyFosRestConfig(ExtendedContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig('fos_rest');
        $oldRestApiPattern = '^/api/rest';
        $restApiPatternPlaceholder = '%' . OroApiExtension::REST_API_PATTERN_PARAMETER_NAME . '%';
        foreach ($configs as $configKey => $config) {
            if (!isset($config['format_listener']['rules']) || !\is_array($config['format_listener']['rules'])) {
                continue;
            }
            $newRules = [];
            foreach ($config['format_listener']['rules'] as $rule) {
                if (empty($rule['path'])) {
                    continue;
                }
                if ($rule['path'] === $oldRestApiPattern) {
                    $backendRule = $rule;
                    $backendRule['path'] = sprintf('^/%s/api/rest', $this->getBackendPrefix($container));
                    $newRules[] = $backendRule;
                } elseif ($rule['path'] === $restApiPatternPlaceholder) {
                    $backendRule = $rule;
                    $backendRule['path'] = $this->getBackendApiPattern($container);
                    $newRules[] = $backendRule;
                }
            }
            if ($newRules) {
                $configs[$configKey]['format_listener']['rules'] = array_merge(
                    $newRules,
                    $config['format_listener']['rules']
                );
            }
        }

        $container->setExtensionConfig('fos_rest', $configs);
    }

    private function configureFrontendSession(ContainerBuilder $container, array $config): void
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
                'gc_divisor',
                'cookie_secure',
                'cookie_httponly',
                'cookie_samesite'
            ];
            foreach ($keys as $key) {
                if (isset($sessionConfig[$key])) {
                    $options[$key] = $sessionConfig[$key];
                }
            }
        }

        $container->setParameter(self::FRONTEND_SESSION_STORAGE_OPTIONS_PARAMETER_NAME, $options);
    }

    private function configureApiDocViews(ContainerBuilder $container, array $config): void
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

    private function configureApiCors(ContainerBuilder $container, array $config): void
    {
        $corsConfig = $config['frontend_api']['cors'];
        $container->getDefinition(self::CORS_SETTINGS_SERVICE_ID)
            ->replaceArgument(0, $corsConfig['preflight_max_age'])
            ->replaceArgument(1, $corsConfig['allow_origins'])
            ->replaceArgument(2, $corsConfig['allow_credentials'])
            ->replaceArgument(3, $corsConfig['allow_headers'])
            ->replaceArgument(4, $corsConfig['expose_headers']);
    }

    private function getBackendPrefix(ContainerBuilder $container): string
    {
        return trim(trim($container->getParameter('web_backend_prefix')), '/');
    }

    private function getBackendApiPattern(ContainerBuilder $container): string
    {
        $backendPrefix = $this->getBackendPrefix($container);
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
    ): void {
        $config = DependencyInjectionUtil::getConfig($container);
        foreach ($frontendViewNames as $name) {
            if (!\array_key_exists($name, $apiDocViews)) {
                throw new LogicException(sprintf(
                    'The view "%s" defined in oro_frontend.frontend_api.api_doc_views is unknown.'
                    . ' Check that it is configured in oro_api.api_doc_views.',
                    $name
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

    private function getApiDocViews(ContainerBuilder $container): array
    {
        $config = DependencyInjectionUtil::getConfig($container);

        return $config['api_doc_views'];
    }
}
