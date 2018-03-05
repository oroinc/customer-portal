<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection\Compiler;

use Oro\Bundle\ApiBundle\Util\DependencyInjectionUtil;
use Oro\Bundle\FrontendBundle\DependencyInjection\OroFrontendExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Sets the default view to frontend and backend RestDocUrlGenerator services.
 */
class RestDocUrlGeneratorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $frontendDefaultView = null;
        $backendDefaultView = null;
        $frontendViewNames = $container->getParameter(OroFrontendExtension::API_DOC_VIEWS_PARAMETER_NAME);
        $views = $this->getApiDocViews($container);
        foreach ($views as $name => $view) {
            if (\array_key_exists('default', $view) && $view['default']) {
                if (\in_array($name, $frontendViewNames, true)) {
                    $frontendDefaultView = $name;
                } else {
                    $backendDefaultView = $name;
                }
            }
        }

        $container->getDefinition('oro_frontend.api.rest.doc_url_generator')
            ->replaceArgument(3, $frontendDefaultView);
        $container->getDefinition('oro_api.rest.doc_url_generator')
            ->replaceArgument(2, $backendDefaultView);
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
