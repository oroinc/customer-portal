<?php

namespace Oro\Bundle\FrontendBundle\Form\Extension;

use Oro\Bundle\CMSBundle\Form\Type\WYSIWYGType;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Layout\Extension\Theme\DataProvider\ThemeProvider;
use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Adds information about layout themes to the WYSIWYGType options
 */
class WYSIWYGTypeExtension extends AbstractTypeExtension
{
    private const COMMERCE_GROUP = 'commerce';

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
     * @var Packages
     */
    private $packages;

    public function __construct(
        ThemeManager $themeManager,
        ThemeProvider $themeProvider,
        ConfigManager $configManager,
        WebsiteManager $websiteManager,
        Packages $packages
    ) {
        $this->themeManager = $themeManager;
        $this->themeProvider = $themeProvider;
        $this->configManager = $configManager;
        $this->websiteManager = $websiteManager;
        $this->packages = $packages;
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
        foreach ($themes as $theme) {
            if (!$theme->hasGroup(self::COMMERCE_GROUP)) {
                continue;
            }

            $themeName = $theme->getName();
            $styleOutput = $this->themeProvider->getStylesOutput($themeName);
            $themeData = [
                'name' => $themeName,
                'label' => $theme->getLabel(),
                'stylesheet' => $this->packages->getUrl($styleOutput),
            ];
            if ($layoutThemeName === $themeName) {
                $themeData['active'] = true;
            }

            $themesData[] = $themeData;
        }

        return $themesData;
    }
}
