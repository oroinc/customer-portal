<?php

namespace Oro\Bundle\FrontendBundle\Twig;

use Oro\Bundle\AsseticBundle\AssetsConfiguration;
use Oro\Bundle\ThemeBundle\Model\ThemeRegistry;
use Symfony\Bundle\AsseticBundle\Factory\AssetFactory;

class DemoThemeExtension extends \Twig_Extension
{
    const NAME = 'oro_frontend_theme';

    const TAG_NAME = 'oro_css';
    const PARSER_OUTPUT = 'css/*.css';

    /**
     * @var AssetsConfiguration
     */
    protected $assetsConfiguration;

    /**
     * @var AssetFactory
     */
    protected $assetFactory;

    /**
     * @var ThemeRegistry
     */
    protected $themeRegistry;

    /**
     * @param AssetFactory $assetFactory
     * @param AssetsConfiguration $assetsConfiguration
     * @param ThemeRegistry $themeRegistry
     */
    public function __construct(
        AssetFactory $assetFactory,
        AssetsConfiguration $assetsConfiguration,
        ThemeRegistry $themeRegistry
    ) {
        $this->assetFactory = $assetFactory;
        $this->assetsConfiguration = $assetsConfiguration;
        $this->themeRegistry = $themeRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenParsers()
    {
        return [
            new FrontendAsseticTokenParser(
                $this->assetsConfiguration,
                $this->assetFactory,
                $this->themeRegistry,
                self::TAG_NAME,
                self::PARSER_OUTPUT
            )
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
