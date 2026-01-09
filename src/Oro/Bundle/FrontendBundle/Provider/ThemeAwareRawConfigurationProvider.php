<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\DataGridBundle\Provider\Cache\GridCacheUtils;
use Oro\Bundle\DataGridBundle\Provider\RawConfigurationProvider;
use Oro\Bundle\DataGridBundle\Provider\RawConfigurationProviderInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Component\Config\Cache\ConfigCache;
use Oro\Component\Config\Cache\PhpConfigCacheAccessor;
use Oro\Component\Config\Cache\WarmableConfigCacheInterface;
use Oro\Component\Config\Loader\CumulativeConfigLoader;
use Oro\Component\Config\Loader\FolderingCumulativeFileLoader;
use Oro\Component\Config\Loader\YamlCumulativeFileLoader;
use Oro\Component\Config\ResourcesContainer;
use Oro\Component\Config\ResourcesContainerInterface;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Oro\Component\PhpUtils\ArrayUtil;

/**
 * The provider for datagrids configuration
 * that is loaded from "Resources/views/layouts/{themeId}/config/datagrids.yml" files
 * and not processed by SystemAwareResolver.
 */
class ThemeAwareRawConfigurationProvider implements RawConfigurationProviderInterface, WarmableConfigCacheInterface
{
    private ?PhpConfigCacheAccessor $rootCacheAccessor = null;

    private ?PhpConfigCacheAccessor $gridCacheAccessor = null;
    private bool $hasCache = false;
    private array $rawConfiguration = [];

    public function __construct(
        private readonly string $cacheDir,
        private readonly bool $debug,
        private readonly FrontendHelper $frontendHelper,
        private readonly RawConfigurationProvider $rawConfigurationProvider,
        private readonly GridCacheUtils $gridCacheUtils,
        private readonly ThemeManager $themeManager,
        private readonly CurrentThemeProvider $currentThemeProvider,
        private readonly string $folderPattern = '',
    ) {
    }

    #[\Override]
    public function getRawConfiguration(string $gridName): ?array
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return $this->rawConfigurationProvider->getRawConfiguration($gridName);
        }

        $this->ensureRawConfigurationLoaded($gridName);

        return $this->rawConfiguration[$gridName] ?? null;
    }

    #[\Override]
    public function warmUpCache(): void
    {
        // warm up original decorated raw config provider
        $this->rawConfigurationProvider->warmUpCache();

        $this->hasCache = false;
        $this->ensureCacheWarmedUp();
    }

    /**
     * Makes sure that configuration cache was warmed up.
     */
    private function ensureCacheWarmedUp(): void
    {
        if ($this->hasCache) {
            return;
        }

        $rootCache = new ConfigCache($this->cacheDir . '/frontend_datagrids.php', $this->debug);
        if (!$rootCache->isFresh()) {
            $resourcesContainer = new ResourcesContainer();
            $gridCacheAccessor = $this->getGridCacheAccessor();
            $aggregatedConfigs = $this->loadConfiguration($resourcesContainer);

            foreach ($aggregatedConfigs as $themeId => $themeGrids) {
                foreach ($themeGrids as $gridName => $gridConfigs) {
                    $gridCacheAccessor->save(
                        $this->gridCacheUtils->getGridConfigCache($gridName, $themeId),
                        $gridConfigs
                    );
                }
            }
            $this->getRootCacheAccessor()->save($rootCache, true, $resourcesContainer->getResources());
        }

        $this->hasCache = $this->getRootCacheAccessor()->load($rootCache);
    }

    private function ensureRawConfigurationLoaded(string $gridName): void
    {
        $this->ensureCacheWarmedUp();
        if (isset($this->rawConfiguration[$gridName])) {
            return;
        }
        $themeGridCache = $this->gridCacheUtils->getGridConfigCache($gridName, $this->getCurrentThemeId());
        if (\is_file($themeGridCache->getPath())) {
            $this->rawConfiguration = \array_merge(
                $this->rawConfiguration,
                $this->getGridCacheAccessor()->load($themeGridCache)
            );
        }
    }

    /**
     * @param ResourcesContainerInterface $resourcesContainer
     *
     * @return array [grid name => [grid name => config, mixin grid name => config, ...], ...]
     */
    private function loadConfiguration(ResourcesContainerInterface $resourcesContainer): array
    {
        $themeConfigs = [];
        $loaders = [
            new YamlCumulativeFileLoader('Resources/views/layouts/{folder}/config/datagrids.yml'),
            new YamlCumulativeFileLoader('../templates/layouts/{folder}/config/datagrids.yml')
        ];
        $cumulativeConfigLoader = new CumulativeConfigLoader(
            'oro_frontend_datagrid',
            [
                new FolderingCumulativeFileLoader(
                    '{folder}',
                    $this->folderPattern,
                    $loaders,
                )
            ]
        );
        $resources = $cumulativeConfigLoader->load($resourcesContainer);

        /**
         * Generate a new resource list by bundle and themeId. This is required to correctly implement
         * grid configuration inheritance.
         *
         * Ex. Having a 'custom' theme datagrid extending the 'default' themes datagrid:
         * - in bundle1 :
         *     - default theme: grid1.options.entityHint = 'label_b1_default'
         *     - custom theme:  grid1.options.entityHint = 'label_b1_custom'
         * - in bundle2 :
         *     - default theme: grid1.options.entityHint = 'label_b2_default'
         *     - custom theme:  grid1.options.entityHint = 'label_b2_custom'
         * Resulting configuration:
         * - default: grid1 inherits in order: label_b1_default -> label_b2_default
         *     => final value: label_b2_default
         * - custom: grid1 inherits in order: label_b2_default -> label_b1_custom -> label_b2_custom
         *     => final value: label_b2_custom
         */
        $resourcesPerBundle = [];
        $themeIds = array_map(fn (Theme $theme) => $theme->getName(), $this->themeManager->getAllThemes());
        foreach ($resources as $resource) {
            if (
                !$resource->folderPlaceholder
                || !isset($resource->data[RawConfigurationProviderInterface::ROOT_SECTION])
            ) {
                continue;
            }
            $resourcesPerBundle[$resource->bundleClass][$resource->folderPlaceholder] = $resource;
        }

        $themeLevels = $this->orderThemesByLevel($themeIds);

        /**
         * Process root level themes first (as they don't depend on other themes,
         * Then second level themes as they only depend on root level, which is already processed in
         * previous iteration
         */
        foreach ($themeLevels as $themeInfo) {
            $themeId = $themeInfo['themeId'];
            // If theme is having a parent, use it's final config as starting point for current theme
            $themeConfigs[$themeId] = $themeInfo['parent'] ? $themeConfigs[$themeInfo['parent']] : [];
            foreach ($resourcesPerBundle as $themedResources) {
                $grids = $themedResources[$themeId]->data[RawConfigurationProviderInterface::ROOT_SECTION] ?? [];
                if (!$grids) {
                    continue;
                }

                $themeConfigs[$themeId] = ArrayUtil::arrayMergeRecursiveDistinct(
                    $themeConfigs[$themeId],
                    $grids
                );
            }
        }

        $aggregatedThemeGrids = [];
        foreach ($themeConfigs as $themeId => $themeGrids) {
            $aggregatedThemeGrids[$themeId] = $this->rawConfigurationProvider->aggregateConfiguration($themeGrids);
        }

        return $aggregatedThemeGrids;
    }

    private function getRootCacheAccessor(): PhpConfigCacheAccessor
    {
        if (null === $this->rootCacheAccessor) {
            $this->rootCacheAccessor = new PhpConfigCacheAccessor(function ($config) {
                if (true !== $config) {
                    throw new \LogicException('Expected boolean TRUE.');
                }
            });
        }

        return $this->rootCacheAccessor;
    }

    private function getGridCacheAccessor(): PhpConfigCacheAccessor
    {
        if (null === $this->gridCacheAccessor) {
            $this->gridCacheAccessor = new PhpConfigCacheAccessor(function ($config) {
                if (!\is_array($config)) {
                    throw new \LogicException('Expected an array.');
                }
            });
        }

        return $this->gridCacheAccessor;
    }

    private function getCurrentThemeId(): string
    {
        $themeId = $this->currentThemeProvider->getCurrentThemeId();
        if (!$themeId) {
            throw new \RuntimeException('Could not fetch current theme id.');
        }

        return $themeId;
    }

    /**
     * Get a list of theme ids, and generate a new sorted theme id list
     * by level of theme in its hierarchy, in ASC order
     */
    private function orderThemesByLevel(array $themeIds): array
    {
        $themeLevels = [];
        foreach ($themeIds as $themeId) {
            $hierarchy = $this->themeManager->getThemesHierarchy($themeId);
            $themeLevels[$themeId] = [
                'themeId' => $themeId,
                'level' => count($hierarchy),
                'parent' => $this->themeManager->getTheme($themeId)->getParentTheme()
            ];
        }
        usort($themeLevels, function ($val1, $val2) {
            return $val1['level'] <=> $val2['level'];
        });

        return $themeLevels;
    }
}
