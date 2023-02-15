<?php

namespace Oro\Bundle\FrontendBundle\Api;

use Oro\Bundle\ApiBundle\Util\DependencyInjectionUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Provides a set of methods to simplify working with frontend API services.
 */
class FrontendApiDependencyInjectionUtil
{
    /**
     * Disables the specific API processor for the frontend API.
     */
    public static function disableProcessorForFrontendApi(
        ContainerBuilder $container,
        string $processorServiceId
    ): void {
        DependencyInjectionUtil::disableApiProcessor($container, $processorServiceId, 'frontend');
    }
}
