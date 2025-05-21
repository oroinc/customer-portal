<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Layout\Block\Type;

use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use Oro\Component\Layout\Block\OptionsResolver\OptionsResolver;
use Oro\Component\Layout\Block\Type\AbstractType;
use Oro\Component\Layout\Block\Type\ContainerType;
use Oro\Component\Layout\Block\Type\Options;
use Oro\Component\Layout\BlockBuilderInterface;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;

/**
 * Implements preload fonts layout block type
 */
class PreloadFontsType extends AbstractType
{
    private const PRELOAD_WEB_LINK = 'preload_web_link';
    private const FONT_SYMBOLS_TO_SEARCH =  ['~', '//'];
    private const FONT_SYMBOLS_TO_REPLACE = ['', '/'];

    public function __construct(
        private readonly ThemeConfigurationProvider $themeConfigurationProvider,
        private readonly ThemeManager $themeManager,
        private string $webpackStaticFilesPath
    ) {
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'preload_attributes',
                'as',
                'crossorigin'
            ])
            ->setDefaults([
                'preload_attributes' => ['as' => 'font'],
                'as' => 'font',
                'crossorigin' => 'anonymous'
            ]);
    }

    #[\Override]
    public function getParent(): string
    {
        return ContainerType::NAME;
    }

    #[\Override]
    public function getName(): string
    {
        return 'preload_fonts';
    }

    #[\Override]
    public function buildBlock(BlockBuilderInterface $builder, Options $options): void
    {
        $fontsPaths = $this->getFontPathsForPreload();
        if (!$fontsPaths) {
            return;
        }

        $fontOptions = $options->toArray();
        if (!isset($fontOptions['preload_attributes']['as'])) {
            $fontOptions['preload_attributes']['as'] = 'font';
        }

        if (!isset($fontOptions['preload_attributes']['crossorigin'])) {
            $fontOptions['preload_attributes']['crossorigin'] = $fontOptions['crossorigin'];
        }

        foreach ($fontsPaths as $key => $path) {
            $fontOptions['path'] = $path;
            $this->addBlockType($builder, $fontOptions, $key);
        }
    }

    private function addBlockType(BlockBuilderInterface $builder, array $fontOptions, int $key): void
    {
        $builderId = $builder->getId();
        $id = \sprintf('%s_%s%s', $builderId, self::PRELOAD_WEB_LINK, $key);

        $builder->getLayoutManipulator()->add($id, $builderId, self::PRELOAD_WEB_LINK, $fontOptions);
    }

    private function getFontPathsForPreload(): array
    {
        $themeName = $this->themeConfigurationProvider->getThemeName();
        if (!$themeName) {
            return [];
        }

        $fonts = $this->themeManager->getThemeOption($themeName, 'fonts');
        if (!\is_array($fonts) || $fonts === []) {
            return [];
        }

        $fontsPaths = [];
        foreach ($fonts as $font) {
            if ($font['preload'] !== true) {
                continue;
            }

            foreach ($font['variants'] as $variant) {
                foreach ($font['formats'] as $format) {
                    $fontsPaths[] = $this->buildFontPath($variant['path'], $format);
                }
            }
        }

        return \array_unique($fontsPaths);
    }

    private function buildFontPath(string $path, string $format): string
    {
        return \str_replace(
            self::FONT_SYMBOLS_TO_SEARCH,
            self::FONT_SYMBOLS_TO_REPLACE,
            \sprintf('%s%s.%s', $this->webpackStaticFilesPath, $path, $format)
        );
    }
}
