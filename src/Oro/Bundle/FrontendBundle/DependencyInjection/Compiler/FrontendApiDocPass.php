<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection\Compiler;

use Oro\Bundle\ApiBundle\Util\DependencyInjectionUtil;
use Oro\Bundle\FrontendBundle\Api\ApiDoc\RestDocUrlGenerator;
use Oro\Bundle\FrontendBundle\DependencyInjection\OroFrontendExtension;
use Oro\Bundle\FrontendBundle\EventListener\ValidateApiDocViewListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * Configures frontend API sandbox.
 */
class FrontendApiDocPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->configureValidateApiDocViewListener($container);
        $this->configureHtmlFormatters($container);
    }

    private function configureValidateApiDocViewListener(ContainerBuilder $container)
    {
        $container->getDefinition('oro_api.api_doc.validate_view_listener')
            ->setClass(ValidateApiDocViewListener::class)
            ->addArgument($container->getParameter(OroFrontendExtension::API_DOC_VIEWS_PARAMETER_NAME))
            ->addArgument($container->getParameter(OroFrontendExtension::API_DOC_DEFAULT_VIEW_PARAMETER_NAME));
    }

    private function configureHtmlFormatters(ContainerBuilder $container)
    {
        $htmlFormatters = $this->getHtmlFormatters($container);
        foreach ($htmlFormatters as $serviceId => $item) {
            [$frontend, $viewNames] = $item;
            $service = $container->getDefinition($serviceId);
            $calls = $service->getMethodCalls();
            foreach ($calls as $call) {
                if ($call[0] === 'setViews') {
                    $views = array_intersect_key($call[1][0], array_fill_keys($viewNames, true));
                    $service->removeMethodCall('setViews');
                    $service->addMethodCall('setViews', [$views]);
                    break;
                }
            }
            if ($frontend) {
                $service->addMethodCall('setRootRoute', [RestDocUrlGenerator::ROUTE]);
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array [[html formatter service id => [frontend, [view name, ...]], ...]]
     */
    private function getHtmlFormatters(ContainerBuilder $container): array
    {
        $frontendViewNames = $container->getParameter(OroFrontendExtension::API_DOC_VIEWS_PARAMETER_NAME);

        $backendViewNames = [];
        $views = $this->getApiDocViews($container);
        foreach ($views as $name => $view) {
            if (!in_array($name, $frontendViewNames, true)) {
                $backendViewNames[] = $name;
            }
        }

        $htmlFormatters = [];
        foreach ($views as $name => $view) {
            $htmlFormatter = $view['html_formatter'];
            if (!array_key_exists($htmlFormatter, $htmlFormatters)) {
                if (in_array($name, $frontendViewNames, true)) {
                    $htmlFormatters[$htmlFormatter] = [true, $frontendViewNames];
                } else {
                    $htmlFormatters[$htmlFormatter] = [false, $backendViewNames];
                }
            } elseif ($htmlFormatters[$htmlFormatter][0] !== in_array($name, $frontendViewNames, true)) {
                throw new LogicException(sprintf(
                    'The HTML formater "%s" configured for view "%s" cannot be used for both'
                    . ' frontend and backend views.',
                    $htmlFormatter,
                    $name
                ));
            }
        }

        return $htmlFormatters;
    }

    private function getApiDocViews(ContainerBuilder $container): array
    {
        $config = DependencyInjectionUtil::getConfig($container);

        return $config['api_doc_views'];
    }
}
