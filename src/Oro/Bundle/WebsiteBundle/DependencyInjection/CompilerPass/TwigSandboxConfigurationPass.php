<?php

namespace Oro\Bundle\WebsiteBundle\DependencyInjection\CompilerPass;

use Oro\Bundle\EmailBundle\DependencyInjection\Compiler\AbstractTwigSandboxConfigurationPass;

/**
 * Registers the following Twig functions and filters for the email templates rendering sandbox:
 * * website_path
 * * website_secure_path
 * * oro_format_datetime_by_entity
 * * oro_format_date_by_entity
 * * oro_format_day_by_entity
 * * oro_format_time_by_entity
 */
class TwigSandboxConfigurationPass extends AbstractTwigSandboxConfigurationPass
{
    #[\Override]
    protected function getFunctions(): array
    {
        return [
            'website_path',
            'website_secure_path',
        ];
    }

    #[\Override]
    protected function getFilters(): array
    {
        return [
            'oro_format_datetime_by_entity',
            'oro_format_date_by_entity',
            'oro_format_day_by_entity',
            'oro_format_time_by_entity',
        ];
    }

    #[\Override]
    protected function getTags(): array
    {
        return [];
    }

    #[\Override]
    protected function getExtensions(): array
    {
        return [
            'oro_website.twig.website_extension',
            'oro_website.twig.entity_date_time_extension'
        ];
    }
}
