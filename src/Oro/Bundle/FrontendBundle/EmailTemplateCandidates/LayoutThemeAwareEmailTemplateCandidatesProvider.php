<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\EmailTemplateCandidates;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\EmailTemplateCandidates\EmailTemplateCandidatesProviderInterface;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;

/**
 * Provides an email template name that includes the current layout theme taken from a system config.
 *
 * Example of the resulting template name:
 * @theme:name=default/sample_template_name
 */
class LayoutThemeAwareEmailTemplateCandidatesProvider implements EmailTemplateCandidatesProviderInterface
{
    protected ConfigManager $configManager;

    private array $templateExtensions = ['.html.twig'];

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param string[] $templateExtensions
     */
    public function setTemplateExtensions(array $templateExtensions): void
    {
        $this->templateExtensions = $templateExtensions;
    }

    public function getCandidatesNames(EmailTemplateCriteria $emailTemplateCriteria, array $templateContext = []): array
    {
        if (str_starts_with($emailTemplateCriteria->getName(), '@')) {
            return [];
        }

        $themeName = $this->getCurrentThemeName($templateContext);
        if (!$themeName) {
            return [];
        }

        $emailTemplateNames = [];
        foreach ($this->templateExtensions as $extension) {
            $emailTemplateNames[] = sprintf(
                '@theme:%s/%s%s',
                http_build_query(['name' => $themeName]),
                $emailTemplateCriteria->getName(),
                $extension
            );
        }

        return $emailTemplateNames;
    }

    protected function getCurrentThemeName(array $templateContext = []): ?string
    {
        return $this->configManager->get('oro_frontend.frontend_theme');
    }
}
