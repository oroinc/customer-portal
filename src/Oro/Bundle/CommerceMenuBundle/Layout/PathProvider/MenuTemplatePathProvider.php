<?php

namespace Oro\Bundle\CommerceMenuBundle\Layout\PathProvider;

use Oro\Component\Layout\ContextAwareInterface;
use Oro\Component\Layout\ContextInterface;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Oro\Component\Layout\Extension\Theme\PathProvider\PathProviderInterface;

/**
 * Builds list of paths which must be processed to find layout updates for menu template.
 */
class MenuTemplatePathProvider implements PathProviderInterface, ContextAwareInterface
{
    private ThemeManager $themeManager;

    private ContextInterface $context;

    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
    }

    public function setContext(ContextInterface $context): void
    {
        $this->context = $context;
    }

    public function getPaths(array $existingPaths): array
    {
        $themeName = $this->context->getOr('theme');
        $menuTemplate = $this->context->getOr('menu_template');
        if ($themeName && $menuTemplate) {
            $existingPaths = [];

            $themes = $this->themeManager->getThemesHierarchy($themeName);
            foreach ($themes as $theme) {
                $existingPath = implode(self::DELIMITER, [$theme->getDirectory(), 'menu_template']);

                $existingPaths[] = $existingPath;
                $existingPaths[] = implode(self::DELIMITER, [$existingPath, $menuTemplate]);
            }
        }

        return $existingPaths;
    }
}
