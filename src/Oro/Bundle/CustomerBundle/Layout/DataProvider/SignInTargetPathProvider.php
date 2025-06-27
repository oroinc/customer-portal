<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Provider\RedirectAfterLoginProvider;
use Oro\Bundle\SecurityBundle\Util\SameSiteUrlHelper;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Provides the target URL to redirect the user to after a successful login.
 *
 * The redirect URL is determined using the RedirectAfterLoginProvider, which typically stores
 * the original URL the user attempted to access before being prompted to log in.
 *
 * If the resolved route from the stored URL is in the list of excluded routes,
 * the fallback route is returned instead. This is necessary when redirecting back to certain routes
 * makes no sense or causes errors, such as attempting to return to a reset password page that is no longer valid.
 */
class SignInTargetPathProvider implements SignInTargetPathProviderInterface
{
    private const string FALLBACK_ROUTE = 'oro_frontend_root';

    public array $excludedRoutes = [];

    public function __construct(
        private RedirectAfterLoginProvider $redirectProvider,
        private SameSiteUrlHelper $urlHelper,
        private RouterInterface $router
    ) {
    }

    public function addExcludedRoute(string $route): void
    {
        if (!in_array($route, $this->excludedRoutes, true)) {
            $this->excludedRoutes[] = $route;
        }
    }

    #[\Override]
    public function getTargetPath(): ?string
    {
        $redirectUrl = $this->redirectProvider->getRedirectTargetUrl();
        $path = parse_url($redirectUrl, PHP_URL_PATH);

        try {
            $routeMatch = $this->router->match($path);
        } catch (ResourceNotFoundException) {
            $routeMatch = [];
        }

        $routeName = $routeMatch['_route'] ?? null;

        if (in_array($routeName, $this->excludedRoutes, true)) {
            return $this->router->generate(self::FALLBACK_ROUTE);
        }

        if (is_string($redirectUrl) && $this->urlHelper->isSameSiteUrl($redirectUrl)) {
            return $redirectUrl;
        }

        return null;
    }
}
