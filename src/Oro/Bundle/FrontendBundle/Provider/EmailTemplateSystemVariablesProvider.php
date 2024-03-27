<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\EntityBundle\Twig\Sandbox\SystemVariablesProviderInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provides the following system variables for email templates:
 * * websiteURL
 * * websiteName
 */
class EmailTemplateSystemVariablesProvider implements SystemVariablesProviderInterface
{
    private WebsiteManager $websiteManager;

    private WebsiteUrlResolver $websiteUrlResolver;

    private TranslatorInterface $translator;

    public function __construct(
        WebsiteManager $websiteManager,
        WebsiteUrlResolver $websiteUrlResolver,
        TranslatorInterface $translator,
    ) {
        $this->websiteManager = $websiteManager;
        $this->websiteUrlResolver = $websiteUrlResolver;
        $this->translator = $translator;
    }

    public function getVariableDefinitions(): array
    {
        return [
            'websiteURL' => [
                'type' => 'string',
                'label' => $this->translator->trans('oro_frontend.emailtemplate.website_url'),
            ],
            'websiteName' => [
                'type' => 'string',
                'label' => $this->translator->trans('oro_frontend.emailtemplate.website_name'),
            ],
            'organizationName' => [
                'type' => 'string',
                'label' => $this->translator->trans('oro.email.emailtemplate.organization_name'),
            ],
        ];
    }

    public function getVariableValues(): array
    {
        $currentWebsite = $this->websiteManager->getCurrentWebsite();

        return [
            'websiteURL' => $this->websiteUrlResolver->getWebsiteSecurePath('oro_frontend_root', [], $currentWebsite),
            'websiteName' => (string)$currentWebsite?->getName(),
            'organizationName' => (string)$currentWebsite?->getOrganization()?->getName(),
        ];
    }
}
