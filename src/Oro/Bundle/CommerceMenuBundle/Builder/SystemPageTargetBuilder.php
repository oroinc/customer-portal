<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Routing\RouterInterface;

/**
 * Menu builder which sets URI for menu items with system page as target.
 */
class SystemPageTargetBuilder implements BuilderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var RouterInterface */
    private $router;

    /** @var FeatureChecker */
    private $featureChecker;

    public function __construct(RouterInterface $router, FeatureChecker $featureChecker)
    {
        $this->router = $router;
        $this->featureChecker = $featureChecker;
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritDoc}
     */
    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        $this->applyRecursively($menu, $options);
    }

    private function applyRecursively(ItemInterface $menuItem, array $options): void
    {
        if (!$menuItem->isDisplayed()) {
            return;
        }

        foreach ($menuItem->getChildren() as $menuChild) {
            $this->applyRecursively($menuChild, $options);
        }

        $systemPageRoute = $menuItem->getExtra('system_page_route');
        if (!$systemPageRoute) {
            return;
        }

        $url = $this->generateUrl($systemPageRoute);
        if ($url) {
            $menuItem->setUri($url);
        } else {
            $menuItem->setDisplay(false);
        }
    }

    /**
     * @param string $routeName
     *
     * @return string
     */
    private function generateUrl(string $routeName): ?string
    {
        if (!$this->isRouteEnabled($routeName)) {
            return null;
        }

        try {
            return $this->router->generate($routeName);
        } catch (\Exception $exception) {
            $this->logger->warning(
                sprintf(
                    'Could not generate url for menu item with system page route "%s"',
                    $routeName
                ),
                ['e' => $exception]
            );
        }

        return null;
    }

    private function isRouteEnabled(string $routeName): bool
    {
        return $this->featureChecker->isResourceEnabled($routeName, 'routes');
    }
}
