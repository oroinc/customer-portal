<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\CustomerBundle\Form\Type\RedirectAfterLoginConfigType;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\SecurityBundle\Util\SameSiteUrlHelper;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Menu\MenuContentNodesProviderInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Provides a target type and URL that is used to redirect after logging in.
 */
class RedirectAfterLoginProvider
{
    public function __construct(
        private ConfigManager $configManager,
        private SameSiteUrlHelper $sameSiteUrlHelper,
        private ManagerRegistry $registry,
        private RouterInterface $router,
        private LocalizationHelper $localizationHelper,
        private MenuContentNodesProviderInterface $menuContentNodesProvider
    ) {
    }

    public function getRedirectTargetType(): ?string
    {
        $targetPage = $this->getRedirectTargetPage();

        return $targetPage['targetType'] ?? null;
    }

    public function getRedirectTargetUrl(): ?string
    {
        $targetPage = $this->getRedirectTargetPage() ?? [];

        return match ($targetPage['targetType'] ?? null) {
            RedirectAfterLoginConfigType::TARGET_NONE         =>
                $this->getRefererUrl(),
            RedirectAfterLoginConfigType::TARGET_URI          =>
                $this->getRedirectUrl($targetPage['uri'] ?? null),
            RedirectAfterLoginConfigType::TARGET_SYSTEM_PAGE  =>
                $this->getSystemPageUrl($targetPage['systemPageRoute'] ?? null),
            RedirectAfterLoginConfigType::TARGET_CATEGORY     =>
                $this->getCategoryUrl($targetPage['category'] ?? null),
            RedirectAfterLoginConfigType::TARGET_CONTENT_NODE =>
                $this->getContentNodeUrl($targetPage['contentNode'] ?? null),
            default => null
        };
    }

    private function getRefererUrl(): string
    {
        return $this->sameSiteUrlHelper->getSameSiteReferer();
    }

    private function getRedirectUrl(?string $uri): ?string
    {
        if (!$uri) {
            return null;
        }

        if (!\str_starts_with($uri, '/')) {
            $uri = '/' . $uri;
        }

        try {
            $route = $this->router->match($uri);
            if (isset($route['_route'])) {
                return $uri;
            }
        } catch (ResourceNotFoundException) {
            // Route not found.
        }

        return null;
    }

    private function getSystemPageUrl(?string $route): ?string
    {
        return $route ? $this->router->generate($route) : null;
    }

    private function getCategoryUrl(?int $categoryId): ?string
    {
        $category = $categoryId ? $this->registry->getRepository(Category::class)->find($categoryId) : null;
        if (!$category) {
            return null;
        }

        return $this->router->generate('oro_product_frontend_product_index', [
            'categoryId' => $categoryId,
            'includeSubcategories' => $category->getLevel() > 0
        ]);
    }

    private function getContentNodeUrl(?int $contentNodeId): ?string
    {
        $contentNode = $contentNodeId ? $this->registry->getRepository(ContentNode::class)->find($contentNodeId) : null;
        if (!$contentNode) {
            return null;
        }

        $resolvedNode = $this->menuContentNodesProvider->getResolvedContentNode($contentNode, ['tree_depth' => 0]);
        if (!$resolvedNode) {
            return null;
        }

        return (string)$this->localizationHelper->getLocalizedValue(
            $resolvedNode->getResolvedContentVariant()->getLocalizedUrls()
        );
    }

    private function getRedirectTargetPage(): array
    {
        return $this->configManager->get(Configuration::getConfigKey(Configuration::REDIRECT_AFTER_LOGIN)) ?? [];
    }
}
