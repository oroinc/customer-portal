<?php

namespace Oro\Bundle\FrontendBundle\Form\Extension;

use Oro\Bundle\CMSBundle\Form\Type\WYSIWYGType;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Layout\Extension\Theme\DataProvider\ThemeProvider;
use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Adds information about layout themes to the WYSIWYGType options
 */
class WYSIWYGTypeExtension extends AbstractTypeExtension
{
    /**
     * @var ThemeManager
     */
    private $themeManager;

    /**
     * @var ThemeProvider
     */
    private $themeProvider;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    /**
     * @param ThemeManager $themeManager
     * @param ThemeProvider $themeProvider
     * @param ConfigManager $configManager
     * @param WebsiteManager $websiteManager
     */
    public function __construct(
        ThemeManager $themeManager,
        ThemeProvider $themeProvider,
        ConfigManager $configManager,
        WebsiteManager $websiteManager
    ) {
        $this->themeManager = $themeManager;
        $this->themeProvider = $themeProvider;
        $this->configManager = $configManager;
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [WYSIWYGType::class];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('page-component', function (Options $options, $value) {
            $value['options']['themes'] = $this->getThemes();

            return $value;
        });
    }

    /**
     * @return Theme[]
     */
    private function getThemes(): array
    {
        $themes = $this->themeManager->getAllThemes();
        $defaultWebsite = $this->websiteManager->getDefaultWebsite();
        $layoutThemeName = $this->configManager->get('oro_frontend.frontend_theme', false, false, $defaultWebsite);

        $themesData = [];
        foreach ($themes as $key => $theme) {
            $themeName = $theme->getName();
            $themeData = [
                'name' => $themeName,
                'label' => $theme->getLabel(),
                'stylesheet' => $this->themeProvider->getStylesOutput($themeName),
            ];
            if ($layoutThemeName === $themeName) {
                $themeData['active'] = true;
            }

            $themesData[$key] = $themeData;
        }

        return $themesData;
    }
}
