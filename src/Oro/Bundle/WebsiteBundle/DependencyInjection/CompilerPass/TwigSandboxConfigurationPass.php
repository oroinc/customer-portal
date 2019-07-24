<?php

namespace Oro\Bundle\WebsiteBundle\DependencyInjection\CompilerPass;

use Oro\Bundle\EmailBundle\DependencyInjection\Compiler\AbstractTwigSandboxConfigurationPass;

/**
 * Configure allowed twig functions within sandbox
 */
class TwigSandboxConfigurationPass extends AbstractTwigSandboxConfigurationPass
{
    const WEBSITE_PATH_EXTENSION_SERVICE_KEY = 'oro_website.twig.website_path';

    /**
     * {@inheritdoc}
     */
    protected function getFunctions()
    {
        return [
            'website_path',
            'website_secure_path',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilters()
    {
        return [
            'oro_format_datetime_by_entity',
            'oro_format_date_by_entity',
            'oro_format_day_by_entity',
            'oro_format_time_by_entity',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        return [
            self::WEBSITE_PATH_EXTENSION_SERVICE_KEY,
            'oro_website.twig.entity_date_time_extension'
        ];
    }
}
