<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\EmailTemplateCandidates;

use Oro\Bundle\EmailBundle\EmailTemplateCandidates\EmailTemplateCandidatesProviderInterface;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;

/**
 * Provides an email template name that includes the current layout theme taken from a system config.
 *
 * Example of the resulting template name:
 * @theme:name=default/sample_template_name
 */
class LayoutThemeAwareEmailTemplateCandidatesProvider implements EmailTemplateCandidatesProviderInterface
{
    protected ThemeConfigurationProvider $themeConfigurationProvider;

    private array $templateExtensions = ['.html.twig'];

    public function __construct(ThemeConfigurationProvider $themeConfigurationProvider)
    {
        $this->themeConfigurationProvider = $themeConfigurationProvider;
    }

    /**
     * @param string[] $templateExtensions
     */
    public function setTemplateExtensions(array $templateExtensions): void
    {
        $this->templateExtensions = $templateExtensions;
    }

    #[\Override]
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
        return $this->themeConfigurationProvider->getThemeName();
    }
}
